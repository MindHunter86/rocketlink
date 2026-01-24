<?php

declare(strict_types=1);

const ROUTER_SUBSTR_CHARS = 7;

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function request_path(): string
{
    $u = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $p = is_string($u) ? $u : '/';
    $p = rtrim($p, '/');
    return $p === '' ? '/' : $p;
}

function json_response(mixed $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function json_response_error(string $message, int $code, ?string $details = ""): never
{
    json_response(
        [
            'error' => $message,
            'has_details' => strlen($details) !== 0 ? true : false,
            'details' => $details
        ],
        $code
    );
}

function request_json(): array
{
    $raw = file_get_contents('php://input');
    if (!is_string($raw) || $raw === '') return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function data_path(string $relative): string
{
    // data/ лежит на уровне project/data
    return dirname(__DIR__) . '/data/' . ltrim($relative, '/');
}


/* ========= базовые ========= */
function env(string $key, ?string $default = null): ?string
{
    $v = getenv($key);
    return ($v === false) ? $default : $v;
}

function is_https(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') return true;
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') return true;
    return false;
}

/* ========= минимальные сессии ========= */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return (string)$_SESSION['csrf'];
}

/* ========= Шифрование (libsodium) =========
   APP_KEY должен быть base64 от 32 байт.
   Пример (Linux):
   php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
*/

function app_key(): string
{
    $b64 = env('APP_KEY');
    if (!$b64) throw new RuntimeException('APP_KEY missing');

    $key = base64_decode($b64, true);
    if ($key === false || strlen($key) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
        throw new RuntimeException('APP_KEY must be base64(32 bytes)');
    }
    return $key;
}

function encrypt_str(string $plain): string
{
    $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    $cipher = sodium_crypto_secretbox($plain, $nonce, app_key());
    return base64_encode($nonce . $cipher);
}

function decrypt_str(string $b64): ?string
{
    $bin = base64_decode($b64, true);
    if ($bin === false) return null;

    $n = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
    if (strlen($bin) < $n) return null;

    $nonce  = substr($bin, 0, $n);
    $cipher = substr($bin, $n);

    $plain = sodium_crypto_secretbox_open($cipher, $nonce, app_key());
    return ($plain === false) ? null : $plain;
}

// accoutns utils
function pass_hash(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

function pass_verify(string $password, string $hash): bool
{
    return password_verify($password, $hash);
}
