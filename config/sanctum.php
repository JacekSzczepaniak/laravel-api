<?php

use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Session\Middleware\StartSession;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

return [
    'stateful' => env('APP_ENV') === 'testing'
        ? []
        : explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
            '%s%s',
            'localhost,127.0.0.1,localhost:3000,127.0.0.1:3000',
            env('APP_URL') ? (','.parse_url(env('APP_URL'), PHP_URL_HOST)) : ''
        ))),

    'guard' => ['web'],

    // Czas życia tokenów (w minutach). null = bezterminowo
    'expiration' => null,

    // Prefiks tokenów (opcjonalnie puste)
    'token_prefix' => '',

    // Middleware dla routingu „web” (dla SPA). Nie wpływa na API tokens, ale zostawiamy zgodnie z domyślną konfiguracją.
    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'add_cookies_to_response' => AddQueuedCookiesToResponse::class,
        'start_session' => StartSession::class,
        'verify_csrf_token' => VerifyCsrfToken::class,
    ],

    // Cache mapowania token -> użytkownik. W testach wyłączamy.
    'cache' => [
        'store' => env('APP_ENV') === 'testing'
            ? null
            : env('SANCTUM_CACHE_STORE', null),
        'ttl' => env('APP_ENV') === 'testing'
            ? 0
            : (int) env('SANCTUM_CACHE_TTL', 300),
    ],
];
