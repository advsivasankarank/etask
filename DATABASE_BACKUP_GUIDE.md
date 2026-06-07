# e-Tasks Database Backup Guide

## Purpose

This guide explains how to back up the e-Tasks database safely before deployments, migrations, or production cutover.

## Built-In Backup Script

Use:

```bash
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\backup_database.php
```

## What the Script Does

- loads application configuration
- resolves the `mysqldump` binary from `MYSQLDUMP_BINARY`
- creates a timestamped SQL dump
- writes output into:
  - `storage/backups/`

## Default Windows mysqldump Path

If not overridden:

```text
C:\xampp\mysql\bin\mysqldump.exe
```

## Backup Output Naming

Generated file format:

```text
{database_name}_backup_YYYYMMDD_HHMMSS.sql
```

## Recommended Backup Timing

Take backups:

- before every deployment
- before every migration run
- before permission reseeding
- before route or environment reconfiguration

## Manual Verification

After backup:

1. confirm the SQL file exists in `storage/backups/`
2. confirm file size is greater than zero
3. keep one off-server copy for production environments

## Recommended Retention

- daily backups: retain 7
- weekly backups: retain 4
- monthly backups: retain 3

## Restore Guidance

To restore on MySQL:

```bash
mysql -h HOST -P PORT -u USER -p DATABASE_NAME < backup_file.sql
```

Use a controlled maintenance window for production restore operations.

## Backup Safety Notes

- never deploy without a current backup
- test restore process in staging before production go-live
- ensure database credentials used by backup script are valid
- ensure `storage/backups/` is writable
- ensure the backup location is not publicly exposed
