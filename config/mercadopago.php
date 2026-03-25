<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Access token
    |--------------------------------------------------------------------------
    |
    | Production or test access token from Mercado Pago (Credentials).
    | See: https://www.mercadopago.com.br/developers/pt/reference
    |
    */

    'access_token' => env('MERCADO_PAGO_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | SDK runtime
    |--------------------------------------------------------------------------
    |
    | Use "local" when testing with sandbox credentials on localhost.
    |
    */

    'runtime' => env('MERCADO_PAGO_RUNTIME', 'server'),

    /*
    |--------------------------------------------------------------------------
    | Checkout Pro return URLs
    |--------------------------------------------------------------------------
    |
    | Where the payer is redirected after Checkout Pro (frontend URLs).
    |
    */

    'back_urls' => [
        'success' => env('MERCADO_PAGO_BACK_SUCCESS_URL', env('APP_URL').'/checkout/success'),
        'failure' => env('MERCADO_PAGO_BACK_FAILURE_URL', env('APP_URL').'/checkout/failure'),
        'pending' => env('MERCADO_PAGO_BACK_PENDING_URL', env('APP_URL').'/checkout/pending'),
    ],

    'auto_return' => env('MERCADO_PAGO_AUTO_RETURN', 'approved'),

    'statement_descriptor' => env('MERCADO_PAGO_STATEMENT_DESCRIPTOR', 'EVENTMASTER'),

    'notification_url' => env('MERCADO_PAGO_NOTIFICATION_URL'),

];
