# Glossary

- Backend: Laravel application exposing HTTP APIs and background jobs.
- Web: Next.js browser application.
- Wallet service: Node.js service for blockchain-facing workflows.
- Queue: Asynchronous work stored in Redis and consumed by workers.
- Horizon: Laravel dashboard and supervisor for Redis queues.
- Migration: Versioned database schema change.
- Testnet/devnet: Blockchain network with non-production assets.
- Mainnet: Production blockchain network with assets that may have real value.
- Custody: Control or storage of user assets or signing keys.
- Secret: Credential or key that must never be sent to a browser or committed.
- `NEXT_PUBLIC_*`: Next.js build-time value embedded into browser JavaScript;
  it is always public.
- Clean-room installation: Installation on a new machine using only public
  repository documentation and no production files.
- Rollback: Returning code and database state to a previously verified release.

