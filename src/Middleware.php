<?php

$config = require __DIR__ . '/auto-google-recaptcha.php';

$secret = $config['secret'];
$sitekey = $config['sitekey'];

$captcha = new WebMavens\AutoGoogleRecaptcha\AutoGoogleRecaptcha($secret, $sitekey);

// Only run captcha if enabled
if ($config['enabled']) {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $requiresCaptcha = in_array($method, $config['allowed_methods'])
                       && !in_array($uri, $config['excluded_routes']);

    if ($requiresCaptcha) {
        $captchaResponse = $_POST['g-recaptcha-response'] ?? null;

        if (!$captchaResponse) {
            die("Captcha missing. Request blocked.");
        }

        if ($captcha->verify($captchaResponse, $_SERVER['REMOTE_ADDR'])) {
            die("Captcha failed. Request blocked.");
        }
    }
}