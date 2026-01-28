<?php

declare(strict_types=1);

// moved to api.php
// start_session();
// ensure_schema();

$method = request_method();
$path   = request_path();

$redirectPath = $path === '' ? '/' : $path;

$routes = [
    'GET' => [
        // internal routers

        // shorten redirect subrouter
        '#^/r/(?P<id>[a-zA-Z0-9]+)$#' => 'redirect_link_redirect',
    ]
];

// router bootstrap
require_once(__DIR__ . '/router.php');
require_once(__DIR__ . '/redirect/00init.php');
router_dispatch($routes, $method, $redirectPath);
