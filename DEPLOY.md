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
| `deploy.yml` | push to `main` / manual run | Builds & pushes the image to GHCR, then deploys to the VPS over SSH             |




### How deployment works

1. The image is built once on the GitHub runner and pushed to
  **GHCR** (`ghcr.io/<owner>/taskora-app:latest` + a `:<commit-sha>` tag).
2. The workflow SSHes into the VPS, pulls that prebuilt image, and runs
  `docker compose up -d --no-build`. The server no longer builds images itself.



### One-time server preparation

The server keeps the repo (for `docker-compose.yml` + `.env`) but runs the
prebuilt image:

```bash
cd ~/taskora
git pull                      # get the APP_IMAGE-aware compose file
```

Ensure `.env` on the server contains your production values (DB password,
`ENABLE_SSL`, `DOMAIN`, etc.). `APP_IMAGE` is injected by the pipeline, so it
does not need to be in `.env`.

### Required GitHub repository secrets

Add these under **Settings → Secrets and variables → Actions**:


| Secret                | Description                                                     |
| --------------------- | --------------------------------------------------------------- |
| `SSH_HOST`            | VPS IP or hostname (e.g. `taskora.digital`)                     |
| `SSH_USER`            | SSH user (e.g. `root` or a deploy user)                         |
| `DEPLOY_ACCESS_TOKEN` | SSH password (or access token) for `SSH_USER`                   |
| `SSH_PORT`            | Optional, defaults to `22`                                      |
| `DEPLOY_PATH`         | Optional, path to the repo on the VPS (defaults to `~/taskora`) |

Password authentication must be enabled on the VPS for this user
(`PasswordAuthentication yes` in `sshd_config`, then restart sshd).

Key-based secrets (`DEPLOY_SSH_KEY`, `DEPLOY_SSH_PASSPHRASE`, `SSH_KEY`)
are no longer used by the deploy workflow.


`GITHUB_TOKEN` is provided automatically and is used to push/pull the GHCR
image — no personal access token is required.

### SSH password / access token

Use the Linux account password for `SSH_USER`, or create a dedicated deploy
user and put that password in the `DEPLOY_ACCESS_TOKEN` secret.

On the VPS, ensure password login is allowed:

```bash
# /etc/ssh/sshd_config
PasswordAuthentication yes

sudo systemctl restart sshd
```

Confirm you can log in from your machine first:

```bash
ssh YOUR_USER@YOUR_HOST
```

Also confirm port 22 (or `SSH_PORT`) is reachable from the public internet;
a `dial tcp ... i/o timeout` means the firewall or provider security group is
blocking GitHub Actions.

### GHCR image visibility

The first push creates the package as **private**. The pipeline logs in with
`GITHUB_TOKEN` before pulling, so private works out of the box. To allow
pulling without auth, make the package public in
**GitHub → your profile → Packages → taskora-app → Package settings**.

### Trigger a deployment

- Push to `main`, or
- **Actions → Deploy → Run workflow** (manual `workflow_dispatch`).

