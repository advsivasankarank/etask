# e-Tasks Version 1.0 RC1 Release Notes

## Release Status

- Product: `e-Tasks`
- Release Candidate: `v1.0 RC1`
- Codebase root: `C:\xampp\htdocs\e-task`
- Release intent: production preparation and documentation freeze
- Feature status: no new features added as part of RC1 packaging

## RC1 Freeze Baseline

### Schema Freeze

RC1 schema is frozen at:

- base schema: `step-1-database-schema.sql`
- tracked migrations:
  - `step-5-workflow-engine.sql`
  - `step-10-client-master.sql`
  - `step-11-client-portal-credentials.sql`
  - `step-12-client-portal-label.sql`
  - `step-13-service-order-periods.sql`
  - `step-14-security-hardening.sql`
  - `step-15-rbac-permissions.sql`
  - `step-16-reports-permissions.sql`
  - `step-17-document-access-layer.sql`
  - `step-18-enterprise-search.sql`
  - `step-19-reminder-notification-engine.sql`

Tracked RC1 migration count: `11`

### Permission Freeze

RC1 permission baseline is frozen at the permission set introduced through:

- `step-15-rbac-permissions.sql`
- `step-16-reports-permissions.sql`
- `step-17-document-access-layer.sql`
- `step-18-enterprise-search.sql`
- `step-19-reminder-notification-engine.sql`

Frozen permission count: `52`

### Route Freeze

RC1 route baseline is frozen at:

- `routes/web.php`

Registered route count at freeze: `83`

## Functional Scope Included in RC1

- Authentication and password reset flow
- Permission-based RBAC
- Client Master
- Portal credential management
- Client Portal / PSO
- Service Order management
- Workflow progression and closures
- Billing, invoice, payment, and receipt processing
- Consultant workflow
- Secure document management and audited downloads
- Enterprise Search
- Reports
- Reminder and notification engine

## Functional Scope Not Released as Active RC1 Modules

- Attendance module: folder placeholder only
- Compliance module: folder placeholder only

These are not active RC1 feature modules and should not be presented as production-available workflows.

## Security and Operations Included in RC1

- APP_KEY enforcement
- debug disabled for production-style runtime
- centralized exception handling
- CSRF protection
- encrypted Aadhaar storage
- secure document download endpoint
- audited document access logging
- role-to-permission migration baseline
- reminder delivery and escalation logging
- CLI migration, backup, and environment-doctor scripts

## Validation Snapshot

- regression smoke suite: `13/13 passed`
- latest validated runtime: under `1 second` locally
- HTML regression report generated in:
  - `storage/reports/regression/`

## Release Artifacts

- `RELEASE_NOTES_v1.0.md`
- `SYSTEM_ADMIN_GUIDE.md`
- `USER_MANUAL.md`
- `DEPLOYMENT_GUIDE.md`
- `DATABASE_BACKUP_GUIDE.md`
- `MODULE_INVENTORY_RC1.md`
- `ARCHITECTURE_RC1.md`
- `PRODUCTION_INSTALLATION_CHECKLIST.md`
- `PRODUCTION_CONFIGURATION_CHECKLIST.md`

## RC1 Recommendation

RC1 is suitable for:

- controlled UAT
- staging deployment
- production preparation
- administrator training

RC1 should be treated as the frozen release candidate baseline until a formal v1.0 final signoff is completed.
