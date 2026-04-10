# Tomato Sorting API (Laravel)

Backend API for the AIgriculture tomato sorting system.

This service provides:
- Authentication (Sanctum token auth)
- Role-based authorization (`admin`, `farmer`, `sorter`)
- Appointments and sorting sessions/logs
- Notifications
- Activity logs
- Dashboard metrics

## Tech Stack

- Laravel 12
- PHP 8.2+
- PostgreSQL
- Laravel Sanctum (token auth)

## Local Setup

1. Install dependencies:
```bash
composer install
```

2. Configure environment:
```bash
cp .env.example .env
```
Update DB and app values in `.env`.

3. Generate key and run migrations:
```bash
php artisan key:generate
php artisan migrate
```

4. Start server:
```bash
php artisan serve
```

Base URL (local default): `http://127.0.0.1:8000/api`

## Authentication

This API uses Bearer tokens from Sanctum.

1. Login or register to get a token.
2. Send it in requests:
```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

## Roles and Access

- `admin`: user management + system-wide access
- `farmer`: can create/view own appointments
- `sorter`: can process sessions/logs on assigned work

Role checks are enforced by middleware and controller guards.

## API Endpoints

### Auth

- `POST /auth/register` - Register farmer/sorter account
- `POST /auth/login` - Login and return token
- `POST /auth/logout` - Revoke current token (auth required)
- `GET /auth/me` - Current user profile (auth required)

### Dashboard

- `GET /dashboard` - Role-aware summary metrics

### Users (Admin)

- `GET /users` - List users (`?role=admin|farmer|sorter`)
- `POST /users` - Create user
- `GET /users/{id}` - Get user
- `PUT/PATCH /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user
- `GET /sorters` - List sorters (used by appointment booking)

### Appointments

- `GET /appointments` - List appointments (paginated)
  - Query params:
    - `per_page` (default `15`, max `200`)
    - `status` (`pending|confirmed|cancelled|completed`)
    - `has_session=1` (only appointments with sorting session)
- `POST /appointments` - Create appointment (farmer only)
- `GET /appointments/{id}` - Appointment detail
- `PUT/PATCH /appointments/{id}` - Update appointment (pending only)
- `DELETE /appointments/{id}` - Delete appointment (not completed)
- `PATCH /appointments/{id}/status` - Update status (`confirmed|cancelled|completed`)

### Sorting Sessions (Sorter/Admin)

- `GET /appointments/{appointmentId}/sessions` - List appointment sessions
- `POST /appointments/{appointmentId}/sessions` - Start session
- `GET /sessions/{id}` - Session detail
- `PUT/PATCH /sessions/{id}` - Declared route (currently not implemented)
- `DELETE /sessions/{id}` - Declared route (currently not implemented)
- `POST /sessions/{id}/complete` - Complete session and finalize counts

### Sorting Logs (Sorter/Admin)

- `GET /sessions/{sessionId}/logs` - List logs for session
- `POST /sessions/{sessionId}/logs` - Create log entry
- `GET /logs/{id}` - Get single log

### Notifications

- `GET /notifications` - List notifications
- `PATCH /notifications/{id}/read` - Mark single notification as read
- `PATCH /notifications/read-all` - Mark all as read

### Activity Logs

- `GET /activity-logs` - List activity logs
  - Supports filters like `action`, `search`; admins have broader filters
- `GET /activity-logs/{id}` - Get activity log detail

## How To Call Endpoints

Use `curl`, Postman, Insomnia, or frontend HTTP client.

### 1) Register

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/register" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"full_name\":\"Farmer One\",\"email\":\"farmer1@example.com\",\"password\":\"password123\",\"password_confirmation\":\"password123\",\"role\":\"farmer\",\"farm_name\":\"Green Farm\"}"
```

### 2) Login (get token)

```bash
curl -X POST "http://127.0.0.1:8000/api/auth/login" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"email\":\"farmer1@example.com\",\"password\":\"password123\"}"
```

Save returned `token` as `<TOKEN>`.

### 3) Authenticated request example

```bash
curl -X GET "http://127.0.0.1:8000/api/auth/me" ^
  -H "Authorization: Bearer <TOKEN>" ^
  -H "Accept: application/json"
```

## CRUD Example: Appointments

### Create
```bash
curl -X POST "http://127.0.0.1:8000/api/appointments" ^
  -H "Authorization: Bearer <TOKEN>" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"sorter_id\":1,\"scheduled_date\":\"2026-04-20\",\"scheduled_time\":\"10:30\",\"notes\":\"Morning harvest\"}"
```

### Read (list)
```bash
curl -X GET "http://127.0.0.1:8000/api/appointments?per_page=15&status=pending" ^
  -H "Authorization: Bearer <TOKEN>" ^
  -H "Accept: application/json"
```

### Read (single)
```bash
curl -X GET "http://127.0.0.1:8000/api/appointments/1" ^
  -H "Authorization: Bearer <TOKEN>" ^
  -H "Accept: application/json"
```

### Update
```bash
curl -X PATCH "http://127.0.0.1:8000/api/appointments/1" ^
  -H "Authorization: Bearer <TOKEN>" ^
  -H "Content-Type: application/json" ^
  -H "Accept: application/json" ^
  -d "{\"notes\":\"Updated note\"}"
```

### Delete
```bash
curl -X DELETE "http://127.0.0.1:8000/api/appointments/1" ^
  -H "Authorization: Bearer <TOKEN>" ^
  -H "Accept: application/json"
```

## Common Response Patterns

- Success:
  - JSON object (resource or message)
  - Paginated lists include `data`, pagination metadata, and links
- Validation errors:
  - `422` with `message` and `errors` object
- Forbidden:
  - `403` when role is insufficient
- Unauthorized:
  - `401` if missing/invalid token

## Core Domain Model

- `User` has one `Farmer` or one `Sorter`
- `Appointment` belongs to `Farmer` and `Sorter`
- `Appointment` has one `SortingSession`
- `SortingSession` has many `SortingLog`
- `Notification` belongs to `User` and optional `Appointment`
- `ActivityLog` tracks user actions

## Frontend Integration

The frontend app (separate repo: `tomato-frontend`) uses:
- `VITE_API_URL=http://127.0.0.1:8000/api`
- `Authorization: Bearer <token>`
- endpoints documented above through `src/lib/api.ts`
