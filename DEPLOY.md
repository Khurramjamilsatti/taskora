# Taskora — Production Deployment (Docker)

This repository ships two Docker images orchestrated by `docker-compose.yml`:

| Service    | Image                | Role                                                        |
|------------|----------------------|-------------------------------------------------------------|
| `backend`  | `taskora-backend`    | Laravel API (php-fpm + nginx + supervisor), internal only   |
| `frontend` | `taskora-frontend`   | Vue SPA built with Vite, served by nginx, proxies `/api`    |

The frontend nginx serves the compiled SPA **and** reverse-proxies `/api/*` to
the backend container, so the whole app is a single origin — no CORS needed.

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
# edit .env — set APP_URL / FRONTEND_URL to your server IP or domain
```

For a real domain you would typically set `HTTP_PORT=80` and put a TLS
terminator (e.g. Caddy, Traefik, or nginx) in front, or extend the compose file.

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

## 5. Common operations

```bash
# Rebuild after pulling new code
git pull && docker compose up -d --build

# Stop
docker compose down

# Stop and wipe persisted data (sqlite db, sessions, logs)
docker compose down -v

# Run an artisan command
docker compose exec backend php artisan about
```

---

## 6. Notes on data

- The backend is **self-contained**: sessions, cache and queue use the
  filesystem, and a SQLite database lives on the `backend_storage` volume.
  No external database service is required.
- To switch to MySQL/PostgreSQL later, add a `db` service to
  `docker-compose.yml` and update the `DB_*` variables in
  `backend/.env.docker` (or pass them as environment overrides).
