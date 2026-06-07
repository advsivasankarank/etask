# Reminder and Notification Engine Change Log

Date: 2026-06-07

## Scope

Implemented a production-grade reminder and notification engine without changing existing workflow transitions.

## Added

- Reminder templates
- Escalation rules
- Reminder delivery logs
- Scheduler service
- Dashboard notifications
- Email notification channel
- Reminder reports
- Reminder permissions
- Scheduler CLI script

## Reminder Types

- Pending Documents
- Pending PSO
- Pending Service Orders
- Workflow Follow-up
- Invoice Due
- Overdue Invoice
- Consultant Deliverables
- Client Clarification Pending
- Compliance Due Dates
- Existing E-Verification reminders remain supported

## Channels

- `IN_APP` dashboard notifications
- `EMAIL` notifications
- `WHATSAPP` reserved in schema and service flow for future extension

## Files Added

- `database/migrations/step-19-reminder-notification-engine.sql`
- `app/Repositories/ReminderRepository.php`
- `app/Services/ReminderService.php`
- `app/Services/ReminderSchedulerService.php`
- `app/Services/DashboardNotificationChannel.php`
- `app/Services/EmailNotificationChannel.php`
- `modules/Reminders/ReminderController.php`
- `modules/Reminders/views/*`
- `database/scripts/run_reminder_scheduler.php`
- `REMINDER_NOTIFICATION_CHANGELOG.md`

## Files Updated

- `routes/web.php`
- `layouts/main.php`
- `config/app.php`
- `app/Repositories/DashboardRepository.php`
- `app/Services/DashboardService.php`
- `modules/Dashboard/views/index.php`

## Reports Added

- Reminder Register
- Pending Reminder Report
- Reminder Effectiveness Report
- Escalation Report

## Validation Targets

- Scheduler execution
- Email generation path
- Dashboard alert delivery
- Escalation rule processing
