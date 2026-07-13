# e-Pani Screen, Workflow, and UI Audit

**Audit date:** 12 July 2026
**Environment:** Live production (`https://etask.etaxadv.com`)
**Role used:** `SUPER_ADMIN`
**Coverage:** 46 primary navigation screens, 46 secondary/form/report screens, and 5 available client/user detail screens. Desktop and 390px mobile rendering were checked. Forms were inspected read-only and were not submitted.

## Executive Summary

The application has a coherent visual system and broad operational coverage. Most registers, forms, dashboards, and empty states render correctly on desktop. The strongest areas are client onboarding, service-order creation, document/DSC operations, reminders, accounts, settings, and record detail pages.

The application is not ready for unrestricted production use yet because several high-value navigation paths return a server error, and mobile layouts have horizontal overflow on many primary workflows. The reporting failure is especially significant because the Reports dashboard and nine core register reports are unavailable.

### Overall assessment

- **Desktop UI:** Good visual consistency; generally usable.
- **Workflow completeness:** Broad, but critical reporting/search/admin paths are broken.
- **Mobile UI:** Navigation exists, but many screens overflow horizontally or clip content/actions.
- **Accessibility/semantics:** Most module pages do not expose their visible page title as an `h1`.
- **Production readiness:** Blocked pending server-error remediation and responsive fixes.

## P0 — Production Blockers

### 1. Reports dashboard and nine core reports return HTTP-style 500 screens

Broken screens:

- `/reports`
- `/reports/clients`
- `/reports/service-orders`
- `/reports/pso`
- `/reports/invoices`
- `/reports/receipts`
- `/reports/outstanding`
- `/reports/gst-summary`
- `/reports/revenue`
- `/reports/consultants`

Working report screens:

- Operational, workforce, attendance, documents, DSC, accounts, audit, and document-access reports.

**Impact:** Management cannot access core client, work, billing, collection, GST, revenue, or consultant reporting from the main reporting workflow.

**Recommendation:** Treat the common dependency used by the broken report methods as the first diagnostic target. Add an automated smoke test for every GET report route before deployment.

### 2. Global search is entirely unavailable

Broken screens:

- `/search`
- `/search/quick`
- `/search/advanced`
- `/search/history`

The global header advertises `Search… Ctrl+K` on every internal screen, so this failure is highly visible and affects all modules.

**Recommendation:** Fix or temporarily hide the global search affordance until all four routes pass. Add graceful empty-result behavior rather than allowing repository/schema exceptions to become a generic 500 page.

### 3. Attendance administration screens fail

Broken screens:

- `/attendance/admin` — Review Daily Reports
- `/attendance/productivity` — Productivity Summary

Working attendance screens:

- Staff Monitor
- My Work Day
- Start Work
- Daily Work Report

**Impact:** Staff can record activity, but managers cannot review reports or use productivity oversight—the workflow stops at submission.

### 4. Roles & Permissions fails

Broken screen:

- `/users/rights`

User listing, creation, and profile screens work, but rights administration does not. User profile pages prominently link to “Manage Rights,” leading administrators into a 500 error.

**Impact:** Access governance cannot be maintained safely through the application.

## P1 — Mobile and Responsive Issues

### 1. Horizontal page overflow is widespread

At a 390px mobile viewport, 34 tested screens produced document-level horizontal overflow. The clearest examples are:

- Dashboard
- Client Register
- Add Client
- Create Service Order
- Workforce Dashboard
- Accounts Dashboard
- User Accounts
- Consultant Assignments
- Service Types, Milestones, Reminder Templates
- Reminder registers/reports
- Operational, workforce, attendance, and audit reports
- Document/DSC creation forms

Observed effects:

- A persistent horizontal scrollbar appears at the bottom.
- Dashboard hero content and the second primary action are clipped off-screen.
- Client table columns and row actions extend beyond the viewport.
- Long forms appear visually correct in their first column but still force horizontal scrolling.
- Wide settings/report tables are not contained inside a dedicated scroll region.

**Recommendation:**

1. Ensure the app shell uses `min-width: 0` on content grid/flex children.
2. Replace fixed/minimum content widths with responsive constraints.
3. Put wide tables inside an explicit `.table-scroll` container, not on the document itself.
4. Convert register tables to stacked mobile cards where actions are important.
5. Stack hero/header action buttons at small breakpoints and allow headings/subtitles to wrap.
6. Add automated checks asserting `scrollWidth <= clientWidth` at 390px for every main route.

### 2. Mobile register actions are not reliably visible

On Client Register, the identity columns are visible but the View/Edit/Credentials actions are off-screen. This makes the record appear readable but not operable without discovering horizontal scrolling.

**Recommendation:** Use a mobile record card with a fixed action row, or pin the actions column and provide a visible horizontal-scroll hint.

### 3. Header text competes for space

On small screens, long page titles and “System Super Admin” wrap aggressively. This contributes to cramped headers and reduces room for notification/navigation controls.

**Recommendation:** Show a shorter role/user label in the header, move full identity to the profile menu, and truncate unusually long page titles only after preserving their accessible name.

## P1 — Workflow and Information Architecture

### 1. Duplicate financial and consultant workspaces

The application exposes both:

- Accounts routes (`/accounts/...`) and Billing (`/billing`)
- Workforce consultant routes (`/workforce/...`) and Consultants (`/consultants`)

The distinction is not obvious to an administrator. Similar labels and overlapping responsibilities increase training cost and may lead to records being entered in the wrong workflow.

**Recommendation:** Define one primary workspace per business process. If both views are required, rename them by purpose—for example, “Accounts Control” versus “Service Order Billing,” and “Consultant Master” versus “Consultant Delivery Workspace.”

### 2. Reports navigation has no graceful degradation

The Reports dashboard itself fails, even though seven report destinations still work. This removes discoverability for the working reports.

**Recommendation:** Keep the report index lightweight and independent. Render each report card with availability/error status so one failed repository call cannot take down the entire report catalogue.

### 3. Empty-state language is inconsistent

The application alternates among “No results,” “No data,” “No movements,” “All clear,” “All caught up,” and uppercase category labels. Some empty states include a creation action; others do not.

**Recommendation:** Standardize empty states with three parts: what is empty, why it may be empty, and the next permitted action. Keep success-style empty states (“All caught up”) only for genuinely positive conditions.

### 4. Settings contains read-only-looking pages mixed with editable pages

Service Types, Milestones, Reminder Templates, Role Defaults, Document Categories, and DSC Categories often look like configuration masters but mostly present reference tables without an obvious edit affordance.

**Recommendation:** Label read-only configuration explicitly as “Reference,” or add clear create/edit controls based on permission. Avoid implying manageability when a page is informational only.

## P2 — Visual, Content, and Accessibility Refinements

### 1. Missing semantic page headings

Most module pages visually show a page title but do not expose it as an `h1`. Dashboard and attendance pages do, while many client, service-order, document, DSC, accounts, settings, and user screens do not.

**Recommendation:** Use exactly one `h1` per screen for the primary page title. Keep section titles at `h2`/`h3` levels in logical order.

### 2. Dashboard density is too high for an empty/new installation

The desktop dashboard is polished, but it displays many zero-value KPI cards and an empty 14-day chart. On a new tenant this creates more scanning effort than insight.

**Recommendation:** Collapse zero-value secondary KPIs into one “No exceptions” summary, emphasize onboarding actions, and reveal detailed exception cards when data exists.

### 3. Naming and capitalization need normalization

Examples include:

- “Everification” instead of “e-Verification”
- Status values shown as raw constants such as `IN_PROGRESS`, `FOLLOWED_UP`, and `CLIENT_CLARIFICATION_PENDING`
- Mixed use of “Register,” “Dashboard,” “Module,” and “Workspace” for similar views

**Recommendation:** Add a central presentation-label map for enum/status values and adopt a naming guide for navigation and page titles.

### 4. Currency format is inconsistent

Some screens display `INR 0`, others `INR 0.00`, while the landing page uses `₹`.

**Recommendation:** Standardize on one locale-aware money formatter, preferably `₹0.00` or `INR 0.00` depending on accounting requirements.

### 5. Generic 500 page needs a support reference

The error page says the incident was logged but gives the user no incident ID, timestamp, retry action, or support path.

**Recommendation:** Include a safe reference ID, timestamp, “Try again,” “Return to previous page,” and support guidance. Never expose stack traces in production.

### 6. Change-password messaging should be context-aware

Direct navigation to `/change-password` uses forced-security-update language even after the user has already changed the initial password.

**Recommendation:** Use “Change Password” for voluntary access and reserve “Secure Your Account” / forced-change language for `must_change_password = 1`.

## Screen Areas That Rendered Correctly

The following workflows rendered without server errors in the audited state:

- Dashboard and quick-access workspace
- Client register, create, detail, edit, and portal-credential screens
- Service-order register and creation screen
- Document register, requests, movement, access log, and creation forms
- DSC register, movement, usage, renewals, reports, and creation forms
- Reminder dashboard, templates, escalation rules, and reminder reports
- Staff self-service attendance screens
- Workforce dashboard and consultant operational screens
- Accounts dashboard, registers, ageing, follow-ups, payables, unbilled work, and accounts reports
- Operational/workforce/attendance/document/DSC/accounts/audit reports
- Settings dashboard and all tested settings/reference screens
- User register, create, and detail screens

## Coverage Limitations

- No forms were submitted; this audit did not create, update, archive, approve, pay, upload, or delete production records.
- Service-order detail, billing detail, consultant detail, and other record-specific downstream screens could not be exercised because the relevant production registers were empty.
- Client portal screens require a separate portal-role session and should receive a dedicated role-based audit.
- Visual checks used the current production dataset, which is sparse after the recent cleanup. Populated-table, pagination, long-text, file-upload, and validation-error states still need dedicated test fixtures.

## Recommended Delivery Sequence

1. Fix all 500 routes and add GET smoke tests for every authenticated route.
2. Repair mobile document-level overflow and table/action usability.
3. Complete end-to-end workflow tests with seeded non-sensitive fixture data.
4. Audit the client portal under a portal user.
5. Normalize headings, enum labels, empty states, currency, and naming.
6. Perform accessibility testing: keyboard navigation, focus visibility, labels, heading structure, contrast, and screen-reader announcements.
