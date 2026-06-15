#!/usr/bin/env bash
# Uruchom jako root na świeżym Ubuntu 24.04 na Hetzner:
#   bash <(curl -fsSL https://raw.githubusercontent.com/wtk13/aib/master/scripts/server-setup.sh)
# lub po skopiowaniu na serwer:
#   bash scripts/server-setup.sh

set -euo pipefail

REPO="https://github.com/wtk13/aib.git"
APP_DIR="/var/www/aib"
DEPLOY_KEY_PATH="/root/.ssh/github_actions"

echo "==> [1/6] System update"
apt-get update -q
apt-get upgrade -y -q
apt-get install -y -q git curl ufw

echo "==> [2/6] Instalacja Docker"
curl -fsSL https://get.docker.com | sh
systemctl enable --now docker

echo "==> [3/6] Konfiguracja UFW (firewall)"
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp comment 'SSH'
ufw allow 80/tcp comment 'HTTP (Cloudflare)'
ufw allow 443/tcp comment 'HTTPS (Cloudflare)'
ufw --force enable
echo "UFW status:"
ufw status verbose

echo "==> [4/6] Klonowanie repozytorium"
if [ -d "$APP_DIR/.git" ]; then
    echo "Repo już istnieje, pomijam."
else
    git clone "$REPO" "$APP_DIR"
fi

echo "==> [5/6] Generowanie klucza SSH dla GitHub Actions"
if [ ! -f "$DEPLOY_KEY_PATH" ]; then
    ssh-keygen -t ed25519 -f "$DEPLOY_KEY_PATH" -N "" -C "github-actions-deploy"
fi
cat "$DEPLOY_KEY_PATH.pub" >> /root/.ssh/authorized_keys
chmod 600 /root/.ssh/authorized_keys

echo ""
echo "============================================================"
echo "  KLUCZ PRYWATNY — wklej jako secret HETZNER_SSH_KEY w GitHub"
echo "  (GitHub → repo → Settings → Secrets → Actions)"
echo "============================================================"
cat "$DEPLOY_KEY_PATH"
echo "============================================================"
echo ""

echo "==> [6/6] Tworzenie .env.production"
if [ ! -f "$APP_DIR/.env.production" ]; then
    cp "$APP_DIR/.env.production.example" "$APP_DIR/.env.production" 2>/dev/null || \
    cp "$APP_DIR/.env.example" "$APP_DIR/.env.production"
    echo ""
    echo "UWAGA: Uzupełnij $APP_DIR/.env.production przed pierwszym deployem!"
    echo "  nano $APP_DIR/.env.production"
fi

echo ""
echo "============================================================"
echo "  SETUP GOTOWY"
echo ""
echo "  Następne kroki:"
echo "  1. Dodaj klucz prywatny (wyżej) jako HETZNER_SSH_KEY w GitHub Secrets"
echo "  2. Dodaj pozostałe sekrety:"
echo "     HETZNER_HOST = 46.225.160.41"
echo "     HETZNER_USER = root"
echo "  3. Uzupełnij: nano $APP_DIR/.env.production"
echo "  4. Push do master → deploy automatyczny"
echo "============================================================"
