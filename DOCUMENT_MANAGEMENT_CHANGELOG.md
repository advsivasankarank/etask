# Secure Document Management Change Log

## Scope

Production-grade document access hardening only. No business workflow, numbering, approval chain, billing logic, consultant lifecycle, or compliance workflow was changed.

## Completed Changes

### Secure download endpoint

- Added `modules/Documents/DocumentController.php`
- Added authenticated download route:
  - `/documents/{id}/download`
- Route now resolves through dynamic router pattern support

### Authorization and ownership checks

- Added `app/Services/DocumentAccessService.php`
- Enforces:
  - user authentication
  - internal permission validation
  - client ownership validation for portal users
  - consultant self-access validation for consultant-linked documents
  - module-aware access validation by linked module

### Document access logging

- Added document access logging via `activity_logs`
- Logs include:
  - user
  - timestamp
  - document id
  - action code
  - IP address
  - user agent

### Private storage for new uploads

- New uploads are now stored under `PRIVATE_STORAGE_PATH`
- Default local path is:
  - `C:\xampp\epani_private_storage`
- This path is outside `C:\xampp\htdocs`

### Direct access protection

- Added `storage/.htaccess`
- Existing legacy `storage/` paths are blocked from direct web browsing on Apache
- UI no longer exposes raw `latest_file_path` values in:
  - Clients
  - Client Portal
  - Consultants

### Document Access Report

- Added report route:
  - `/reports/document-access`
- Added filterable access report for:
  - success
  - denied
  - missing-file events

### Permissions

- Added:
  - `documents.download`
  - `documents.report`

## Files Added

- `app/Repositories/DocumentRepository.php`
- `app/Services/DocumentAccessService.php`
- `modules/Documents/DocumentController.php`
- `modules/Reports/views/document_access.php`
- `database/migrations/step-17-document-access-layer.sql`
- `storage/.htaccess`
- `DOCUMENT_MANAGEMENT_CHANGELOG.md`

## Files Updated

- `app/Core/Request.php`
- `app/Core/Router.php`
- `app/Core/Response.php`
- `app/Repositories/ClientRepository.php`
- `app/Repositories/PsoRepository.php`
- `app/Repositories/ConsultantRepository.php`
- `app/Services/DocumentUploadService.php`
- `app/Services/EnvironmentDoctorService.php`
- `config/app.php`
- `modules/Clients/views/show.php`
- `modules/ClientPortal/views/show.php`
- `modules/Consultants/views/show.php`
- `modules/Reports/ReportController.php`
- `modules/Reports/views/index.php`
- `routes/web.php`

## Validation Summary

- PHP lint passed on all touched files
- Migration `step-17-document-access-layer.sql` applied
- Environment doctor reports all checks `OK`
- Unauthenticated request to `/documents/1/download` redirects to login through auth middleware
