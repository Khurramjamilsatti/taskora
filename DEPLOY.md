# Taskora — Production Deployment (Docker)

This repository ships **one application image** (frontend + backend combined)
plus PostgreSQL, orchestrated by `docker-compose.yml`:


| Service    | Image                | Role                                                                        |
| ---------- | -------------------- | --------------------------------------------------------------------------- |
| `postgres` | `postgres:16-alpine` | PostgreSQL database for site content and estimates                          |
| `app`      | `taskora-app`        | Vue SPA **and** Laravel API in one container (nginx + php-fpm + supervisor) |


The single `app` image is built from the repo-root `Dockerfile`: it compiles the
Vue SPA, installs the Laravel backend, and runs nginx (serving the SPA and
routing `/api/*` to php-fpm) — one origin, no CORS needed.

---



## 1. Install Docker on CentOS 10 Stream

```bash
sudo dnf -y install dnf-plugins-core
sudo dnf config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
sudo dnf -y install docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

sudo systemctl enable --now docker

# (optional) run docker without sudo
sudo usermod -aG docker "$USER"
newgrp docker
```

Open the firewall for HTTP:

```bash
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --reload
```

> SELinux is enforcing on CentOS by default. The compose file uses a named
> volume (`backend_storage`) rather than host bind-mounts, so no `:z`/`:Z`
> relabeling is required.

---



## 2. Get the code

```bash
git clone https://github.com/Khurramjamilsatti/taskora.git
cd taskora
```

---



## 3. Configure

```bash
cp .env.example .env
# sdf edit .env — set APP_URL / FRONTEND_URL to your server IP or domain
```

Leave `ENABLE_SSL=false` for a plain HTTP deployment (e.g. when using a bare
IP). To serve HTTPS with a free Let's Encrypt certificate, see section 5.

---



## 4. Build & run

```bash
docker compose up -d --build
```

Check status and logs:

```bash
docker compose ps
docker compose logs -f
```

The site is now available at `http://<server-ip>/` and the API at
`http://<server-ip>/api/site`.

---



## 5. Enable HTTPS with Let's Encrypt (Certbot)

TLS is built into the `app` container — nginx, php-fpm **and** Certbot run
together. No extra service or reverse proxy is required.

**Prerequisites**

- A real domain with a DNS **A record pointing to this server's IP**.
- Ports **80 and 443** open in the firewall (see section 1).

**Configure** `.env`**:**

```dotenv
HTTP_PORT=80
HTTPS_PORT=443

APP_URL=https://taskora.example.com
FRONTEND_URL=https://taskora.example.com

ENABLE_SSL=true
DOMAIN=taskora.example.com
CERTBOT_EMAIL=you@example.com
# Use staging first to avoid hitting rate limits while testing:
CERTBOT_STAGING=true
```

**Launch:**

```bash
docker compose up -d --build
docker compose logs -f app   # watch the certificate get issued
```

On first boot the container:

1. Requests a certificate from Let's Encrypt (standalone, on port 80).
2. Configures nginx for HTTPS and redirects all HTTP traffic to HTTPS.
3. Renews the certificate automatically (checked twice daily, nginx reloaded).

If issuance fails (e.g. DNS not propagated yet), the container falls back to a
**self-signed** certificate so the site still loads over HTTPS. Fix DNS, then:

```bash
docker compose restart app
```

Once staging works, switch to real certificates:

```dotenv
CERTBOT_STAGING=false
```

```bash
# Remove the staging cert and re-issue a trusted one
docker compose exec app rm -rf /etc/letsencrypt/live /etc/letsencrypt/archive /etc/letsencrypt/renewal
docker compose restart app
```

Certificates persist in the `letsencrypt` Docker volume across restarts.

---



## 6. Common operations

```bash
# Rebuild after pulling new code
git pull && docker compose up -d --build

# Stop
docker compose down

# Stop and wipe persisted data (postgres, certs, sessions, logs)
docker compose down -v

# Run an artisan command
docker compose exec app php artisan about

# Force a certificate renewal check
docker compose exec app certbot renew --webroot -w /var/www/certbot --deploy-hook "nginx -s reload"
```

---



## 7. Notes on data

- Site content and cost-estimator options are stored in **PostgreSQL**.
- On first boot the backend runs migrations and seeds content from
`config/taskora.php` into Postgres. The frontend loads everything via
`GET /api/site`, and estimates via `POST /api/estimate`.
- Sessions/cache/queue use the filesystem on the `backend_storage` volume.
- Postgres data lives on the `postgres_data` volume, TLS certificates on the
`letsencrypt` volume. Wipe everything with `docker compose down -v`.

---



## 8. CI/CD (GitHub Actions)

Two workflows live in `.github/workflows/`:


| Workflow     | Trigger                     | What it does                                                                              |
| ------------ | --------------------------- | ----------------------------------------------------------------------------------------- |
| `ci.yml`     | every push & pull request   | Builds the Vue frontend, validates the Laravel backend, builds the Docker image           |
| `deploy.yml` | push to `main` / manual run | Builds & pushes the image to GHCR, then POSTs a deploy webhook on the VPS (no SSH)        |


### How deployment works

1. The image is built once on the GitHub runner and pushed to
   **GHCR** (`ghcr.io/<owner>/taskora-app:latest` + a `:<commit-sha>` tag).
2. The workflow calls the VPS **deploy webhook** (`POST /deploy`) with a shared
   secret. The webhook pulls the new image and restarts the `app` container.
   No SSH is used.



### One-time server preparation

The server keeps the repo (for `docker-compose.yml` + `.env`) but runs the
prebuilt image:

```bash
cd ~/taskora
git pull

# Add a long random secret to .env (same value as the GitHub secret):
# DEPLOY_WEBHOOK_SECRET=...
# WEBHOOK_PORT=9100

docker compose up -d --build webhook
# or restart the full stack:
# docker compose up -d --build
```

Open the webhook port on the firewall (CentOS / firewalld example):

```bash
firewall-cmd --permanent --add-port=9100/tcp
firewall-cmd --reload
```

Ensure `.env` on the server contains your production values (DB password,
`ENABLE_SSL`, `DOMAIN`, `DEPLOY_WEBHOOK_SECRET`, etc.). `APP_IMAGE` is sent by
the pipeline in the webhook payload, so it does not need to be in `.env`.

Health check:

```bash
curl http://YOUR_VPS_IP:9100/health
```

### Required GitHub repository secrets

Add these under **Settings → Secrets and variables → Actions**:


| Secret                  | Description                                                                 |
| ----------------------- | --------------------------------------------------------------------------- |
| `DEPLOY_WEBHOOK_URL`    | Full webhook URL, e.g. `http://YOUR_VPS_IP:9100/deploy`                     |
| `DEPLOY_WEBHOOK_SECRET` | Same value as `DEPLOY_WEBHOOK_SECRET` in the VPS `.env`                     |

`GITHUB_TOKEN` is provided automatically and is used to push the image and to
let the webhook pull it from GHCR — no personal access token is required.

SSH secrets (`SSH_HOST`, `SSH_USER`, `DEPLOY_ACCESS_TOKEN`, keys, etc.) are
**not** used anymore.

### Webhook auth

GitHub Actions sends:

```http
POST /deploy
Authorization: Bearer <DEPLOY_WEBHOOK_SECRET>
Content-Type: application/json

{"image":"ghcr.io/.../taskora-app:latest","ghcr_user":"...","ghcr_token":"..."}
```

Keep `DEPLOY_WEBHOOK_SECRET` long and random. Prefer firewalling port `9100`
to known ranges if your provider allows it.

### GHCR image visibility

The first push creates the package as **private**. The pipeline logs in with
`GITHUB_TOKEN` before pulling, so private works out of the box. To allow
pulling without auth, make the package public in
**GitHub → your profile → Packages → taskora-app → Package settings**.

### Trigger a deployment

- Push to `main`, or
- **Actions → Deploy → Run workflow** (manual `workflow_dispatch`).

