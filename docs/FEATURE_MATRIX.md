# Feature Matrix

| Domain | Capability | Source Evidence | Status |
| --- | --- | --- | --- |
| Identity | Registration, login, password reset, email verification | Backend API routes | Source present |
| Security | 2FA, phone verification, API whitelist | Profile/auth routes | Source present; audit required |
| KYC | Document upload and verification workflows | Profile/KYC routes | Source present; privacy review required |
| Spot trading | Market, limit, stop-limit, order history | Exchange routes and migrations | Source present; trading tests required |
| Futures | Futures pages and module | `FutureTrade`, web futures pages | Optional module; validation required |
| Demo trading | Demo wallets and trading flows | `DemoTrade` module | Source present |
| Wallets | Deposit, withdrawal, transfers, networks | Wallet routes and migrations | Source present; custody audit required |
| Blockchain | EVM, Solana, Tron processing | Wallet-service source/config | Source present; testnet only |
| Fiat | Bank deposit and withdrawal | Fiat routes/migrations | Source present; provider setup required |
| P2P | Ads, orders, trade and gift cards | `P2P` module and pages | Optional module |
| Staking | Offers, investments, earnings | Staking routes/migrations | Source present; legal review required |
| Launchpad | ICO phases and purchases | `IcoLaunchpad` module | Optional module |
| Content | Blog, news, FAQ, knowledge base | Modules and API routes | Source present |
| Support | Tickets, chat, notifications | Web pages and backend models | Source present |
| Admin | Users, roles, coins, networks, reports | Admin controllers/routes | Source present |
| Localization | Multi-language resources | Backend/web locale folders | Source present |
| Operations | Queues, Horizon, scheduled commands | Console commands/config | Source present |

“Source present” means code and schema elements were found. It does not mean
the feature passed end-to-end, security, legal, or production verification.

