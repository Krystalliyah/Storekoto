# Installed Packages Documentation

This document provides a comprehensive guide to all packages installed in the iTinda application, including their purpose, version, and usage examples.

---

## PHP Backend Packages

### Core Framework & Core Dependencies

#### **Laravel Framework (^12.0)**
- **Purpose**: Core PHP web application framework
- **Usage**: All server-side routing, models, controllers, and middleware
- **Key Features**:
  - Request/response handling
  - Database ORM (Eloquent)
  - Authentication scaffolding
  - Queue system for background jobs

#### **Laravel Reverb (^1.9)**
- **Purpose**: WebSocket server for real-time notifications and broadcasting
- **Installation**: Already configured (see `config/reverb.php`)
- **Usage in Project**: 
  ```php
  // Broadcasting events
  broadcast(new OrderStatusUpdated($order))->toOthers();
  ```
- **Configuration**: Edit `config/reverb.php` and `config/broadcasting.php`
- **Starting Reverb**: 
  ```bash
  php artisan reverb:start
  ```

#### **Laravel Tinker (^2.10.1)**
- **Purpose**: Interactive REPL (Read-Eval-Print Loop) for Laravel
- **Usage**: 
  ```bash
  php artisan tinker
  ```
- **Example in Tinker**:
  ```php
  User::first();
  User::count();
  cache()->put('key', 'value', 3600);
  ```

#### **Laravel Wayfinder (^0.1.9)**
- **Purpose**: Breadcrumb and navigation menu generation
- **Configuration**: `config/wayfinder.php`
- **Usage**: Automatically generates breadcrumbs for navigation

### Multi-Tenancy & Permissions

#### **Tenancy (^3.9)** - Stancl
- **Purpose**: Multi-tenancy package for customer isolation and data separation
- **Configuration**: `config/tenancy.php`
- **Usage**:
  - Automatic tenant detection from subdomain
  - Tenant-specific database connections
  - Automatic middleware application
- **Key Files**:
  - `app/Providers/TenantServiceProvider.php`
  - `bootstrap/providers.php` (tenancy registration)
- **Example**:
  ```php
  // Access current tenant
  $tenant = Tenant::current();
  $tenant_id = tenant('id');
  ```

#### **Laravel Permission (^6.24)** - Spatie
- **Purpose**: Fine-grained permission and role management
- **Configuration**: `config/permission.php`
- **Database Tables Created**:
  - `permissions`
  - `roles`
  - `role_has_permissions`
  - `model_has_roles`
  - `model_has_permissions`
- **Usage Examples**:
  ```php
  // Assign role to user
  $user->assignRole('vendor');
  
  // Check permission
  if ($user->hasPermissionTo('manage-products')) {
    // do something
  }
  
  // Check role
  if ($user->hasRole('vendor')) {
    // do something
  }
  ```

### Authentication & Security

#### **Laravel Fortify (^1.30)**
- **Purpose**: Headless authentication system with support for 2FA, email verification
- **Configuration**: `config/fortify.php`
- **Features Enabled**:
  - Two-factor authentication
  - Email verification
  - Password reset
  - Account deletion
- **Usage**:
  ```php
  // Access authenticated user
  $user = auth()->user();
  
  // Check if user is verified
  if ($user->hasVerifiedEmail()) {
    // allow access
  }
  ```

### API & Inertia Integration

#### **Inertia.js Laravel (^2.0)**
- **Purpose**: Seamless bridge between Vue.js frontend and Laravel backend
- **Configuration**: `config/inertia.php`
- **Usage in Controllers**:
  ```php
  use Inertia\Inertia;
  
  return Inertia::render('Dashboard', [
    'users' => User::all(),
    'stats' => $this->getStats(),
  ]);
  ```
- **Automatic Props Sharing**: 
  - Add props to `Inertia::share()` in service providers
  - Props automatically available in all Vue components

---

## Development Tools (PHP)

#### **Laravel Pint (^1.24)**
- **Purpose**: Automatic PHP code formatter and style checker
- **Configuration**: `pint.json`
- **Usage**:
  ```bash
  # Format code
  php artisan pint
  
  # Check without fixing
  php artisan pint --test
  
  # Format specific file(s)
  pint app/Models/User.php
  ```

#### **Laravel Sail (^1.41)**
- **Purpose**: Docker environment for local development
- **Configuration**: `docker-compose.yml` in database folder
- **Usage**:
  ```bash
  ./vendor/bin/sail up
  ./vendor/bin/sail artisan migrate
  ```

#### **Laravel Pail (^1.2.2)**
- **Purpose**: Tail application logs in real time
- **Usage**:
  ```bash
  php artisan pail
  
  # Filter specific channels
  php artisan pail --filter=database
  ```

#### **Laravel Boost (^2.0)**
- **Purpose**: Performance optimization package
- **Configuration**: `config/boost.php`
- **Update Hook**: Runs `boost:update` on composer update
- **Usage**: Automatically optimizes performance through caching and compilation

### Testing & Development

#### **Pest (^3.8)** - PHPStan/PestPHP
- **Purpose**: Modern PHP testing framework built on PHPUnit
- **Configuration**: `phpunit.xml`, `tests/Pest.php`
- **Usage**:
  ```bash
  # Run all tests
  php artisan test
  
  # Run specific test
  php artisan test tests/Feature/UserTest.php
  
  # Run with coverage
  php artisan test --coverage
  ```
- **Test Files Location**: `tests/Feature/` and `tests/Unit/`

#### **Pest Plugin - Laravel (^3.2)**
- **Purpose**: Laravel-specific testing helpers for Pest
- **Features**:
  - Database transactions
  - Authentication helpers
  - HTTP testing
- **Usage**:
  ```php
  test('user can login', function () {
    $user = User::factory()->create();
    $this->actingAs($user)
      ->get('/dashboard')
      ->assertOk();
  });
  ```

#### **Mockery (^1.6)**
- **Purpose**: Object mocking library for unit tests
- **Usage**:
  ```php
  $mock = Mockery::mock(PaymentService::class);
  $mock->shouldReceive('process')->andReturn(true);
  ```

#### **Faker (^1.23)** - FakerPHP
- **Purpose**: Generate fake data for testing and seeding
- **Configuration**: Used in factories (`database/factories/`)
- **Usage**:
  ```php
  // In factories
  'name' => fake()->name(),
  'email' => fake()->unique()->safeEmail(),
  'phone' => fake()->phoneNumber(),
  ```

#### **Collision (^8.6)**
- **Purpose**: Better exception and error reporting in console
- **Benefit**: Clearer error output with stack traces
- **Automatic**: Works automatically with Pest and Laravel

---

## JavaScript/Node.js Packages

### Core Framework & Build Tools

#### **Vue 3 (^3.5.13)**
- **Purpose**: Progressive JavaScript framework for UI
- **Usage**:
  ```vue
  <template>
    <div>{{ message }}</div>
  </template>
  
  <script setup lang="ts">
  import { ref } from 'vue'
  const message = ref('Hello World')
  </script>
  ```
- **Documentation**: https://vuejs.org

#### **Vite (^7.0.4)**
- **Purpose**: Next-generation frontend build tool
- **Configuration**: `vite.config.ts`
- **Usage**:
  ```bash
  # Development server
  npm run dev
  
  # Build for production
  npm run build
  
  # Build with SSR
  npm run build:ssr
  ```

#### **Vite Plugin Vue (^6.0.0)**
- **Purpose**: Official Vite plugin for Vue 3
- **Auto-configured**: Included in `vite.config.ts`

#### **TypeScript (^5.2.2)**
- **Purpose**: Typed superset of JavaScript
- **Configuration**: `tsconfig.json`
- **Usage**: Write `.ts` and `.vue` files with type safety
- **Compilation**: Automatic via Vite

#### **Vue TSC (^2.2.4)**
- **Purpose**: Type-check Vue files
- **Usage**:
  ```bash
  # Type check all Vue files
  vue-tsc --noEmit
  ```

### Inertia & State Management

#### **Inertia.js Vue 3 (^2.3.7)**
- **Purpose**: Frontend adapter for Inertia.js Laravel bridge
- **Configuration**: Automatic in main.ts
- **Usage in Components**:
  ```vue
  <script setup lang="ts">
  import { usePage } from '@inertiajs/vue3'
  
  const { props } = usePage()
  </script>
  ```
- **Routing**: Use `Link` component for navigation
  ```vue
  <Link href="/users">Users</Link>
  ```

#### **Laravel Echo Vue (^2.3.3)**
- **Purpose**: Vue 3 integration for real-time broadcasting
- **Usage**:
  ```vue
  <script setup>
  import { useBroadcast } from '@laravel/echo-vue'
  
  const { subscribe } = useBroadcast()
  subscribe('channel-name').listen('EventName', (event) => {
    console.log(event)
  })
  </script>
  ```

#### **Vue Router (^5.0.4)**
- **Purpose**: Official router for Vue 3
- **Note**: Currently project uses Inertia routing (no SPA routes)
- **Available for**: Custom route handling if needed

### UI Component Library & Utilities

#### **Reka UI (^2.6.1)**
- **Purpose**: Unstyled, accessible component library
- **Usage**: Base components like Dialog, Select, Combobox
- **Example**:
  ```vue
  <SelectRoot v-model="selectedValue">
    <SelectTrigger>
      <SelectValue />
    </SelectTrigger>
    <SelectContent>
      <SelectItem value="option1">Option 1</SelectItem>
    </SelectContent>
  </SelectRoot>
  ```

#### **Heroicons Vue (^2.2.0)**
- **Purpose**: Beautiful hand-crafted SVG icons from Tailwind Labs
- **Usage**:
  ```vue
  <script setup>
  import { CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline'
  </script>
  
  <template>
    <CheckIcon class="h-6 w-6" />
  </template>
  ```
- **Icon Sets**: outline, solid, mini

#### **Lucide Vue Next (^0.468.0)**
- **Purpose**: Beautiful, consistent SVG icon library
- **Usage**:
  ```vue
  <script setup>
  import { AlertCircle, Calendar } from 'lucide-vue-next'
  </script>
  
  <template>
    <AlertCircle />
  </template>
  ```

#### **Vue Sonner (^2.0.9)**
- **Purpose**: Toast notification library for Vue 3
- **Usage**:
  ```javascript
  import { toast } from 'vue-sonner'
  
  toast.success('Profile updated!')
  toast.error('Something went wrong', { duration: 5000 })
  ```

#### **Vue Input OTP (^0.3.2)**
- **Purpose**: One-Time Password input component for 2FA
- **Usage**:
  ```vue
  <script setup>
  import InputOtp from 'vue-input-otp'
  </script>
  
  <template>
    <InputOtp @update:modelValue="handleOtpChange" />
  </template>
  ```

### Styling & Theming

#### **Tailwind CSS (^4.1.1)**
- **Purpose**: Utility-first CSS framework
- **Configuration**: `tailwindcss` config in `package.json` or `tailwind.config.js`
- **Usage**:
  ```html
  <div class="flex items-center gap-4 rounded-lg bg-blue-50 p-4">
    <h1 class="text-2xl font-bold">Hello</h1>
  </div>
  ```
- **CSS File**: `resources/css/app.css` and `resources/css/dashboard.css`

#### **Tailwind CSS Vite (^4.1.11)**
- **Purpose**: Vite plugin integration for Tailwind CSS
- **Auto-configured**: Works automatically with Vite

#### **Tailwind CSS for Oxide (Windows - ^4.0.1)**
- **Purpose**: Windows native binary for Tailwind CSS compilation
- **Optional Dependency**: Improves build performance

#### **Class Variance Authority (^0.7.1)**
- **Purpose**: Type-safe variant management for component styling
- **Usage**:
  ```javascript
  import { cva } from 'class-variance-authority'
  
  const buttonVariants = cva('btn', {
    variants: {
      intent: {
        primary: 'btn-primary',
        secondary: 'btn-secondary',
      },
    },
  })
  ```

#### **clsx (^2.1.1)**
- **Purpose**: Utility for combining class names conditionally
- **Usage**:
  ```javascript
  import clsx from 'clsx'
  
  clsx('btn', isActive && 'btn-active', { 'btn-disabled': isDisabled })
  ```

#### **Tailwind Merge (^3.2.0)**
- **Purpose**: Merge Tailwind CSS classes without conflicts
- **Usage**:
  ```javascript
  import { twMerge } from 'tailwind-merge'
  
  twMerge('px-2 py-4 px-4') // Returns 'py-4 px-4' (px-4 overrides px-2)
  ```

#### **Prettier Plugin Tailwindcss (^0.6.11)**
- **Purpose**: Auto-sort Tailwind CSS classes
- **Configuration**: Included in `.prettierrc`
- **Usage**: Automatic on save or `npm run format`

#### **tw-animate-css (^1.2.5)**
- **Purpose**: Extended animation utilities for Tailwind CSS
- **Usage**: Custom animations like bounce, fade, slide-in, etc.

### Utility Libraries

#### **Axios (^1.14.0)**
- **Purpose**: HTTP client for API requests
- **Configuration**: Usually configured in main.ts
- **Usage**:
  ```javascript
  import axios from 'axios'
  
  axios.get('/api/users')
    .then(response => console.log(response.data))
    .catch(error => console.error(error))
  ```

#### **@VueUse/Core (^12.8.2)**
- **Purpose**: Collection of essential Vue 3 composition functions
- **Usage**:
  ```javascript
  import { useLocalStorage, useWindowSize, useClipboard } from '@vueuse/core'
  
  const { width, height } = useWindowSize()
  const text = useLocalStorage('key', 'default')
  ```

#### **Laravel Vite Plugin (^2.0.0)**
- **Purpose**: Vite plugin for Laravel integration
- **Configuration**: Auto-configured in `vite.config.ts`
- **Features**:
  - Asset compilation
  - Hot module replacement
  - SSR support

#### **Laravel Vite Plugin Wayfinder (^0.1.3)**
- **Purpose**: Breadcrumb and navigation integration with Vite
- **Usage**: Automatic breadcrumb path generation

#### **Pusher JS (^8.5.0)**
- **Purpose**: WebSocket client for real-time features
- **Configuration**: `config/broadcasting.php` on backend
- **Usage**: Client-side subscription to events
  ```javascript
  import Pusher from 'pusher-js'
  
  const pusher = new Pusher(import.meta.env.VITE_PUSHER_APP_KEY)
  const channel = pusher.subscribe('my-channel')
  ```

#### **Laravel Echo (^2.3.3)**
- **Purpose**: Wrapper around Pusher for Laravel broadcasting
- **Usage**:
  ```javascript
  import Echo from 'laravel-echo'
  
  window.Echo.channel('orders')
    .listen('OrderStatusUpdated', (event) => {
      console.log(event)
    })
  ```

#### **@Inertiajs/vue3**
- **Purpose**: Already documented above

### Development & Code Quality

#### **ESLint (^9.17.0)**
- **Purpose**: JavaScript linter for code quality
- **Configuration**: `eslint.config.js`
- **Usage**:
  ```bash
  # Lint and fix issues
  npm run lint
  
  # Check without fixing
  npx eslint .
  ```
- **Plugins Included**:
  - `@eslint/js` - Core ESLint rules
  - `eslint-plugin-import` - Import/export linting
  - `eslint-plugin-vue` - Vue-specific rules
  - `@vue/eslint-config-typescript` - TypeScript support

#### **TypeScript ESLint (^8.23.0)**
- **Purpose**: TypeScript support for ESLint
- **Configuration**: Included in `eslint.config.js`

#### **ESLint Plugin Vue (^9.32.0)**
- **Purpose**: Vue-specific linting rules
- **Configuration**: Auto-configured

#### **ESLint Plugin Import (^2.32.0)**
- **Purpose**: Import/export statement validation
- **Rules**: Detect unused imports, cyclic dependencies

#### **ESLint Import Resolver TypeScript (^4.4.4)**
- **Purpose**: TypeScript resolver for ESLint import plugin
- **Benefit**: Correctly resolves TypeScript paths and aliases

#### **ESLint Config Prettier (^10.0.1)**
- **Purpose**: Disables ESLint rules conflicting with Prettier
- **Configuration**: Included in `eslint.config.js`

#### **Prettier (^3.4.2)**
- **Purpose**: Code formatter
- **Configuration**: `.prettierrc` or `package.json`
- **Usage**:
  ```bash
  # Format all files
  npm run format
  
  # Check without formatting
  npm run format:check
  ```

#### **@Types/Node (^22.13.5)**
- **Purpose**: TypeScript type definitions for Node.js
- **Usage**: Type safety when using Node.js APIs

#### **Concurrently (^9.0.1)**
- **Purpose**: Run multiple npm scripts simultaneously
- **Usage**: Already used in composer scripts
  ```javascript
  concurrently "npm run dev" "npm run watch"
  ```

---

## Environment Configuration

### Required Environment Variables

Create a `.env` file based on `.env.example` with these critical packages:

```env
# Broadcasting (Reverb)
BROADCAST_DRIVER=reverb
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=
REVERB_PORT=8080

# Real-time notifications (Pusher)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=

# Queue (if enabled)
QUEUE_CONNECTION=database
```

---

## Common Tasks & Examples

### Adding a New Permission
```php
// In migration or tinker
use Spatie\Permission\Models\Permission;

Permission::create(['name' => 'view-reports']);
```

### Broadcasting an Event
```php
// In your event class
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class OrderStatusUpdated implements ShouldBroadcast
{
    public function broadcastOn()
    {
        return new Channel('orders');
    }
}

// In your controller
broadcast(new OrderStatusUpdated($order))->toOthers();
```

### Creating a Vue Component
```vue
<template>
  <div class="rounded-lg bg-white p-4 shadow">
    <h2 class="mb-4 text-xl font-bold">{{ title }}</h2>
    <button
      @click="handleClick"
      class="rounded bg-blue-500 px-4 py-2 text-white hover:bg-blue-600"
    >
      Click Me
    </button>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { toast } from 'vue-sonner'

interface Props {
  title: string
}

defineProps<Props>()

const count = ref(0)

const handleClick = () => {
  count.value++
  toast.success(`Clicked ${count.value} times!`)
}
</script>
```

### Running Tests
```bash
# All tests
php artisan test

# Specific test file
php artisan test tests/Feature/OrderTest.php

# With coverage
php artisan test --coverage

# Lint check
php artisan test:lint
```

---

## Maintenance & Updates

### Keeping Packages Updated

**PHP Packages:**
```bash
composer update             # Update all packages
composer update laravel/framework  # Update specific package
php artisan optimize        # Optimize autoloader
```

**JavaScript Packages:**
```bash
npm update                  # Update all packages
npm update vue             # Update specific package
npm audit fix              # Fix security vulnerabilities
```

### Checking for Vulnerabilities

```bash
# PHP
composer audit

# JavaScript
npm audit
```

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Permission denied errors | Clear cache: `php artisan cache:clear` |
| TypeScript errors in Vue | Run: `vue-tsc --noEmit` to check |
| Vite HMR not working | Check `VITE_HMR_HOST` in `.env` |
| Reverb not connecting | Verify `REVERB_PORT` and firewall settings |
| Tests failing | Run `php artisan test --fails-on-warning` for more details |

---

## Quick Reference Commands

```bash
# Development
npm run dev                 # Start Vite dev server
php artisan serve          # Start Laravel server
php artisan reverb:start   # Start WebSocket server
npm run lint               # Lint JavaScript
php artisan test           # Run tests

# Building
npm run build              # Build for production
npm run build:ssr          # Build with SSR support
composer install           # Install PHP dependencies
npm install                # Install Node dependencies

# Maintenance
php artisan migrate        # Run database migrations
php artisan cache:clear    # Clear all caches
npm audit fix              # Fix npm vulnerabilities
composer audit             # Check for PHP vulnerabilities
```

---

**Last Updated**: April 2, 2026