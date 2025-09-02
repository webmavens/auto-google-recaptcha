<?php

$config = require __DIR__ . '/config/auto-google-recaptcha.php';

$secret = $config['secret'];
$sitekey = $config['sitekey'];

$captcha = new WebMavens\AutoGoogleRecaptcha\AutoGoogleRecaptcha($secret, $sitekey, $config['options']);

// Only run captcha if enabled
if ($captcha->isEnabled()) {
    $method = $_SERVER['REQUEST_METHOD'];
    $uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $requiresCaptcha = in_array($method, $config['options']['allowed_methods'])
                       && !isExcluded($uri, $config['options']['excluded_routes']);

    if ($requiresCaptcha) {
        $captchaResponse = $_POST['g-recaptcha-response'] ?? null;

        if (!$captchaResponse) {
            die("Captcha missing. Request blocked.");
        }

        if (!$captcha->verify($captchaResponse, $_SERVER['REMOTE_ADDR'])) {
            die("Captcha failed. Request blocked.");
        }
    }
}

function isExcluded($uri = '/', $excludedRoutes = []) {
    foreach ($excludedRoutes as $pattern) {
        // fnmatch supports wildcards like admin* or *admin
        if (fnmatch($pattern, ltrim($uri, '/'))) {
            return true;
        }
    }
    return false;
}
