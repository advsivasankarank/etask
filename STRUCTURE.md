# Compliance Management System Structure

Project root: `C:\xampp\htdocs\e-task`

## Top-Level Layout

- `public/` - Web entry point and public assets
- `app/` - Core application classes shared across modules
- `config/` - Environment and application configuration
- `modules/` - Business modules grouped by domain
- `layouts/` - Shared UI layouts, partials, and reusable view components
- `storage/` - Logs, cache, temporary files, and uploaded documents
- `bootstrap/` - Application bootstrapping and initialization
- `routes/` - Route registration files
- `database/` - Migrations and seeders

## App Layer

- `app/Core/` - Kernel, router, base controller, base model, response helpers
- `app/Controllers/` - Cross-module controllers when needed
- `app/Models/` - Shared models and base ORM-style abstractions
- `app/Services/` - Shared services such as auth, numbering, notifications
- `app/Repositories/` - Data access abstractions
- `app/Helpers/` - Small reusable helper functions
- `app/Middleware/` - Auth, permission, CSRF, and request middleware
- `app/Policies/` - Access-control policies
- `app/Validators/` - Input validation classes
- `app/Exceptions/` - Custom exception classes
- `app/Traits/` - Reusable traits
- `app/Views/` - Shared non-module-specific views

## Modules

- `modules/Auth/` - Login, logout, password change, session controls
- `modules/ServiceOrders/` - SO creation, assignment, search, locking, closure
- `modules/Workflow/` - Workflow engine, milestones, transitions, stage history
- `modules/ClientPortal/` - PSO, document upload, query response, status tracking
- `modules/Billing/` - Invoice, payment, receipt, disbursement, Razorpay integration
- `modules/Consultants/` - Assignment, deliverables, internal review, consultant billing
- `modules/Documents/` - Secure document download endpoint and access-control enforcement
- `modules/Attendance/` - Login/logout tracking, active/idle time, task linkage
- `modules/Compliance/` - ITR, GST, TDS tracking, reminders, SLA monitoring
- `modules/Dashboard/` - Role-based dashboards and KPIs
- `modules/Reports/` - Registers, financial summaries, GST summary, and revenue analytics
- `modules/Search/` - Global, quick, advanced, and role-based enterprise search
- `modules/Reminders/` - Reminder templates, scheduler, escalation rules, notifications, and reminder reports

## Reports Module Details

- `app/Repositories/ReportRepository.php` - Centralized SQL queries for reports
- `app/Repositories/SearchRepository.php` - Enterprise search queries and search-history persistence
- `app/Repositories/ReminderRepository.php` - Reminder templates, scheduler source queries, delivery logs, and reminder reports
- `modules/Reports/ReportController.php` - Request handling for report screens
- `modules/Search/SearchController.php` - Request handling for global, quick, advanced, and history search screens
- `modules/Reminders/ReminderController.php` - Request handling for reminder operations, templates, rules, and reports
- `modules/Reports/views/` - View files for register and summary pages
- `modules/Search/views/` - View files for enterprise search and search history
- `modules/Reminders/views/` - View files for reminder management and reminder reports
- `app/Repositories/DocumentRepository.php` - Document metadata and access audit queries
- `app/Services/DocumentAccessService.php` - Ownership, permission, and role-aware download authorization
- `app/Services/SearchService.php` - Search orchestration, role scoping, and audit logging
- `app/Services/ReminderService.php` - Reminder management, options, and report orchestration
- `app/Services/ReminderSchedulerService.php` - Scheduled reminder generation, delivery, and escalation processing

## Storage Rules

- `storage/uploads/clients/` - Client master documents
- `storage/uploads/pso/` - PSO uploads
- `storage/uploads/so/` - SO execution documents and acknowledgements
- `storage/uploads/consultants/` - Consultant deliverables and bills
- `storage/uploads/billing/` - Invoice and receipt related uploads
- `storage/logs/` - Application logs and audit exports
- `storage/cache/` - Runtime cache
- `storage/temp/` - Temporary generated files

## Notes

- Public requests should enter only through `public/index.php`
- Uploaded files stay outside public web access
- Modules remain isolated while using shared services from `app/`
- No business logic should live directly in views
