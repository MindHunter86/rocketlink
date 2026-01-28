<?

declare(strict_types=1);

// utils includes
require_once(__DIR__ . '/utils.php');
require_once(__DIR__ . '/../utils/database.php');
require_once(__DIR__ . '../../../config/shorten.php');

// validate DB:mysql table schema
ensure_schema();

// start default PHPSESS sessions engine
start_session();

// session upgrade to anonymous
require_once(__DIR__ . '/../utils/session.php');
if (!session_is_exists_accounts()) session_deauthenticate();

// subroutes
require_once(__DIR__ . '/redirect.php');

// internal handlers