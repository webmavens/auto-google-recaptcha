<?php
namespace WebMavens\AutoGoogleRecaptcha;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class AutoGoogleRecaptcha
{
    const CLIENT_API = 'https://www.google.com/recaptcha/api.js';
    const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
    const LOCAL_SCRIPT_PATH = '/vendor/web-mavens/auto-google-recaptcha/resources/js/auto-recaptcha.js';

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

    public function verify($response, $ip = null)
    {
        if (!$response) {
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


    public function renderJs($lang = null)
    {
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

    public function isEnabled(): bool
    {
        return (bool) ($this->options['enable'] && !empty($this->sitekey) && !empty($this->secret));
    }
}