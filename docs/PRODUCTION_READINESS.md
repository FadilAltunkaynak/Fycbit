# Production Readiness

The public baseline is not production-ready. A successful local build proves
only that source dependencies and compilation work in that environment.

Before production:

1. Resolve source ownership and license questions.
2. Remove browser-visible shared-secret authentication.
3. Complete application, infrastructure, wallet, and custody security audits.
4. Define supported chains and disable every unused integration.
5. Use a managed secret store and documented key-rotation process.
6. Segment web, API, workers, databases, Redis, and wallet infrastructure.
7. Add TLS, firewall rules, rate limits, monitoring, alerts, and audit logs.
8. Test backup restoration and database migration rollback.
9. Complete legal, privacy, KYC/AML, sanctions, and jurisdictional review.
10. Perform load, failure, replay, idempotency, and incident-response tests.

## Release Gate

A release must not be described as production-ready while any required test,
build, audit, restore test, or legal review remains incomplete.

