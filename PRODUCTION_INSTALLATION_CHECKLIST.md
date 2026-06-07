# e-Tasks Production Installation Checklist

## Server Readiness

- PHP 8.x available
- MySQL available
- web server points application traffic to `public/`
- shell or CLI access available for admin scripts

## Application Files

- upload full application code
- confirm `public/index.php` exists
- confirm `bootstrap/`, `app/`, `modules/`, `config/`, `routes/`, `database/`, and `storage/` are present

## Environment

- create production `.env`
- set `APP_ENV=production`
- set `APP_DEBUG=false`
- set production `APP_URL`
- set strong `APP_KEY`

## Database

- create MySQL database
- create DB user
- grant required privileges
- set DB connection values
- import base schema if using fresh setup
- run tracked migrations

## Writable Paths

- `storage/logs`
- `storage/temp`
- `storage/backups`
- `storage/reports`
- `storage/uploads`
- `PRIVATE_STORAGE_PATH`

## Validation Commands

- run `migration_status.php`
- run `environment_doctor.php`
- run `run_regression_suite.php`

## Functional Smoke Checks

- login works
- password change works
- client list opens
- service order list opens
- billing opens
- reports open
- search works
- reminders open
- secure document download works

## Backup Safety

- create pre-go-live SQL backup
- store one backup copy outside server

## Final RC1 Signoff

- schema matches RC1 freeze
- permissions match RC1 freeze
- routes match RC1 freeze
- no post-freeze feature code added
