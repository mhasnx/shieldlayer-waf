# ShieldLayer — Multi-Tenant Web Application Firewall (WAF) & SOC Platform

A production-ready, custom PHP (MVC) Web Application Firewall and Security Operations Center designed for high-throughput multi-tenant environments.

---

## Key Features

- **Multi-Tenant Architecture**: Schema-level isolation (`tenants`, `tenant_users`) with dynamic tenant provisioning and tenant context switching.
- **Deep Payload Inspection (WAF)**: Stateful signature scanning against SQL Injection (SQLi), Cross-Site Scripting (XSS), and Path Traversal with instant `403 Forbidden` termination.
- **DoS & Brute-Force Defense**: Sliding-window rate-limiting engine enforcing `429 Too Many Requests` per-IP/tenant burst thresholds.
- **Dynamic Policy Enforcement**: Real-time tenant-level IP blacklisting and rule management directly from the dashboard.
- **Role-Based Access Control (RBAC)**: Fine-grained authorization tiers (`Owner`, `Analyst`, `Viewer`) with team management and seat assignment.
- **Telemetry & Threat Intelligence**: Live attack stream, vector-based metric breakdown, and RFC-4180 compliant CSV export for compliance and auditing.
- **System Posture & Health Engine**: Real-time evaluation of server configuration, session security flags, and PHP runtime posture.

---

## Tech Stack

- **Backend**: PHP 8.1+ (Pure Custom MVC, Front-Controller Architecture)
- **Database**: MySQL / MariaDB (PDO, Prepared Statements)
- **Security Primitives**: Argon2id Hashing, Timing-Safe CSRF (`hash_equals`), Session Hardening (SameSite/HttpOnly)
- **Dependency Management**: Composer (PSR-4 Autoloading)