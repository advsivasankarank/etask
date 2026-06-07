# Security Change Log

## Scope
Critical security hardening only. No business workflow, numbering logic, role routing, billing flow, consultant flow, PSO flow, or compliance workflow was changed.

## Completed Changes

### Secure file upload validation
- Strengthened upload validation in `app/Services/DocumentUploadService.php`.
- Added `is_uploaded_file()` verification.
- Kept MIME-type and allowed-extension validation.
- Continued blocking executable and script extensions.
- Added suspicious double-extension rejection like `file.php.jpg`.
- Kept upload size enforcement from `UPLOAD_MAX_BYTES`.

### Upload execution blocking
- Added `storage/uploads/.htaccess`.
- Added automatic `.htaccess` protection creation for runtime upload directories.
- Denied execution of PHP and other script-like files inside upload storage.

### Debug mode suppression
- Confirmed `APP_DEBUG=false` in `.env`.
- Confirmed `APP_DEBUG=false` in `.env.example`.
- Browser error display remains disabled through centralized exception handling.

### Centralized error handling
- Confirmed centralized exception handling in `app/Core/ExceptionHandler.php`.
- Added fatal shutdown handling so unrecoverable PHP fatal errors also resolve through the centralized logger and generic `500` response.

### Aadhaar encryption
- Confirmed Aadhaar writes in `app/Services/ClientService.php` are encrypted through `app/Services/EncryptionService.php`.
- Confirmed plaintext `aadhaar_no` is stored as `null` during create and update flows.
- Confirmed encrypted values are stored in `aadhaar_ciphertext` and `aadhaar_iv`.
- Confirmed `aadhaar_last4` remains available for masked display without exposing the full number.

### APP_KEY enforcement
- Strengthened startup validation in `bootstrap/app.php`.
- Application now rejects:
  - blank `APP_KEY`
  - keys shorter than 32 characters
  - obvious placeholder values
- Encryption service now rejects weak or placeholder keys before sensitive data is encrypted or decrypted.

### Sensitive log redaction
- Expanded redaction in `app/Core/Logger.php`.
- Redacts passwords, password hashes, CSRF tokens, Aadhaar fields, and app key references from structured logs.

## Files Updated
- `app/Core/ExceptionHandler.php`
- `app/Core/Logger.php`
- `app/Services/EncryptionService.php`
- `app/Services/DocumentUploadService.php`
- `bootstrap/app.php`
- `storage/uploads/.htaccess`
- `SECURITY_CHANGELOG.md`

## Validation Intent
- No business workflow changes.
- No route, approval, numbering, or lifecycle rule changes.
- Hardening is limited to startup validation, error handling, secure storage, upload safety, and safer logging.
