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


| Workflow     | Trigger                     | What it does                                                                    |
| ------------ | --------------------------- | ------------------------------------------------------------------------------- |
| `ci.yml`     | every push & pull request   | Builds the Vue frontend, validates the Laravel backend, builds the Docker image |
| `deploy.yml` | push to `main` / manual run | Builds & pushes to GHCR with `DEPLOY_ACCESS_TOKEN`, then deploys on a self-hosted VPS runner |


### How deployment works

1. A GitHub-hosted runner builds the image and pushes it to **GHCR** using your
   Personal Access Token (`DEPLOY_ACCESS_TOKEN`).
2. A **self-hosted runner on the VPS** pulls that image and restarts the `app`
   container. No SSH and no webhook port are used.



### One-time server preparation

```bash
cd ~/taskora
git pull
# ensure .env has production values; optionally set:
# APP_IMAGE=ghcr.io/khurramjamilsatti/taskora-app:latest
docker compose up -d
```

### Install a self-hosted runner on the VPS

1. GitHub → **repo → Settings → Actions → Runners → New self-hosted runner**
2. Follow the Linux instructions on the VPS (download, `./config.sh`, `./run.sh`
   or install as a service with `sudo ./svc.sh install && sudo ./svc.sh start`).
3. When `config.sh` asks for a token, use the registration token shown on that
   GitHub page (or a PAT with `admin:org` / repo admin rights for runners).

The runner must run as a user that can execute `docker` / `docker compose`
(usually in the `docker` group), and the working directory for deploy defaults
to `$HOME/taskora` (override with secret `DEPLOY_PATH`).

### Required GitHub repository secrets

Add under **Settings → Secrets and variables → Actions**:


| Secret                | Description                                                                 |
| --------------------- | --------------------------------------------------------------------------- |
| `DEPLOY_ACCESS_TOKEN` | GitHub **Personal Access Token** (classic) with `write:packages`, `read:packages`, and `repo` |
| `DEPLOY_PATH`         | Optional. Path to the repo on the VPS (default `$HOME/taskora`)             |

Create the PAT at **GitHub → Settings → Developer settings → Personal access tokens**.

Webhook / SSH secrets are **not** used.

### GHCR image visibility

The first push creates the package as **private**. The pipeline logs in with
`GITHUB_TOKEN` before pulling, so private works out of the box. To allow
pulling without auth, make the package public in
**GitHub → your profile → Packages → taskora-app → Package settings**.

### Trigger a deployment

- Push to `main`, or
- **Actions → Deploy → Run workflow** (manual `workflow_dispatch`).

