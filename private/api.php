<?php

declare(strict_types=1);

// moved to api.php
// start_session();
// ensure_schema();

$method = request_method();
$path   = request_path();

// /api/v1/items -> /items
$apiPath = substr($path, ROUTER_SUBSTR_CHARS);
$apiPath = $apiPath === '' ? '/' : $apiPath;

$routes = [
    'GET' => [
        // internal routers
        '/ping'  => 'api_ping',
        '/debug/session' => 'api_debug_session',

        // account users subrouter
        '/accounts/users/payments' => 'api_account_payments',
        '/accounts/users/orders' => 'api_account_order',

        // links subrouter
        '#^/links/(?P<id>[a-zA-Z0-9]+)$#' => 'api_get_link',
        // '#^/links/(?P<id>[a-zA-Z0-9]+)/stats$#' => 'api_link_stats',

        // orders subrouter
        '#^/orders/(?P<id>[0-9]+)$#' => 'api_get_order',

        // payments subrouter
        '#^/payments/(?P<id>[a-zA-Z0-9]+)$#' => 'api_get_payment',

        // cart subrouter
        '/cart' => 'api_cart',

        // analytics subrouter
        '/analytics' => 'api_analytics',
    ],
    'POST' => [
        // account users subrouter
        '/accounts/users/auth/login' => 'api_account_auth_login',
        '/accounts/users/auth/logout' => 'api_account_auth_logout',
        '/accounts/users/auth/forgot' => 'api_account_auth_forgot',
        '/accounts/users/auth/reset' => 'api_account_auth_reset',

        '/accounts/users/register' => 'api_account_register',

        // links subrouter
        '/links' => 'api_post_link',
        // '#^/links/(?P<id>[a-zA-Z0-9]+)/disable$#' => 'api_link_disable',
        // '#^/links/(?P<id>[a-zA-Z0-9]+)/enable$#' => 'api_link_enable',
        // '#^/links/(?P<id>[a-zA-Z0-9]+)/qrcode$#' => 'api_link_qrcode',
        // '#^/links/(?P<id>[a-zA-Z0-9]+)/stats/reset$#' => 'api_link_stats_reset',

        // cart subrouter
        '/cart' => 'api_cart_post',
        '/cart/flush' => 'api_cart_flush',

        // orders subrouter
        '/order' => 'api_post_order',

        // analytics subrouter
        '/analytics' => 'NULL',
    ],

    'UPDATE' => [
        // account users subrouter
        '/accounts/users/session/update' => 'api_account_session_update',
        '/accounts/users/payment' => 'api_account_payment_update',

        // cart subrouter
        '/cart' => 'api_cart_update',

        // links subrouter
        '/links' => 'api_update_link',
    ],

    'DELETE' => [
        // links subrouter
        '#^/links/(?P<id>[a-zA-Z0-9]+)$#' => 'api_link_delete',

        // cart subrouter
        '#^/cart/(?P<id>[0-9]+)$#' => 'api_cart_delete',
    ]
];

// router bootstrap
require_once(__DIR__ . '/router.php');
require_once(__DIR__ . '/api/00init.php');
router_dispatch($routes, $method, $apiPath);
