<?php

declare(strict_types=1);
require_once(__DIR__ . '/../utils/analytics.php');

// TODO: due to it's user page, no JSON responses ?
function redirect_link_redirect(array $params = []): void
{
    $id = param_validation($params['id'] ?? '');
    if ($id === null) json_response_error('invalid data recevied', 400, 'input data validation was not passed');
    if (empty($id)) json_response_error('invalid data recevied', 400, 'recevied id is not valid');

    $res = db_one('SELECT id,owner,destination FROM links where shortenid=?', [$id]);
    if (!$res) json_response_error('requested link not found', 404);

    // if Sec-CH-UA headers enabled in config - send redirect with dummy param ?ch=1
    $is_ch_req = $_GET['ch'] ?? "0";
    if (CONFIG_LINK_SECCH_HEADERS_ENABLE && !$is_ch_req) {
        header('Accept-CH: Sec-CH-UA-Mobile,Sec-CH-UA-Model,Sec-CH-UA-Platform,Sec-CH-UA-Platform-Version,Sec-CH-UA-Viewport-Width,Sec-CH-UA-Viewport-Height');
        header('Location: http://' . CONFIG_LINK_SHORTEN_DOMAIN . '/r/' . $id . '?ch=1');
        http_response_code(302);
        exit;
    }

    analytics_trigger_event($res['id']);

    header('Location: ' . $res['destination']);
    http_response_code(302);
    exit;
}
