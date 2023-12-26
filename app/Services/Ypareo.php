<?php

namespace App\Services;

use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Ypareo service
 */
class Ypareo
{
    /**
     * @var string
     */
    protected $apiKey = null;

    /**
     * @var string
     */
    protected $baseUrl = null;

    /**
     * Construct Ypareo service
     *
     * @param string $apiKey
     * @param string $baseUrl
     */
    public function __construct($apiKey, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Retrieve CSRF token from login page
     *
     * @param  string $userAgent
     * @return string
     */
    protected function getLoginCsrf($userAgent, CookieJar $cookieJar): string
    {
        return '**CSRF**';
    }

    /**
     * Authenticate a user against Ypareo
     *
     * @param  string $username
     * @param  string $password
     * @param  string $userAgent
     * @return bool
     */
    public function auth($username, $password, $userAgent): bool
    {
        return true;
    }
}
