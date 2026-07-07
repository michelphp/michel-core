# Michel PHP Framework Core 🚀

The super-lightweight, versatile, and modern PHP engine. No bloat, no YAML, no XML. Just clean, raw PHP power!

## 🤔 Why Michel PHP?

Modern frameworks are awesome, but they often come with heavy overhead and complex configuration layers. Michel PHP takes a different path:

1. **Raw Speed**: No config parsing (YAML/XML) on every request. Everything is written in pure PHP, making boot time lightning fast.
2. **Modular Architecture**: Swap out the router, template engine, or DI container by changing a single closure. It's built entirely on PSR interfaces.
3. **KISS Principle**: Minimalist, clean, and immediately readable code. No black magic under the hood.

## 🌟 Core Features

- ⚡ **Fully PSR-Compliant**: Built on top of PSR-7 (HTTP), PSR-11 (DI Container), PSR-14 (Events), PSR-15 (Middleware), and PSR-3 (Logging).
- 🛠️ **Developer-Centric**: Automatic route discovery, CLI command scanner, and an interactive project initializer.
- 🔒 **Secure out-of-the-box**: Easy maintenance mode toggling, HTTPS redirection, and IP restriction middlewares.
- 🎨 **Minimal & KISS**: Designed to get out of your way. Only use the components you actually need.

---

## 🚀 Quickstart in 1-2-3

### 1. Grab the Package
```bash
composer require michel/michel-core
```

### 2. Boot the Kernel (`public/index.php`)
```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$kernel = new App\Kernel();
$response = $kernel->handle(create_request());
send_http_response($response);
```

### 3. Initialize your project wizard
```bash
php bin/michel project:init
```

---

## 🛠️ The Global Helpers Playground

We expose simple global helpers to make coding enjoyable again:

```php
// 📦 Grab services from the Container
$db = container()->get(Database::class);

// 🌐 Create quick HTTP responses
return response('<h1>Hello World!</h1>', 200);

// 🤖 Build JSON API responses in a snap
return json_response(['status' => 'success', 'data' => $users]);

// 🎨 Render templates instantly
return render('welcome.html.plate', ['username' => 'Michel']);
```

---

*Made with ❤ in Paris. Happy coding!*
