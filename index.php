<?php

declare(strict_types=1);

require_once(__DIR__ . '/private/utils.php');

$path = request_path();

if (str_starts_with($path, '/api/')) {
    require_once(__DIR__ . '/private/api.php');
    exit;
} else if (str_starts_with($path, '/r/')) {
    require_once(__DIR__ . '/private/redirect.php');
    exit;
}

// fallback page
header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="ru">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>School SPA</title>
    <link rel="stylesheet" href="/assets/style.css" />
</head>

<body>
    <div id="app"></div>
    <script src="/assets/app.js"></script>
</body>

</html>