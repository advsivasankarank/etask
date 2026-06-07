# e-Tasks RC1 Architecture

## Overview

e-Tasks RC1 is a modular PHP 8.x + MySQL application using a lightweight MVC-style structure with shared services and repositories.

## Logical Layers

### 1. Public entry

- `public/index.php`
- receives all web traffic
- boots the application and dispatches the router

### 2. Bootstrap and configuration

- `bootstrap/app.php`
- `config/app.php`
- `config/database.php`
- `config/razorpay.php`

Responsibilities:

- environment loading
- APP_KEY validation
- config registration
- session startup
- middleware aliases
- exception handler registration

### 3. Core runtime

- router
- request/response
- session
- auth
- CSRF
- exception handling
- logger

### 4. Business services

Services orchestrate business rules, including:

- auth
- users
- clients
- portal / PSO
- service orders
- workflow
- billing
- consultant operations
- search
- reminders
- secure document access

### 5. Repository layer

Repositories encapsulate SQL and persistence logic, including:

- user repository
- client repository
- service order repository
- billing repository
- document repository
- search repository
- report repository
- reminder repository

### 6. Module controllers and views

Each active module owns its controller and views under `modules/`.

## Security Model

### Authentication

- session-based
- first-login password change supported
- failed login tracking supported

### Authorization

- permission middleware on routes
- effective permission resolution through role and user permission tables
- centralized actor policy for internal, portal, and consultant access patterns

### Data protection

- Aadhaar encrypted at rest
- private document storage outside public root
- secure document download endpoint
- centralized error handling with logging

## Data Architecture

### Core domains

- users and roles
- clients and contacts
- portal credentials
- PSO
- service orders
- workflow status, history, closures, reminders
- billing entities
- consultant entities
- notifications and reminder logs
- activity audit logs

### Numbering

Immutable numbering sequences are maintained through sequence tables for:

- service orders
- PSO
- invoices
- receipts

## Route Architecture

RC1 route registration lives in:

- `routes/web.php`

Frozen route count:

- `83`

## Permission Architecture

Permission definitions are seeded through migration files and enforced at runtime through:

- `role_permissions`
- `user_permissions`
- permission middleware
- policy-aware auth helpers

Frozen RC1 permission count:

- `52`

## Operational Architecture

### CLI administration

- migration control
- backup creation
- environment validation
- reminder execution
- regression smoke testing

### Storage

- `storage/logs`
- `storage/backups`
- `storage/reports`
- `storage/temp`
- `storage/uploads` for legacy/public-blocked paths
- `PRIVATE_STORAGE_PATH` for secure document storage

## Module Interaction Flow

1. client or staff creates source record
2. service layer validates business rules
3. repository layer writes data
4. activity and audit logs are recorded
5. reports/search/reminders consume the same persisted state

## RC1 Architecture Constraints

- no framework dependency added
- shared-hosting compatibility preserved
- schema, permissions, and routes are frozen for RC1
