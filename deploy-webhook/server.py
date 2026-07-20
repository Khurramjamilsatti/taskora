#!/usr/bin/env python3
"""Minimal deploy webhook: POST /deploy with Bearer token triggers docker compose pull/up."""

from __future__ import annotations

import hmac
import json
import os
import subprocess
import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from typing import Dict, Optional


SECRET = os.environ.get("DEPLOY_WEBHOOK_SECRET", "")
PORT = int(os.environ.get("WEBHOOK_PORT", "9100"))
WORKSPACE = os.environ.get("DEPLOY_WORKSPACE", "/workspace")
LOCK = threading.Lock()


def run(cmd: list, env: Optional[Dict[str, str]] = None) -> None:
    merged = os.environ.copy()
    if env:
        merged.update(env)
    print("+", " ".join(cmd), flush=True)
    subprocess.run(cmd, cwd=WORKSPACE, env=merged, check=True)


def deploy(image: str, ghcr_user: str, ghcr_token: str) -> None:
    with LOCK:
        if ghcr_user and ghcr_token:
            login = subprocess.run(
                ["docker", "login", "ghcr.io", "-u", ghcr_user, "--password-stdin"],
                input=ghcr_token.encode(),
                cwd=WORKSPACE,
                check=False,
            )
            if login.returncode != 0:
                raise RuntimeError("docker login to ghcr.io failed")

        try:
            run(["docker", "compose", "pull", "app"], env={"APP_IMAGE": image})
            run(
                ["docker", "compose", "up", "-d", "--no-build", "--remove-orphans"],
                env={"APP_IMAGE": image},
            )
            run(["docker", "image", "prune", "-f"])
        finally:
            subprocess.run(["docker", "logout", "ghcr.io"], cwd=WORKSPACE, check=False)


class Handler(BaseHTTPRequestHandler):
    def log_message(self, fmt: str, *args) -> None:
        print("%s - %s" % (self.address_string(), fmt % args), flush=True)

    def _read_json(self) -> dict:
        length = int(self.headers.get("Content-Length", "0") or 0)
        raw = self.rfile.read(length) if length else b"{}"
        if not raw:
            return {}
        return json.loads(raw.decode("utf-8"))

    def _unauthorized(self) -> None:
        self.send_response(401)
        self.send_header("Content-Type", "application/json")
        self.end_headers()
        self.wfile.write(b'{"ok":false,"error":"unauthorized"}')

    def _bad_request(self, message: str) -> None:
        self.send_response(400)
        self.send_header("Content-Type", "application/json")
        self.end_headers()
        self.wfile.write(json.dumps({"ok": False, "error": message}).encode())

    def _ok(self, payload: dict) -> None:
        body = json.dumps(payload).encode()
        self.send_response(200)
        self.send_header("Content-Type", "application/json")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def _authorized(self) -> bool:
        if not SECRET:
            return False
        auth = self.headers.get("Authorization", "")
        token = ""
        if auth.lower().startswith("bearer "):
            token = auth[7:].strip()
        else:
            token = self.headers.get("X-Deploy-Token", "").strip()
        return hmac.compare_digest(token, SECRET)

    def do_GET(self) -> None:
        if self.path.rstrip("/") == "/health":
            self._ok({"ok": True, "service": "taskora-deploy-webhook"})
            return
        self.send_response(404)
        self.end_headers()

    def do_POST(self) -> None:
        if self.path.rstrip("/") != "/deploy":
            self.send_response(404)
            self.end_headers()
            return

        if not self._authorized():
            self._unauthorized()
            return

        try:
            data = self._read_json()
        except json.JSONDecodeError:
            self._bad_request("invalid json")
            return

        image = (data.get("image") or "").strip()
        if not image:
            self._bad_request("image is required")
            return

        ghcr_user = (data.get("ghcr_user") or "").strip()
        ghcr_token = (data.get("ghcr_token") or "").strip()

        try:
            deploy(image, ghcr_user, ghcr_token)
        except Exception as exc:  # noqa: BLE001 — surface deploy errors to caller
            print(f"deploy failed: {exc}", flush=True)
            body = json.dumps({"ok": False, "error": str(exc)}).encode()
            self.send_response(500)
            self.send_header("Content-Type", "application/json")
            self.send_header("Content-Length", str(len(body)))
            self.end_headers()
            self.wfile.write(body)
            return

        self._ok({"ok": True, "image": image})


def main() -> None:
    if not SECRET:
        raise SystemExit("DEPLOY_WEBHOOK_SECRET is required")
    server = ThreadingHTTPServer(("0.0.0.0", PORT), Handler)
    print(f"Deploy webhook listening on :{PORT}", flush=True)
    server.serve_forever()


if __name__ == "__main__":
    main()
