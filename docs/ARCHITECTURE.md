# Architecture

The Laravel backend owns application APIs, authentication, queues, exchange
workflows, migrations, and optional domain modules. The Next.js client presents
the user interface and calls backend APIs. The wallet service isolates
blockchain integration concerns. MySQL stores application state and Redis
supports cache, queues, and real-time workloads.

Production deployment requires explicit trust boundaries, key management,
network segmentation, audit logging, rate limiting, backup/restore testing,
monitoring, and incident response.

