# Installation

These instructions create a local development environment only.

## Requirements

- PHP compatible with the backend `composer.json`
- Composer
- Node.js and npm
- MySQL
- Redis

## Backend

```bash
cd apps/backend
composer install
cp ../../.env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Use a dedicated empty database. Do not import production dumps.

## Web

```bash
cd apps/web
npm ci
npm run dev
```

## Wallet Service

```bash
cd apps/wallet-service
npm ci
npm run build
```

Keep the wallet service on testnet/devnet. Never place seed phrases or private
keys in Git, `.env.example`, screenshots, logs, or issue reports.

