# e-Tasks RC1 Module Inventory

## Active Modules

| Module | Status | Purpose | Key Entry Points |
| --- | --- | --- | --- |
| Auth | Active | Login, logout, password change | `/login`, `/change-password`, `/logout` |
| Dashboard | Active | Workspace landing page and role-aware visibility | `/`, `/dashboard` |
| Users | Active | Internal and portal user management | `/users` |
| Clients | Active | Client master, CRM assignment, portal credentials | `/clients` |
| ClientPortal | Active | PSO creation, review, approval, rejection | `/client-portal/pso` |
| ServiceOrders | Active | SO creation and viewing | `/service-orders` |
| Workflow | Active | Stage transitions, payment, acknowledgement, closures | `/workflow/*` |
| Billing | Active | Disbursement, invoice, payment, receipt | `/billing` |
| Consultants | Active | Assignment, deliverables, bills, payments | `/consultants` |
| Documents | Active | Secure document download endpoint | `/documents/{id}/download` |
| Search | Active | Quick, global, advanced, and history search | `/search` |
| Reports | Active | Operational and financial reports | `/reports` |
| Reminders | Active | Templates, rules, scheduler, reminder reports | `/reminders` |

## Placeholder / Non-Active RC1 Modules

| Module | Status | Notes |
| --- | --- | --- |
| Attendance | Placeholder | Folder exists but no active implementation shipped in RC1 |
| Compliance | Placeholder | Folder exists but no active standalone implementation shipped in RC1 |

## Shared Application Layers

| Layer | Purpose |
| --- | --- |
| `app/Core` | bootstrap, router, request, response, config, session, exception handling |
| `app/Services` | business rules and orchestration |
| `app/Repositories` | SQL/data access |
| `app/Models` | user/session domain model |
| `app/Middleware` | auth, guest, CSRF, permission, legacy role mapping |
| `app/Helpers` | global helpers |
| `app/Views` | shared error and support views |

## Operational Tooling

| Script | Purpose |
| --- | --- |
| `migrate.php` | apply tracked migrations |
| `migration_status.php` | view migration state |
| `baseline_migrations.php` | register manual baseline |
| `backup_database.php` | create SQL backup |
| `environment_doctor.php` | validate runtime readiness |
| `backfill_aadhaar.php` | secure legacy Aadhaar rows |
| `run_reminder_scheduler.php` | execute reminder cycle |
| `run_regression_suite.php` | run RC1 regression smoke suite |
