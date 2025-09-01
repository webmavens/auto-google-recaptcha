<?php

namespace WebMavens\AutoGoogleRecaptcha\Laravel;

use Illuminate\Support\ServiceProvider;
use WebMavens\AutoGoogleRecaptcha\AutoGoogleRecaptcha;

class AutoGoogleRecaptchaServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bootConfig();

        // Extend validator rule
        $this->app['validator']->extend('recaptcha', function ($attribute, $value) {
            return $this->app['autorecaptcha']->verify($value, request()->ip());
        });
    }

    protected function bootConfig()
    {
        $path = __DIR__ . '/../../config/auto-google-recaptcha.php';

        $this->mergeConfigFrom($path, 'auto-google-recaptcha');

        if (function_exists('config_path')) {
            $this->publishes([$path => config_path('auto-google-recaptcha.php')]);
        }

        // publish JS file
        if (function_exists('public_path')) {
            $this->publishes([
                __DIR__ . '/../../resources/js/auto-recaptcha.js' => public_path('vendor/auto-google-recaptcha/auto-recaptcha.js'),
            ], 'public');
        }
    }

    public function register()
    {
        $this->app->singleton('autorecaptcha', function ($app) {
            return new AutoGoogleRecaptcha(
                $app['config']['auto-google-recaptcha.secret'],
                $app['config']['auto-google-recaptcha.sitekey'],
                $app['config']['auto-google-recaptcha.options'] ?? []
            );
        });
    }

    public function provides()
    {
        return ['autorecaptcha'];
    }
}
