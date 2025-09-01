# auto-google-recaptcha

## Installation

```
composer require web-mavens/auto-google-recaptcha
```

## Laravel 5 and above

### Setup

**_NOTE_** This package supports the auto-discovery feature of Laravel 5.5 and above, So skip these `Setup` instructions if you're using Laravel 5.5 and above.

In `app/config/app.php` add the following :

1- The ServiceProvider to the providers array :

```php
WebMavens\AutoGoogleRecaptcha\Laravel\AutoGoogleRecaptchaServiceProvider::class,
```

2- The class alias to the aliases array :

```php
'AutoReCaptcha' => WebMavens\AutoGoogleRecaptcha\Laravel\Facades\AutoReCaptcha::class,
```

3- Publish the config file

```ssh
php artisan vendor:publish --provider="WebMavens\AutoGoogleRecaptcha\Laravel\AutoGoogleRecaptchaServiceProvider"
```

### Configuration

Add `NOCAPTCHA_SECRET` and `NOCAPTCHA_SITEKEY` in **.env** file :

```
NOCAPTCHA_SECRET=secret-key
NOCAPTCHA_SITEKEY=site-key
```

(You can obtain them from [here](https://www.google.com/recaptcha/admin))