# e-Pani Production Readiness

Project root: `C:\xampp\htdocs\e-task`

## Phase 2 Tooling

The application now includes CLI-safe operational scripts under `database/scripts/`:

- `migrate.php` - applies pending SQL files from `database/migrations/` and records them in `schema_migrations`
- `migration_status.php` - shows applied vs pending migrations and checksum mismatches
- `baseline_migrations.php` - one-time registration tool for legacy databases where SQL was applied manually before migration tracking existed
- `backup_database.php` - creates a SQL dump under `storage/backups/`
- `environment_doctor.php` - validates key production-readiness checks
- `backfill_aadhaar.php` - encrypts legacy Aadhaar rows after the security migration is applied

The database migration set now also includes RBAC permission tables and seeded role-permission mappings in:

- `database/migrations/step-15-rbac-permissions.sql`
- `database/migrations/step-16-reports-permissions.sql`
- `database/migrations/step-17-document-access-layer.sql`

## Secure Document Layer

The application now serves documents through an authenticated, audited endpoint:

- `GET /documents/{id}/download`

Key controls:

- access only after authentication
- permission or ownership validation inside the document access service
- consultant and client self-access restrictions
- download access logging into `activity_logs`
- new uploads stored under `PRIVATE_STORAGE_PATH` outside the public web root
- legacy `storage/` web access blocked with `.htaccess`

## Recommended Command Set

Run migration status:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\migration_status.php
```

Apply pending migrations:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\migrate.php
```

Baseline an existing manual database one time:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\baseline_migrations.php
```

Create a database backup:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\backup_database.php
```

Run the environment doctor:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\environment_doctor.php
```

## Production Checklist

- Set `APP_DEBUG=false`
- Rotate `APP_KEY` to a long random production value
- Set the production `APP_URL`
- Set `PRIVATE_STORAGE_PATH` to a writable private path outside the public web root
- Verify writable permissions for `storage/logs`, `storage/temp`, `storage/uploads`, and `storage/backups`
- Run `migration_status.php` and confirm no pending migrations
- Re-login after RBAC migrations so active sessions receive updated permission grants
- Create a fresh SQL backup before every deployment
- Verify login, password change, client create/edit, service order create, workflow transitions, and billing flows after deployment

## Notes

- `schema_migrations` is created automatically by the migration service
- Backups are written outside the public web root
- CLI scripts are designed to work with XAMPP locally and are suitable for controlled use on cPanel-style hosting
