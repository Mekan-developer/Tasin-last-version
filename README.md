# Tasin Mobile Services — Admin Panel & Storefront

A product catalog platform with an administrative dashboard and public storefront. Administrators manage products, categories, variants, pricing, and inventory. Customers browse, search, and view product details via the public storefront interface. Built as an Inertia.js monolith — no separate REST API, fully integrated backend and frontend.

---

## System Components

| Component | Technology |
|---|---|
| Admin Panel | Laravel 11 + Inertia.js v2 + Vue 3 |
| Public Storefront | Laravel 11 + Inertia.js v2 + Vue 3 |
| Backend API | Laravel 11 (no Sanctum — same-origin requests) |
| Real-time Updates | Laravel Reverb (WebSockets) |
| Database | MySQL 8 |
| Frontend Framework | Vue 3 (Composition API) |
| State Management | Pinia |
| Styling | Tailwind CSS v3 |

---

## Tech Stack

| Layer | Package / Version |
|---|---|
| PHP | 8.3 |
| Framework | Laravel 11 |
| Frontend Bridge | Inertia.js v2 (`inertiajs/inertia-laravel`) |
| Frontend Framework | Vue 3 (Composition API, `<script setup>`) |
| State Management | Pinia |
| Styling | Tailwind CSS v3 |
| Routing | Ziggy v2 (named routes) |
| Real-time | Laravel Reverb (WebSockets) |
| Testing | PHPUnit v10 |
| Code Style | Laravel Pint v1 |
| Build Tool | Vite 6 |

---

## Architecture & Patterns

This project enforces strict layered architecture. Every developer must follow these patterns without exception.

```
HTTP Request
    └── Controller (thin — HTTP only)
            └── Form Request (validation)
            └── Service / Action (business logic)
                    └── Repository (all DB queries)
                    └── Job (background tasks)
            └── Resource (response formatting)
```

| Pattern | Rule |
|---|---|
| **Thin Controllers** | Only handle HTTP: receive request, call service, return response |
| **Repository Pattern** | ALL database queries live in Repositories — never in Controllers or Services |
| **Services** | Complex multi-step business logic |
| **Actions** | Single-purpose operations (e.g. `AssignMasterToOrderAction`) |
| **Form Requests** | All validation — never `$request->validate()` in controllers |
| **API Resources** | All responses — never return raw models or arrays |
| **Jobs** | All background/async processing (image uploads, notifications) |
| **Observers** | All model event handling |
| **Enums** | All statuses and fixed values — never raw strings |

### Hard Rules

```
❌ NEVER  $request->all()              ✅ USE  $request->validated()
❌ NEVER  Model::find($id)             ✅ USE  Model::findOrFail($id)
❌ NEVER  return true/false            ✅ THROW exceptions from Services
❌ NEVER  raw status strings           ✅ USE  Enums
❌ NEVER  session()->flash() directly  ✅ USE  WithNotification trait
❌ NEVER  hardcode UI text             ✅ USE  translation helpers t() / __()
```

---

## Project Structure

```
app/
├── Actions/                    # Single-purpose operations
├── Console/Commands/           # Artisan commands (auto-registered in L11)
├── Enums/                      # Status enums and fixed value sets
├── Events/                     # Application events
├── Http/
│   ├── Controllers/            # Web controllers (thin, Inertia)
│   │   └── Api/V1/             # API controllers (separate from web)
│   ├── Middleware/             # HTTP middleware
│   ├── Requests/               # Form Request validation classes
│   ├── Resources/              # Eloquent API Resources
│   └── Traits/
│       └── WithNotification.php
├── Jobs/                       # Queued jobs (image processing, etc.)
├── Models/                     # Eloquent models
├── Observers/                  # Model event observers
├── Repositories/               # All database query logic
└── Services/                   # Business logic services

resources/
└── js/
    ├── Components/             # Reusable Vue components (Admin + Shop)
    │   ├── Admin/              # Admin-specific UI components
    │   ├── Shop/               # Storefront UI components
    │   └── PasswordInput.vue   # Password field with visibility toggle
    ├── Composables/            # Reusable Vue composables
    ├── Layouts/
    │   ├── AdminLayout.vue     # Admin panel layout (sidebar + topbar)
    │   └── ShopLayout.vue      # Public storefront layout
    ├── Pages/                  # Inertia page components
    │   ├── Auth/               # Login, Register
    │   ├── Admin/              # Admin pages (products, categories, etc.)
    │   ├── Shop/               # Storefront pages (product listing, detail, etc.)
    │   └── Dashboard.vue       # Admin dashboard
    ├── stores/                 # Pinia stores (global state)
    │   ├── useThemeStore.js    # Dark/light mode persistence
    │   └── useNotificationStore.js # Toast notifications
    ├── app.js                  # Inertia bootstrap
    └── css/                    # Tailwind CSS + custom styles

lang/
└── ru/                        # Russian translations (server-side)
    ├── auth.php                # Authentication messages
    ├── notifications.php       # Flash notification messages
    ├── validation.php          # Form validation messages
    └── ...

routes/
├── web.php                     # Admin panel routes
├── auth.php                    # Authentication routes
├── shop.php                    # Storefront (public) routes
└── console.php                 # Artisan commands

public/
└── sounds/
    └── new-order.mp3           # Admin panel alert sound

tests/
├── Feature/                    # Feature tests (primary)
└── Unit/                       # Unit tests (isolated logic only)
```

---

## Local Development Setup

### Requirements

- PHP 8.3
- Composer
- Node.js 20+
- MySQL 8

### Steps

```bash
# 1. Clone the repository
git clone <repo-url>
cd project

# 2. Install dependencies
composer install
npm install

# 3. Environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
# Set DB_CONNECTION=mysql, DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 5. Run migrations and seeders
php artisan migrate --seed

# 6. Link storage
php artisan storage:link

# 7. Start development servers
composer run dev        # runs Vite + php artisan serve together
# OR separately:
npm run dev
php artisan serve

# 8. Queue worker (required for image uploads and jobs)
php artisan queue:work

# 9. WebSocket server (required for realtime order alerts)
php artisan reverb:start
```

---

## Environment Variables

```dotenv
# ── Application ──────────────────────────────────────────────────────────────
APP_NAME=TasinMobile         # Application name (shown in browser title)
APP_ENV=local                # local | staging | production
APP_KEY=                     # Run: php artisan key:generate
APP_DEBUG=true               # Set false in production
APP_URL=http://localhost:8000 # Full public URL (used in emails, links)
APP_TIMEZONE=Asia/Ashgabat   # Server timezone

# ── Localization ──────────────────────────────────────────────────────────────
APP_LOCALE=ru                # Application language (Russian only)
APP_FALLBACK_LOCALE=ru       # Fallback locale

# ── Database (MySQL) ──────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tasin_mobile
DB_USERNAME=root
DB_PASSWORD=

# ── Queue ─────────────────────────────────────────────────────────────────────
QUEUE_CONNECTION=database    # database | redis (redis recommended for production)

# ── WebSocket — Laravel Reverb ────────────────────────────────────────────────
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=               # Generated by: php artisan reverb:install
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http           # https in production

# Injected into Vite for the frontend Echo client
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"

# ── Storage ───────────────────────────────────────────────────────────────────
FILESYSTEM_DISK=local        # local | s3

# ── Session & Cache ───────────────────────────────────────────────────────────
SESSION_DRIVER=database
SESSION_LIFETIME=120         # Minutes before session expires
CACHE_STORE=database         # database | redis
```

---

## Frontend Standards

All frontend code lives in `resources/js/`. Every component uses `<script setup>` — no Options API, no class components.

### Pinia Stores

| Store | File | Purpose |
|---|---|---|
| Theme | `useThemeStore.js` | Dark/light mode toggle. Persists to `localStorage`. Applies `dark` class to `<html>`. |
| Notifications | `useNotificationStore.js` | Toast queue. Methods: `success()`, `error()`, `warning()`, `info()`. Auto-dismiss after 5s. |

### Component Template

```vue
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
  title: String,
})
</script>

<template>
    <AdminLayout :title="title">
        <!-- Single root element inside the layout slot -->
        <div class="rounded-xl bg-white p-6 shadow-sm dark:bg-slate-800">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                {{ title }}
            </h2>
        </div>
    </AdminLayout>
</template>
```

- **Tailwind only** — no `<style>` blocks except scoped transition animations
- **Dark mode** — every visible element must have `dark:` variants
- **Text directly in code** — no i18n wrapper; all UI text in Russian directly in components
- **`@` alias** resolves to `resources/js/`

---

## Localization

The project is **Russian-only** — no multi-language support. All server-side translation keys are in `lang/ru/`.

### Server-side Translations

```php
// lang/ru/notifications.php
return [
    'created' => ':resource успешно создан',
    'updated' => ':resource успешно обновлён',
    'deleted' => ':resource успешно удалён',
];
```

Use via helper: `__('notifications.created', ['resource' => 'Товар'])` or in Form Requests:

```php
// app/Http/Requests/StoreProductRequest.php
public function messages(): array
{
    return [
        'name.required' => 'Введите название товара.',
        'price.required' => 'Укажите цену.',
    ];
}
```

### Frontend Text

All UI text is written **directly in Vue components** — no translation wrapper. Example:

```vue
<template>
    <button @click="save" class="px-4 py-2 bg-blue-600 text-white rounded">
        Сохранить
    </button>
    <p class="text-gray-600">Товар успешно добавлен!</p>
</template>
```
---

## Adding a New Feature

Follow this order every time — no skipping steps.

```
Step 1 — Database
    php artisan make:migration create_xxx_table
    php artisan make:model Xxx -f              # -f creates factory

Step 2 — Repository
    Create app/Repositories/XxxRepository.php

Step 3 — Business Logic
    Create app/Services/XxxService.php

Step 4 — Controller
    php artisan make:controller XxxController  # thin — HTTP only

Step 5 — Validation & Form Request
    php artisan make:request StoreXxxRequest   # messages() and attributes() in Russian

Step 6 — Vue Component
    Create resources/js/Pages/Xxx/Index.vue
    (dark mode, uses appropriate layout: AdminLayout or ShopLayout)

Step 7 — Tests
    php artisan make:test --phpunit XxxTest
    Cover: happy path + validation failure + edge cases

Step 8 — Commit & Push
    git add .
    vendor/bin/pint --dirty
    git commit -m "feat: add xxx management"
    git push
```

---

## Architecture Overview

This project is an **Inertia monolith** — no separate REST API for the web frontend. The same Laravel backend serves:

- **Admin Panel** — staff/admin management interface (`routes/web.php`)
- **Public Storefront** — product catalog for customers (`routes/shop.php`)

Both are rendered server-side as Vue SPA pages via Inertia.js.

```
HTTP Request (from browser)
    └── Controller (thin — HTTP only)
            └── Form Request (validation in Russian)
            └── Service (business logic)
                    └── Repository (all DB queries)
            └── Inertia::render() (response as Vue page component)
```

**Key principle**: All data logic (search, filtering, sorting, pagination) lives on the backend. Frontend only formats requests and renders responses.

---

## Code Quality

### PHP — Laravel Pint

Run this before every commit:

```bash
vendor/bin/pint --dirty    # Fix only changed files
vendor/bin/pint            # Fix entire codebase
```

### Static Analysis — PHPStan / Larastan (level 6)

```bash
vendor/bin/phpstan analyse
```

### Tests — PHPUnit

```bash
php artisan test --compact                                     # All tests
php artisan test --compact tests/Feature/ProductTest.php      # Single file
php artisan test --compact --filter=it_creates_a_product      # Single test
```

Every feature, service, and model must have test coverage: happy path + validation failure + edge cases.

---

## Useful Commands

```bash
# ── Development ──────────────────────────────────────────────────────────────
composer run dev                  # Start Vite + Laravel server together
npm run dev                       # Vite dev server (listens on 192.168.1.4)
npm run build                     # Production asset build
php artisan serve                 # Laravel dev server (port 8000)

# ── Database ──────────────────────────────────────────────────────────────────
php artisan migrate               # Run pending migrations
php artisan migrate --seed        # Migrate + run seeders
php artisan migrate:fresh --seed  # Drop all + migrate + seed
php artisan storage:link          # Symlink public/storage

# ── Inspection ────────────────────────────────────────────────────────────────
php artisan route:list --except-vendor          # All application routes
php artisan route:list --name=products          # Filter by route name
php artisan config:show database                # Show database config
php artisan list                                # All Artisan commands

# ── Code Quality ──────────────────────────────────────────────────────────────
vendor/bin/pint --dirty           # Format changed PHP files
vendor/bin/phpstan analyse        # Static analysis (level 6)

# ── Testing ───────────────────────────────────────────────────────────────────
php artisan test --compact        # Full test suite
```

---

## Git Conventions

| Prefix | When to use |
|---|---|
| `feat:` | New feature |
| `fix:` | Bug fix |
| `refactor:` | Code refactoring (no behavior change) |
| `docs:` | Documentation updates |
| `test:` | Tests or test fixes |
| `style:` | Code style formatting (Pint) |

Example: `feat: add product variants with repository and tests`

---

## Deployment

The recommended target is [Laravel Cloud](https://cloud.laravel.com/), which auto-scales, handles zero-downtime deploys, and manages workers.

Before any deployment, run:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

Ensure: `APP_ENV=production`, `APP_DEBUG=false`, queue worker running (if needed), Reverb WebSocket server running (if real-time features enabled).

---

## Project Status

- **Admin Panel**: Complete — product/category/settings management
- **Storefront**: In active development — product browsing, search, filtering
