# Auto Google reCAPTCHA

A package to add **Google reCAPTCHA v2 Invisible / v3** protection to your entire application forms.
Supports **Laravel (all versions)** and **Core PHP projects**.

---

## 🚀 Installation

Require the package via Composer:

```bash
composer require webmavens/auto-google-recaptcha
```

---

## ⚙️ Laravel Setup

### Laravel 11 and above

No manual registration needed — **Service Provider and Facade are auto-discovered**.

#### Middleware

In **Laravel 11+**, add the middleware to the `web` group in your `bootstrap/app.php` file:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->group('web', [
        \WebMavens\AutoGoogleRecaptcha\Laravel\Middleware\VerifyRecaptcha::class,
    ]);
})
```

---

### Laravel 10 and below

For older Laravel versions, you need to register service provider, facade, and middleware manually.

#### Service Provider

Add to `config/app.php` providers array:

```php
'providers' => [
    // ...
    WebMavens\AutoGoogleRecaptcha\AutoGoogleRecaptchaServiceProvider::class,
],
```

#### Facade

Add to `config/app.php` aliases array:

```php
'aliases' => [
    // ...
    'AutoReCaptcha' => WebMavens\AutoGoogleRecaptcha\Laravel\Facades\AutoGoogleRecaptcha::class,
],
```

#### Middleware

Add to `app/Http/Kernel.php` in the `web` middleware group:

```php
protected $middlewareGroups = [
    'web' => [
        // ...
        \WebMavens\AutoGoogleRecaptcha\Laravel\Middleware\VerifyRecaptcha::class,
    ],
];
```

---

### Publish Config & Assets

For all Laravel versions, publish the config and JS file:

```bash
php artisan vendor:publish --provider="WebMavens\AutoGoogleRecaptcha\Laravel\AutoGoogleRecaptchaServiceProvider"
```

This publishes:

* Config → `config/auto-google-recaptcha.php`
* JS → `public/vendor/auto-google-recaptcha/auto-recaptcha.js`

---

### Environment Variables

Get the sitekey and secret from [Google Recaptcha](https://www.google.com/u/2/recaptcha/admin/create). Add the following to your `.env` file,

```env
NOCAPTCHA_SITEKEY=your-site-key
NOCAPTCHA_SECRET=your-secret-key
NOCAPTCHA_ENABLE=true
```

---

### Render JS

Add this to your main layout (e.g. `layouts/app.blade.php`):

```blade
{!! AutoReCaptcha::renderJs() !!}
```

This ensures reCAPTCHA script is injected globally.

---

## ⚙️ Core PHP Setup

You can also use this package in plain PHP projects.

1. Copy `Middleware.php` from the package `src/` directory to your php project's root directory.
2. In your `index.php` (or main entry file), include it as this
(`Middleware.php` file handles validations for each requests based on the configuration.):

```php
<?php

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/Middleware.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>reCAPTCHA Test</title>
    <!-- Load Google + package JS -->
    <?= $captcha->renderJs() ?>
</head>
<body>
    <h1>Test reCAPTCHA Form</h1>
    <form method="POST" action="">
        <input type="text" name="name" placeholder="Your Name" required>
        <button type="submit">Submit</button>
    </form>
</body>
</html>
```

3. Add your config in `config/auto-google-recaptcha.php` (same format as Laravel).

---

## 🔧 Config

The published config file looks like this:

```php
return [
    'secret' => env('NOCAPTCHA_SECRET', ''),
    'sitekey' => env('NOCAPTCHA_SITEKEY', ''),
    'options' => [
        'timeout' => 30,

        // Methods requiring captcha
        'allowed_methods' => [
            'POST',
            'PUT',
            'DELETE'
        ],

        // Routes to exclude from captcha
        'excluded_routes' => [
            'admin.*' // supports wildcards
        ],

        // Enable/disable globally
        'enable' => env('NOCAPTCHA_ENABLE', true),
    ],
];
```

If you don't want to add the reCAPTCHA to any form in frontend then add `data-no-captcha` attribute to form tag and it will exclude that form from rendering the reCAPTCHA.

You can use this feature with `allowed_methods` config to completly exclude any form from reCAPTCHA validation.

---

## 🛡️ Behavior

* Captcha enforced only on configured HTTP methods (default: `POST`, `PUT`, `DELETE`)
* Skips validation on excluded routes (`admin.*`, etc.)
* On failure → returns **HTTP 403**
* Validation uses Google reCAPTCHA v2 Invisible or v3 API

---

## ✨ Features

* Works with Laravel **11+** (auto-discovery) and older versions
* Works with **Core PHP** projects
* Global middleware validation
* Configurable methods and route exclusions
* Supports reCAPTCHA v2 Invisible & v3
* Easy asset publishing

---

## 📄 License

MIT License © [Web Mavens](https://github.com/webmavens)

---
