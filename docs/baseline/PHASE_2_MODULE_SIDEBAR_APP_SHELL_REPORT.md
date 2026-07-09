# PHASE 2 — MODULE SIDEBAR & APP SHELL REPORT

**Date:** 2026-07-09  
**Time:** 06:35 AM IST  
**Project:** e-Pani Office Automation & Management Suite  
**Report Version:** 1.0  

---

## 1. Executive Summary

Phase 2 converted the existing flat horizontal navigation into a clean, role-based module sidebar with a common internal app shell. The implementation uses the RBAC foundation from Phase 1 to provide role-based module visibility.

**Key Achievements:**
- Converted horizontal menu to vertical sidebar
- Implemented 10 module groups with sub-menus
- Added role-based visibility for all modules
- Separated client portal navigation from internal navigation
- Added responsive sidebar behavior
- Added active module highlighting
- Added "Planned" badges for future module pages

---

## 2. Files Created

| File | Purpose |
|------|---------|
| `docs/baseline/PHASE_2_MODULE_SIDEBAR_APP_SHELL_REPORT.md` | This report |

---

## 3. Files Modified

| File | Changes |
|------|---------|
| `layouts/main.php` | Complete refactoring to sidebar-based app shell with role-based visibility |

---

## 4. Layout Files Audited

| File | Purpose | Status |
|------|---------|--------|
| `layouts/main.php` | Internal authenticated pages | Refactored to sidebar |
| `layouts/auth.php` | Public/auth pages | Unchanged |
| `modules/Auth/views/landing.php` | Public landing | Unchanged |
| `modules/Auth/views/login.php` | Login page | Unchanged |
| `modules/Auth/views/register-client.php` | Client registration | Unchanged |
| `modules/Auth/views/forgot-password.php` | Password reset request | Unchanged |
| `modules/Auth/views/reset-password.php` | Password reset | Unchanged |
| `modules/Auth/views/change-password.php` | Change password | Unchanged |
| `modules/ClientPortal/views/*` | Client portal views | Use main.php layout with portal navigation |

---

## 5. Sidebar/App Shell Summary

### Internal Sidebar:
- Fixed vertical sidebar (260px width)
- Dark teal gradient background
- Brand: e-Pani logo
- Module groups with icons
- Sub-menu items with permission-based visibility
- "Planned" badges for future module pages
- User info and logout at bottom

### Topbar:
- Sticky topbar with page title
- Mobile menu toggle button
- Search shortcut (Ctrl+K)
- Notifications link
- User profile link

### Content Area:
- Main page content preserved
- Flash messages visible
- Footer preserved

### Responsive Behavior:
- Desktop: sidebar visible
- Tablet/Mobile (< 1024px): sidebar collapsible with hamburger menu
- Overlay when sidebar open on mobile
- Existing page content remains usable

---

## 6. Final Sidebar Modules

### 1. Dashboard
- **Visibility:** All authenticated internal users with dashboard permissions
- **Route:** `/dashboard`
- **Status:** Active

### 2. Client Module
- **Visibility:** users with clients.view, clients.create, clients.edit, or clients.credentials.manage
- **Sub-menus:**
  - Client Register (`/clients`)
  - Add Client (`/clients/create`) — if clients.create permission
- **Status:** Active

### 3. Service Order Module
- **Visibility:** users with service_orders.view, service_orders.create, workflow.advance, or workflow.followup.log
- **Sub-menus:**
  - Service Order Register (`/service-orders`)
  - Create Service Order (`/service-orders/create`) — if service_orders.create permission
  - Reminders (`/reminders`) — if reminders.view or reminders.report permission
- **Status:** Active

### 4. Document Module
- **Visibility:** users with documents.view, documents.upload, documents.download, documents.request, documents.movement.view, or documents.access_log.view
- **Sub-menus:**
  - Document Register — Planned
  - Document Requests — Planned
  - Document Movement — Planned
  - Document Access Log (`/reports/document-access`) — if documents.report or documents.access_log.view permission
- **Status:** Partially Active (Access Log active, others planned)

### 5. DSC Module
- **Visibility:** users with dsc.view, dsc.usage.log, dsc.renewal.view, or dsc.reports.view
- **Sub-menus:**
  - DSC Register — Planned
  - DSC Custody — Planned
  - DSC Movement — Planned
  - DSC Usage Log — Planned
  - DSC Renewal — Planned
- **Status:** Planned (all items disabled)

### 6. Workforce Module
- **Visibility:** users with workforce.view, attendance.view, attendance.report.submit, attendance.report.review, attendance.productivity.view, consultants.view, users.manage.internal, or users.manage.portal
- **Sub-menus:**
  - Staff Monitor (`/attendance`)
  - My Work Day (`/attendance/today`)
  - Daily Work Report (`/attendance/report`)
  - Review Daily Reports (`/attendance/admin`)
  - Productivity Summary (`/attendance/productivity`)
  - Consultants (`/consultants`)
  - User Accounts (`/users`)
  - Roles & Permissions (`/users/rights`)
- **Status:** Active

### 7. Accounts Module
- **Visibility:** users with billing.view, accounts.view, accounts.collections.view, accounts.outstanding.view, accounts.ageing.view, or accounts.unbilled.view
- **Sub-menus:**
  - Billing Dashboard (`/billing`)
  - Invoices (`/reports/invoices`)
  - Receipts (`/reports/receipts`)
  - Outstanding (`/reports/outstanding`)
  - Collections — Planned
  - Collection Ageing — Planned
- **Status:** Partially Active (Billing/Invoices/Receipts/Outstanding active, others planned)

### 8. Reports Module
- **Visibility:** users with reports.view, reports.financial, reminders.report, documents.report, or documents.access_log.view
- **Sub-menus:**
  - Reports Home (`/reports`)
  - Client Reports (`/reports/clients`)
  - Service Order Reports (`/reports/service-orders`)
  - Consultant Reports (`/reports/consultants`)
  - Reminder Reports (`/reminders/register`)
- **Status:** Active

### 9. Settings
- **Visibility:** users with settings.view, users.manage.portal, users.manage.internal, users.manage.rights, settings.company.manage, settings.service_types.manage, settings.workflow.manage, or settings.security.manage
- **Sub-menus:**
  - User Accounts (`/users`)
  - Roles & Permissions (`/users/rights`)
  - Reminder Templates (`/reminders/templates`)
  - Escalation Rules (`/reminders/escalations`)
  - Company Settings — Planned
  - Service Types — Planned
  - Workflow Settings — Planned
  - Security Settings — Planned
- **Status:** Partially Active (Users/Templates/Escalations active, others planned)

### 10. Logout
- **Visibility:** All authenticated users
- **Status:** Active (in sidebar footer)

---

## 7. Role-Based Visibility Verification

| Role | Dashboard | Clients | Service Orders | Documents | DSC | Workforce | Accounts | Reports | Settings |
|------|-----------|---------|----------------|-----------|-----|-----------|----------|---------|----------|
| SUPER_ADMIN | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| ADMIN | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| CRM | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | Limited | ✓ | Limited |
| ASSISTANT_CRM | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | Limited | ✓ | Limited |
| BACKEND_STAFF | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ | Limited | ✓ | Limited |
| DEO | ✓ | ✓ | ✓ | ✓ | Limited | ✓ | Limited | ✓ | Limited |
| ACCOUNTS | ✓ | ✓ | ✓ | ✓ | Limited | ✓ | ✓ | ✓ | Limited |
| CONSULTANT | ✓ | Limited | ✓ | ✓ | Limited | ✓ | Limited | ✓ | Limited |
| CLIENT | Portal only | Portal only | Portal only | Portal only | Portal only | Portal only | Portal only | Portal only | Portal only |

### CLIENT Portal Separation:
- **CLIENT internal sidebar visibility:** BLOCKED — CLIENT users see only client portal navigation
- **Client portal menu:**
  - My Account (`/client-portal/account`)
  - My Services (`/client-portal/pso`)
  - My Documents (`/client-portal/documents`)
  - Support (`/client-portal/support`)
  - Logout
- **Routes verified:** All client portal routes use portal.self_access permission

---

## 8. Planned/Disabled Items

| Module | Planned Items | Reason |
|--------|---------------|--------|
| Document Module | Document Register, Document Requests, Document Movement | Routes not yet created |
| DSC Module | DSC Register, DSC Custody, DSC Movement, DSC Usage Log, DSC Renewal | Module not yet built |
| Settings | Company Settings, Service Types, Workflow Settings, Security Settings | Settings UI not yet built |
| Accounts | Collections, Collection Ageing | Accounts enhancement not yet built |

---

## 9. Route Link Verification

### Existing Linked Routes (No 404 Risk):
| Menu Item | Route | Status |
|-----------|-------|--------|
| Dashboard | /dashboard | ✓ Active |
| Client Register | /clients | ✓ Active |
| Add Client | /clients/create | ✓ Active |
| Service Order Register | /service-orders | ✓ Active |
| Create Service Order | /service-orders/create | ✓ Active |
| Reminders | /reminders | ✓ Active |
| Document Access Log | /reports/document-access | ✓ Active |
| Staff Monitor | /attendance | ✓ Active |
| My Work Day | /attendance/today | ✓ Active |
| Daily Work Report | /attendance/report | ✓ Active |
| Review Daily Reports | /attendance/admin | ✓ Active |
| Productivity Summary | /attendance/productivity | ✓ Active |
| Consultants | /consultants | ✓ Active |
| User Accounts | /users | ✓ Active |
| Roles & Permissions | /users/rights | ✓ Active |
| Billing Dashboard | /billing | ✓ Active |
| Invoices | /reports/invoices | ✓ Active |
| Receipts | /reports/receipts | ✓ Active |
| Outstanding | /reports/outstanding | ✓ Active |
| Reports Home | /reports | ✓ Active |
| Client Reports | /reports/clients | ✓ Active |
| Service Order Reports | /reports/service-orders | ✓ Active |
| Consultant Reports | /reports/consultants | ✓ Active |
| Reminder Reports | /reminders/register | ✓ Active |
| Reminder Templates | /reminders/templates | ✓ Active |
| Escalation Rules | /reminders/escalations | ✓ Active |

### Disabled/Planned Routes (No 404 Risk):
| Menu Item | Status | Reason |
|-----------|--------|--------|
| Document Register | Disabled | Planned |
| Document Requests | Disabled | Planned |
| Document Movement | Disabled | Planned |
| DSC Register | Disabled | Planned |
| DSC Custody | Disabled | Planned |
| DSC Movement | Disabled | Planned |
| DSC Usage Log | Disabled | Planned |
| DSC Renewal | Disabled | Planned |
| Collections | Disabled | Planned |
| Collection Ageing | Disabled | Planned |
| Company Settings | Disabled | Planned |
| Service Types | Disabled | Planned |
| Workflow Settings | Disabled | Planned |
| Security Settings | Disabled | Planned |

**404 Risk:** NONE — All links are either active routes or disabled/planned items

---

## 10. Testing Performed

### PHP Syntax:
- ✅ `php -l layouts/main.php` — No syntax errors detected

### Login Flow:
- ✅ Internal login redirects to /dashboard
- ✅ Client login redirects to /client-portal/account
- ✅ Change password flow unchanged

### Dashboard:
- ✅ Sidebar visible for internal users
- ✅ Topbar with page title visible
- ✅ Content area preserved

### Client Portal:
- ✅ CLIENT users see portal sidebar (not internal sidebar)
- ✅ Portal navigation has My Account, My Services, My Documents, Support
- ✅ All portal routes use portal.self_access permission

### Permission/Menu Checks:
- ✅ CLIENT internal sidebar blocked/hidden
- ✅ SUPER_ADMIN full sidebar visible
- ✅ Non-admin restricted menus hidden
- ✅ "Planned" items disabled and visually distinct

---

## 11. Known Risks / Pending Items

| Risk | Severity | Mitigation |
|------|----------|------------|
| APP_KEY contains placeholder | CRITICAL | Must rotate before production deployment |
| DB_PASSWORD is empty | CRITICAL | Must set strong password before production |
| APP_ENV is local | HIGH | Must change to production before deployment |
| APP_URL is localhost | HIGH | Must update to production domain |
| Razorpay keys not configured | MEDIUM | Must configure for billing module |
| DSC Module not built yet | LOW | Permissions seeded, sidebar shows "Planned" |
| Document Movement Module not built | LOW | Permissions seeded, sidebar shows "Planned" |
| Settings Module UI not built | LOW | Permissions seeded, sidebar shows "Planned" |

---

## 12. Whether It Is Safe To Proceed To Phase 3 — Client Module Refinement / Dashboard Finalisation

**YES** — It is safe to proceed to Phase 3 — Client Module Refinement / Dashboard Finalisation.

Phase 2 has successfully:
- ✅ Created role-based module sidebar
- ✅ Separated client portal navigation
- ✅ Added responsive sidebar behavior
- ✅ Added active module highlighting
- ✅ Added "Planned" badges for future modules
- ✅ Verified all routes are valid (no 404 risk)
- ✅ Verified CLIENT users see only portal navigation
- ✅ Verified SUPER_ADMIN sees full sidebar
- ✅ No application logic was modified
- ✅ No database schema was modified

---

**Report Prepared By:** OpenCode AI Assistant  
**Report Date:** 2026-07-09 06:35 AM IST  
**Phase Status:** COMPLETE  
**Next Phase:** PHASE 3 — Client Module Refinement / Dashboard Finalisation
