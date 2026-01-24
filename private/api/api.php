<?
// validate mysql table schema
ensure_schema();

// start default PHPSESS sessions engine
start_session();

// session upgrade to anonymous
require_once(__DIR__ . '/../utils/session.php');
if (!session_is_exists_accounts()) session_deauthenticate();

// utils includes
require_once(__DIR__ . '/utils.php');

// subroutes
require_once(__DIR__ . '/accounts.php');
require_once(__DIR__ . '/links.php');
require_once(__DIR__ . '/order.php');
require_once(__DIR__ . '/payment.php');
require_once(__DIR__ . '/cart.php');
