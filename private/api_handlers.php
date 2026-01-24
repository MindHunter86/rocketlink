<?php

declare(strict_types=1);

require_once(__DIR__ . '/utils/database.php');

function api_ping(): void
{
    $_SESSION['ping_count'] = ($_SESSION['ping_count'] ?? 0) + 1;

    db_test();

    json_response([
        'ok' => true,
        'time' => time(),
        'ping_count' => $_SESSION['ping_count'],
    ]);
}

function api_items_list(): void
{
    // $items = db_all('SELECT id, title, created_at FROM items ORDER BY created_at DESC');
    // json_response(['items' => $items]);
}

function api_items_create(): void
{
    $body  = request_json();
    $title = trim((string)($body['title'] ?? ''));
    if ($title === '') json_response(['error' => 'title required'], 400);

    $id = bin2hex(random_bytes(8));
    // db_exec(
    //     'INSERT INTO items (id, title, created_at) VALUES (:id, :title, :t)',
    //     ['id' => $id, 'title' => $title, 't' => time()]
    // );

    json_response(['ok' => true, 'id' => $id]);
}

require_once(__DIR__ . '/api/api.php');
