# Permission-Based RBAC

Project root: `C:\xampp\htdocs\e-task`

## Tables

- `roles` - existing role catalog used for user assignment
- `permissions` - permission catalog keyed by unique `code`
- `role_permissions` - default grants by role
- `user_permissions` - direct user-level overrides

## Resolution Model

1. Role grants are loaded from `role_permissions`
2. User overrides are loaded from `user_permissions`
3. Direct user grants add permissions
4. Direct user denies remove permissions

## Current Middleware Usage

- Route protection now uses `permission:` middleware entries
- Example:

```php
['auth', 'permission:clients.view']
```

- Multiple permissions in the middleware parameter are treated as OR conditions

## Key Permission Groups

- Dashboard: `dashboard.*`
- Users: `users.manage.portal`, `users.manage.internal`
- Clients: `clients.*`
- Client portal: `portal.*`
- Billing: `billing.*`
- Consultants: `consultants.*`
- Service orders: `service_orders.*`
- Workflow: `workflow.*`
- Reports: `reports.view`, `reports.financial`
- Documents: `documents.download`, `documents.report`

## Migration

Apply the RBAC migration with the standard migration runner:

```powershell
C:\xampp\php\php.exe C:\xampp\htdocs\e-task\database\scripts\migrate.php
```

## Note

Roles are still used for user classification and assignment, but access enforcement is now permission-based.

## Reports Module Grants

- `reports.view`
  Access to:
  - Reports home
  - Client Register
  - Service Order Register
  - GST Summary

- `reports.financial`
  Access to:
  - Invoice Register
  - Receipt Register
  - Outstanding Report
  - Revenue Report

- `documents.download`
  Access to:
  - Secure `/documents/{id}/download` endpoint for authorized internal users
  - Portal clients and consultants use ownership-scoped access checks through the document controller

- `documents.report`
  Access to:
  - Document Access Report under `/reports/document-access`

- `search.view`
  Access to:
  - Enterprise search home under `/search`
  - Global cross-module search results

- `search.quick`
  Access to:
  - Header quick search
  - Quick search page under `/search/quick`

- `search.advanced`
  Access to:
  - Advanced source-based search under `/search/advanced`

- `search.history`
  Access to:
  - Personal search history under `/search/history`

- `search.audit`
  Access to:
  - Cross-user search history and audit visibility under `/search/history`

- `reminders.view`
  Access to:
  - Reminder dashboard
  - Reminder templates and escalation rule listings

- `reminders.create`
  Access to:
  - Create reminder templates
  - Create escalation rules

- `reminders.edit`
  Access to:
  - Edit reminder templates
  - Edit escalation rules

- `reminders.send`
  Access to:
  - Run the reminder scheduler
  - Trigger dashboard and email reminder delivery

- `reminders.report`
  Access to:
  - Reminder Register
  - Pending Reminder Report
  - Reminder Effectiveness Report
  - Escalation Report
