# e-Tasks Production Configuration Checklist

## Application Configuration

- `APP_NAME`
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL`
- `APP_TIMEZONE=Asia/Kolkata`
- `SESSION_NAME`
- `APP_KEY` with strong random value
- `UPLOAD_MAX_BYTES`
- `PRIVATE_STORAGE_PATH`
- `MAIL_FROM_NAME`
- `MAIL_FROM_ADDRESS`

## Database Configuration

- `DB_HOST`
- `DB_PORT`
- `DB_DATABASE`
- `DB_USERNAME`
- `DB_PASSWORD`

## Backup Configuration

- `MYSQLDUMP_BINARY`
- confirm path is valid on target host

## Payments Configuration

- `RAZORPAY_KEY_ID`
- `RAZORPAY_KEY_SECRET`
- `RAZORPAY_CURRENCY`
- if Razorpay is not live, leave feature disabled or keep test configuration out of production use

## Security Configuration

- `APP_KEY` is not placeholder
- `APP_DEBUG=false`
- private storage is outside public root
- HTTPS enabled at web server / proxy layer
- secure session behavior verified

## Mail and Reminder Configuration

- sender name configured
- sender email configured
- PHP mail capability verified if email reminders are expected
- reminder scheduler execution path defined

## File System Configuration

- logs writable
- temp writable
- backups writable
- reports writable
- secure storage writable

## Operational Validation

- environment doctor returns zero errors
- migration status returns no pending files
- regression suite passes

## Freeze Control

Before go-live confirm:

- no new migrations added after RC1 freeze
- no new permissions added after RC1 freeze
- no new routes added after RC1 freeze
