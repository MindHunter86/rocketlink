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

// printf($apiPath);

$routes = [
    'GET' => [
        // internal routers
        '/ping'  => 'api_ping',
        '/accounts/users/debug/session' => 'api_account_session_debug',

        // account users subrouter
        '/accounts/users/payment' => 'api_account_payment',
        '/accounts/users/order' => 'api_account_order',

        // links subrouter
        '#^/links/(?P<id>[a-zA-Z0-9]+)$#' => 'api_get_link',

        // order subrouter
        '#^/links/(?P<id>[0-9]+)$#' => 'api_get_order',
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

        // orders subrouter
        '/order' => 'api_post_order',
    ],

    'UPDATE' => [
        // account users subrouter
        '/accounts/users/session/update' => 'api_account_session_update',
        '/accounts/users/payment' => 'api_account_payment_update',

        // links subrouter
        '/links' => 'api_update_link',
    ],
];

require_once(__DIR__ . '/api_handlers.php');
router_dispatch($routes, $method, $apiPath);

// router core
// function router_dispatch(array $routes, string $method, string $path): never
// {
//     $handler = $routes[$method][$path] ?? null;
//     if (is_string($handler) && function_exists($handler)) {
//         $handler();
//         exit;
//     }

//     foreach ($routes[$method] as $route => $func) {
//         if (!preg_match(str_replace('/', '\/', $route), $path)) continue;

//         $handler = $func ?? null;
//         if (is_string($handler) && function_exists($handler)) {
//             $handler();
//             exit;
//         }
//     }

//     // 405
//     $allowed = [];
//     foreach ($routes as $m => $map) {
//         if (isset($map[$path])) $allowed[] = $m;
//     }
//     if ($allowed) {
//         header('Allow: ' . implode(', ', $allowed));
//         json_response(['error' => 'Method not allowed', 'allow' => $allowed], 405);
//     }

//     json_response_error('Not found', 404);
// }

// router core with regexp support
function router_dispatch(array $routes, string $method, string $path): never
{
    // fast path
    if (isset($routes[$method][$path])) {
        $handler = $routes[$method][$path];
        call_handler($handler, []);
        exit;
    }

    // regexp path
    foreach (($routes[$method] ?? []) as $pattern => $handler) {
        if (!is_string($pattern) || !is_regex_key($pattern)) continue;

        $m = [];
        if (preg_match($pattern, $path, $m)) {
            $params = extract_named_params($m);
            call_handler($handler, $params);
            exit;
        }
    }

    json_response_error('Not found', 404);
}

function is_regex_key(string $key): bool
{
    $c = $key[0] ?? '';
    return $c === '#' || $c === '~';
}

function extract_named_params(array $matches): array
{
    $out = [];
    foreach ($matches as $k => $v) {
        if (is_string($k)) $out[$k] = $v;
    }
    return $out;
}

function call_handler(string $handler, array $params): void
{
    if (!function_exists($handler)) {
        json_response_error('Not found', 500, 'Undefined handler detected');
    }

    // function handlername(array $params = []): void
    $handler($params);
}
