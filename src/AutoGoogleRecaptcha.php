<?php

namespace WebMavens\AutoGoogleRecaptcha;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class AutoGoogleRecaptcha
{
    const CLIENT_API = 'https://www.google.com/recaptcha/api.js';
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const LOCAL_SCRIPT_PATH = '/vendor/webmavens/auto-google-recaptcha/resources/js/auto-recaptcha.js';

    protected $secret;
    protected $sitekey;
    protected $options;
    protected $client;

    /**
     * AutoGoogleRecaptcha constructor.
     *
     * @param string $secret
     * @param string $sitekey
     * @param array $options
     */
    public function __construct($secret, $sitekey, array $options = [])
    {
        $this->secret = $secret;
        $this->sitekey = $sitekey;
        $this->options = $options;
        $this->client = new Client(['timeout' => $options['timeout'] ?? 30]);
    }

    /**
     * Verify the given response against the reCAPTCHA service.
     *
     * @param string $response the response from the client
     * @param string|null $ip the ip address of the client (optional)
     *
     * @return bool
     *
     * @throws \RuntimeException if the request to the reCAPTCHA service fails
     */
    public function verify($response, $ip = null)
    {
        if (!$response && !$this->isEnabled()) {
            return false;
        }

        try {
            $res = $this->client->post(static::VERIFY_URL, [
                'form_params' => [
                    'secret' => $this->secret,
                    'response' => $response,
                    'remoteip' => $ip,
                ]
            ]);

        } catch (\Throwable $e) {
            throw new RuntimeException('reCAPTCHA HTTP error: ' . $e->getMessage());
        }

        $body = json_decode((string) $res->getBody(), true);

        return isset($body['success']) && $body['success'] === true;
    }


    /**
     * Renders the Google reCAPTCHA script and a small script to globally
     * inject the site key and an onload callback.
     *
     * @param string $lang Optional language for the reCAPTCHA widget.
     *   If not provided, the default language is used.
     *
     * @return string The HTML tags for the reCAPTCHA script and its config.
     */
    public function renderJs($lang = null)
    {
        if (!$this->isEnabled()) {
            return '<script>console.error("reCAPTCHA is disabled. Please add keys to config.");</script>';
        }

        $params = [];
        $params['render'] = 'explicit';
        $params['onload'] = 'onloadCallback';
        $lang ? $params['hl'] = $lang : null;

        $scriptGoogle = '<script src="'. static::CLIENT_API . '?'. http_build_query($params) .'"></script>';
        $siteKeyGlobal = '<script> window.RECAPTCHA_SITEKEY = "'. $this->sitekey .'"; </script>';

        // Detect Laravel
        $isLaravel = function_exists('app') && class_exists(\Illuminate\Support\Facades\App::class);

        if ($isLaravel) {
            // Use published path in Laravel
            $scriptPackage = '<script src="' . asset('vendor/auto-google-recaptcha/auto-recaptcha.js') . '"></script>';
        } else {
            // For plain PHP, serve directly (relative path from vendor)
            $scriptPackage = '<script src="'. static::LOCAL_SCRIPT_PATH .'"></script>';
        }

        return $scriptGoogle . "\n" . $siteKeyGlobal . "\n" . $scriptPackage;
    }

    /**
     * Determine if reCAPTCHA is enabled.
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) (($this->options['enable'] ?? true) && !empty($this->sitekey) && !empty($this->secret));
    }
}