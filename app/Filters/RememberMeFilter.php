<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Adjusts session lifetime based on "remember me" cookie.
 *
 * If the cookie is present, sessions last 7 days; otherwise they use
 * the default 2-hour lifetime.
 */
class RememberMeFilter implements FilterInterface
{
    private const REMEMBER_COOKIE = 'remember_me';
    private const DEFAULT_EXPIRATION = 7200;      // 2 hours
    private const REMEMBER_EXPIRATION = 604800;   // 7 days

    public function before(RequestInterface $request, $arguments = null)
    {
        $config = config('Session');

        $remember = $request->getCookie(self::REMEMBER_COOKIE);
        $config->expiration = $remember === '1'
            ? self::REMEMBER_EXPIRATION
            : self::DEFAULT_EXPIRATION;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No-op
    }
}

