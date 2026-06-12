# Installation

These instructions create a local development environment only. Commands are
written for Ubuntu 24.04. Do not use real funds, production credentials, or
personal data.

## Requirements

- PHP 8.1 with CLI, FPM, MySQL, BCMath, cURL, XML, Mbstring, ZIP, GD, and Intl
- Composer
- Node.js 18 and npm
- MySQL 8
- Redis 7

## 1. Install System Packages

```bash
sudo apt update
sudo apt install -y git curl unzip mysql-server redis-server \
  php8.3-cli php8.3-fpm php8.3-mysql php8.3-bcmath php8.3-curl \
  php8.3-xml php8.3-mbstring php8.3-zip php8.3-gd php8.3-intl
curl -sS https://getcomposer.org/installer | php
sudo install -m 0755 composer.phar /usr/local/bin/composer
```

Install Node.js 18 using your organization-approved package source, then
verify:

```bash
php -v
composer --version
node --version
npm --version
mysql --version
redis-cli ping
```

`redis-cli ping` must return `PONG`.

## 2. Clone the Review Branch

```bash
git clone --branch staging-publication --single-branch https://github.com/FadilAltunkaynak/Fycbit.git
cd Fycbit
```

## 3. Create an Empty Development Database

```bash
sudo mysql
```

Run in the MySQL prompt:

```sql
CREATE DATABASE fycbit CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'fycbit'@'localhost' IDENTIFIED BY 'replace-with-a-local-password';
GRANT ALL PRIVILEGES ON fycbit.* TO 'fycbit'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Backend

```bash
cd apps/backend
composer install
cp ../../.env.example .env
chmod 600 .env
php artisan key:generate
php artisan config:clear
php artisan migrate
php artisan serve
```

Edit `apps/backend/.env` before migration and set the local database password.
Use a dedicated empty database. Do not import production dumps.

Verify in another terminal:

```bash
curl -I http://127.0.0.1:8000
```

## Web

```bash
cd apps/web
npm ci
npm run dev
```

Open `http://localhost:3000`. API base URL settings may require adjustment for
your local backend; inspect the web configuration before enabling modules.

## Wallet Service

```bash
cd apps/wallet-service
npm ci
npx prisma generate
npm run type:check
npm run build
```

Keep the wallet service on testnet/devnet. Never place seed phrases or private
keys in Git, `.env.example`, screenshots, logs, or issue reports.

The wallet service shares the MySQL schema and requires its own local `.env`
with a `DATABASE_URL`. Do not start it until every enabled chain is explicitly
configured for testnet/devnet.

## Verification Checklist

```bash
cd apps/backend
php artisan about
php artisan migrate:status
php artisan test

cd ../web
npm run build

cd ../wallet-service
npm run type:check
npm run build
```

Do not continue to deployment while any command fails.

## Known Boundaries

- This baseline was sanitized from a running system and has not yet been
  proven by a clean-room end-to-end installation.
- Some optional modules may require licensed third-party packages or additional
  provider configuration.
- Public uploads and production branding are intentionally absent.
- No production deployment or real-fund custody procedure is provided.

Record installation failures without secrets in a GitHub issue. Security
findings must follow `SECURITY.md`.
