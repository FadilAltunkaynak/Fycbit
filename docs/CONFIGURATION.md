# Configuration

Start from `.env.example` and set values locally. Required values vary by
enabled modules.

Never commit:

- Database, Redis, SMTP, payment, exchange, or Telegram credentials
- JWT/application secrets
- Wallet private keys, mnemonics, signing keys, or custody material
- Cloud, CDN, DNS, or TLS credentials
- User, KYC, transaction, or production address data

Use a secret manager in deployed environments and rotate exposed values.

