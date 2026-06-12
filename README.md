# Fycbit

Fycbit is a multi-service cryptocurrency exchange and trading platform
baseline containing a Laravel backend, Next.js web client, and Node.js wallet
service.

Turkce ayrintili urun ve ozellik tanitimi:
[Fycbit Platform Tanitimi](docs/PRODUCT_OVERVIEW_TR.md).

> This software is not financial advice. Cryptocurrency trading and custody
> involve substantial risk. This public baseline is not represented as
> production-ready, audited, or suitable for custody of real assets.

## Repository Layout

| Path | Description |
| --- | --- |
| `apps/backend` | Laravel API, jobs, migrations, modules, and tests |
| `apps/web` | Next.js web client |
| `apps/wallet-service` | Node.js wallet integration service |
| `docs` | Installation, architecture, operations, and security guides |

## Current Status

This is a sanitized review baseline. Production credentials, `.env` files,
database dumps, TLS keys, KYC/user data, uploads, logs, dependency folders, and
runtime build output are excluded.

The software has not completed an independent security, custody, smart
contract, financial, or compliance audit. Use testnet/devnet and synthetic data
only.

## Quick Start

Prerequisites: PHP 8.x, Composer, Node.js, MySQL, and Redis.

```bash
git clone --branch staging-publication --single-branch https://github.com/FadilAltunkaynak/Fycbit.git
cd Fycbit
cp .env.example apps/backend/.env
cd apps/backend
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

In another terminal:

```bash
cd apps/web
npm ci
npm run dev
```

These are development instructions only. Review
[Installation](docs/INSTALLATION.md) before running any service.

The public baseline does not include production data, uploads, secrets, or
compiled assets. A successful source installation is not a production
deployment or custody approval.

## Security

Never commit exchange keys, wallet keys, seed phrases, signing material,
payment credentials, SMTP credentials, KYC records, user exports, database
dumps, or production configuration. See [SECURITY.md](SECURITY.md).

## Documentation

- [Installation](docs/INSTALLATION.md)
- [Turkce ayrintili kurulum](docs/INSTALLATION_TR.md)
- [Environment reference](docs/ENVIRONMENT_REFERENCE.md)
- [Troubleshooting](docs/TROUBLESHOOTING.md)
- [Glossary](docs/GLOSSARY.md)
- [Backup and restore](docs/BACKUP_AND_RESTORE.md)
- [Production readiness](docs/PRODUCTION_READINESS.md)
- [Feature matrix](docs/FEATURE_MATRIX.md)
- [Architecture](docs/ARCHITECTURE.md)
- [Configuration](docs/CONFIGURATION.md)
- [Operations](docs/OPERATIONS.md)
- [Public release checklist](docs/PUBLIC_RELEASE_CHECKLIST.md)
- [Wiki index](docs/WIKI.md)

## License

No broad public-use license has been granted yet. See [LICENSE.md](LICENSE.md)
and [LICENSE_DECISION_REQUIRED.md](LICENSE_DECISION_REQUIRED.md). Third-party
components remain governed by their respective licenses.
