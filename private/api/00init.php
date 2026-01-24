<?

declare(strict_types=1);

// utils includes
require_once(__DIR__ . '/utils.php');
require_once(__DIR__ . '/../utils/database.php');

// validate DB:mysql table schema
ensure_schema();

// start default PHPSESS sessions engine
start_session();

// session upgrade to anonymous
require_once(__DIR__ . '/../utils/session.php');
if (!session_is_exists_accounts()) session_deauthenticate();

// subroutes
require_once(__DIR__ . '/accounts.php');
require_once(__DIR__ . '/links.php');
require_once(__DIR__ . '/order.php');
require_once(__DIR__ . '/payment.php');
require_once(__DIR__ . '/cart.php');

// internal handlers
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

function api_debug_session(): void
{
    json_response($_SESSION, 200);
}
