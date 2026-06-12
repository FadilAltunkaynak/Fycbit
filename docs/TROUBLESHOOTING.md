# Troubleshooting

## Collect Safe Diagnostics

```bash
php -v
composer --version
node --version
npm --version
mysql --version
redis-cli ping
git rev-parse --short HEAD
```

Never attach `.env`, database dumps, private keys, wallet addresses tied to
users, KYC files, or unredacted logs to a public issue.

## Backend

- HTTP 500: inspect `storage/logs/laravel.log` locally, redact before sharing.
- Database errors: run `php artisan config:clear` and `php artisan migrate:status`.
- Permission errors: ensure `storage/` and `bootstrap/cache/` are writable by
  the runtime user.
- Queue not moving: check Redis, `QUEUE_CONNECTION`, and worker logs.

## Web

- Blank page or API errors: verify `.env.local`, restart `npm run dev`, and
  inspect the browser Network panel.
- Build failure: remove `.next`, run `npm ci`, then `npm run build`.
- Websocket failure: leave broadcasting disabled or verify host/port and the
  backend broadcasting endpoint.

## Wallet Service

- Configuration validation error: compare `.env` with `.env.example`.
- Prisma failure: verify `DATABASE_URL`, run `npx prisma generate`, then
  `npm run type:check`.
- Chain errors: stop the service and confirm testnet configuration. Never
  troubleshoot with production signing keys.

