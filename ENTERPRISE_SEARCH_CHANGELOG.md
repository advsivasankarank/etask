# Enterprise Search Change Log

Date: 2026-06-07

## Scope

Implemented a production-grade Enterprise Search module without changing business workflows.

## Added

- Global Search workspace
- Quick Search endpoint for header search
- Advanced Search with source-based filters
- Role-based search visibility
- Search History with audit trail
- Search permissions and migration

## Search Sources

- Clients
- Service Orders
- Portal Credentials
- Invoices
- Receipts
- Consultants
- Documents

## Security and Audit Controls

- Search results are trimmed by role and ownership context
- Client users only see their own operational and financial records
- Consultant users only see their own consultant-linked records and documents
- Portal credential results never expose passwords
- Every search writes to:
  - `search_history`
  - `activity_logs`

## Files Added

- `app/Repositories/SearchRepository.php`
- `app/Services/SearchService.php`
- `modules/Search/SearchController.php`
- `modules/Search/views/index.php`
- `modules/Search/views/quick.php`
- `modules/Search/views/advanced.php`
- `modules/Search/views/history.php`
- `modules/Search/views/partials/results.php`
- `database/migrations/step-18-enterprise-search.sql`
- `ENTERPRISE_SEARCH_CHANGELOG.md`

## Files Updated

- `routes/web.php`
- `layouts/main.php`
- `RBAC.md`
- `STRUCTURE.md`

## Permissions Added

- `search.view`
- `search.quick`
- `search.advanced`
- `search.history`
- `search.audit`

## Notes

- No business workflow transitions were modified
- Search module uses the existing RBAC and audit logging architecture
- Document results use the secure `/documents/{id}/download` endpoint
