# e-Tasks Deployment Guide

## Purpose

This guide covers deployment of e-Tasks RC1 from localhost or staging to a production-style hosting environment compatible with PHP 8.x and MySQL.

## Target Stack

- PHP 8.x
- MySQL
- Apache or compatible web server
- shared hosting or cPanel-compatible environment

## Directory Model

Application root:

- `C:\xampp\htdocs\e-task`

Web entry point:

- `public/index.php`

Private document storage:

- path defined by `PRIVATE_STORAGE_PATH`

## Pre-Deployment Steps

1. take a full database backup
2. copy the application codebase
3. confirm `.env` values for target environment
4. confirm writable paths
5. confirm `APP_KEY` is set and secure

## Deployment Sequence

### 1. Upload code

Deploy the project files to the target host while preserving:

- `app/`
- `bootstrap/`
- `config/`
- `database/`
- `layouts/`
- `modules/`
- `public/`
- `routes/`
- `storage/`

### 2. Configure environment

Set environment values for:

- `APP_NAME`
- `APP_ENV`
- `APP_DEBUG`
- `APP_URL`
- `APP_TIMEZONE`
- `SESSION_NAME`
- `APP_KEY`
- `UPLOAD_MAX_BYTES`
- `PRIVATE_STORAGE_PATH`
- `MAIL_FROM_NAME`
- `MAIL_FROM_ADDRESS`
- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`
- `MYSQLDUMP_BINARY`
- `RAZORPAY_KEY_ID`
- `RAZORPAY_KEY_SECRET`
- `RAZORPAY_CURRENCY`

### 3. Prepare writable paths

Ensure write access for:

- `storage/logs`
- `storage/temp`
- `storage/uploads`
- `storage/backups`
- `storage/reports`
- private storage directory from `PRIVATE_STORAGE_PATH`

### 4. Validate environment

```bash
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\environment_doctor.php
```

### 5. Confirm migration state

```bash
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\migration_status.php
```

### 6. Apply migrations if needed

```bash
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\migrate.php
```

### 7. Run regression suite

```bash
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\run_regression_suite.php
```

## Post-Deployment Validation

Validate these flows:

- login
- password change
- user list
- client create and edit
- portal credential save
- PSO create/review/approve
- SO create
- workflow milestone progression
- invoice create
- payment and receipt create
- secure document download
- search
- reminders
- reports

## Web Server Notes

- application should route through `public/index.php`
- public web access must not expose private document storage
- keep `APP_DEBUG=false` in production

## Deployment Rollback

If deployment fails:

1. restore previous code package
2. restore database backup if schema or data integrity is affected
3. validate environment doctor
4. rerun smoke validation

## RC1 Release Rule

For RC1, do not alter:

- schema
- permission catalog
- route map

without treating the change as a post-freeze release change.
