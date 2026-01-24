<?php

declare(strict_types=1);

// сюда попадаем из public/index.php, helpers.php уже подключён
$method = request_method();
$path   = request_path();

// Нормализуем /api/... -> /...
$apiPath = substr($path, 4); // убираем "/api"
$apiPath = $apiPath === '' ? '/' : $apiPath;

$file = data_path('items.json');

function storage_read(string $file): array
{
    if (!is_file($file)) return [];
    $raw = file_get_contents($file);
    if (!is_string($raw) || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function storage_write(string $file, array $data): void
{
    // гарантируем, что папка data/ существует
    $dir = dirname($file);
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    // безопасная запись с блокировкой
    $fp = fopen($file, 'c+');
    if ($fp === false) {
        json_response(['error' => 'storage unavailable'], 500);
    }
    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        json_response(['error' => 'storage locked'], 500);
    }

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
}

if ($apiPath === '/ping' && $method === 'GET') {
    json_response(['ok' => true, 'time' => time()]);
}

if ($apiPath === '/items' && $method === 'GET') {
    $items = storage_read($file);
    json_response(['items' => $items]);
}

if ($apiPath === '/items' && $method === 'POST') {
    $body  = request_json();
    $title = trim((string)($body['title'] ?? ''));

    if ($title === '') {
        json_response(['error' => 'title required'], 400);
    }

    $items = storage_read($file);
    $items[] = [
        'id'    => bin2hex(random_bytes(8)),
        'title' => $title,
    ];
    storage_write($file, $items);

    json_response(['ok' => true]);
}

// Если путь существует, но метод не тот — можно 405, но минимально: 404
json_response(['error' => 'Not found'], 404);
