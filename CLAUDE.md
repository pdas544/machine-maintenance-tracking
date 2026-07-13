# Machine Maintenance Tracking System — Codebase Guidelines

## Stack
- **Laravel 13** / PHP 8.3+ / SQLite
- **Bootstrap 5** (CDN) + Bootstrap Icons — no Tailwind/Alpine used in practice
- **Sanctum** for API auth
- Blade templates, no SPA

## Entity Hierarchy
```
Segment → LinesOrGroup → Machine → Ticket
```
- **Segment**: `parts` / `assembly` — mechanics assigned via `segment_mechanics` pivot
- **LinesOrGroup**: production line or group (e.g. `F1`, `Line 1`)
- **Machine**: belongs to a line, has `machine_code` (unique)
- **Ticket**: tracks issue from `pending` → `in_progress` → `completed` / `unfixable_escalated`

## Roles (role_id)
| id | Role               |
|----|--------------------|
| 1  | operator           |
| 2  | line_leader        |
| 3  | mechanic           |
| 4  | floor_incharge     |
| 5  | maintenance_head   |
| 6  | maintenance_manager|

## Conventions

### Models
- Use PHP 8 attributes: `#[Fillable]`, `#[Hidden]` (not `$fillable`/`$hidden` arrays)
- Name relationships explicitly with foreign keys: `belongsTo(User::class, 'raised_by')`
- Eager-load relationships when querying for views

### Controllers
- Single controller pattern — `TicketController` handles all ticket operations
- Validate inline with `$request->validate()`, not Form Requests
- Always return `redirect()->route('dashboard')->with('success', ...)`
- `Auth::user()` and `Auth::id()` for current user

### Routes
- **Web** (`routes/web.php`): Blade-form-based flows (POST/PATCH form submits)
- **API** (`routes/api.php`): Sanctum-protected JSON endpoints
- Middleware: `auth` for web, `auth:sanctum` for API
- Route model binding: `Route::patch('/tickets/{ticket}/status', ...)`

### Views
- `layouts.app` — Bootstrap-based layout (CDN, dark navbar, sticky footer)
- Dashboard per role: `dashboards/operator`, `dashboards/mechanic`, fallback `dashboard`
- Bootstrap modals for forms (raise ticket, update status)
- Print view: `print-job-card.blade.php` — auto-prints on load, table layout
- Session flashes: `@if(session('success'))` alert banner

### Migrations
- `foreignId(...)->constrained()` for FK, `->onDelete('cascade')` where appropriate
- `enum()` for status fields with explicit allowed values

### DB Seeding
- `DatabaseSeeder` creates roles, users, segments, mechanic mappings, lines, machines
- Test users all use password `password`

### Mechanic Assignment
- Ticket auto-assigns the first mechanic from the machine's segment via `segment_mechanics` pivot
- `Machine → linesOrGroup → segment → mechanics` (Many-to-Many via `segment_mechanics`)

### Ticket Status Transitions
| From | To |
|------|----|
| pending | in_progress (sets `acknowledged_at`) |
| in_progress | completed (sets `resolved_at`) |
| in_progress | unfixable_escalated |

## Testing
- PHPUnit via `composer test`
- SQLite in-memory for tests

## Commands
- `composer dev` — serves app + queue + logs + Vite
- `composer test` — run tests
- `php artisan migrate:fresh --seed` — reset DB with seed data