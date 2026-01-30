<?php

declare(strict_types=1);

include_once(__DIR__ . '/../utils/user_agent.php');

function analytics_trigger_event(int $linkid): void
{
    // User-Agent header processing
    $ua = parse_user_agent(($_SERVER['HTTP_USER_AGENT'] ?? ""));

    // stop further processing on error or bot detection
    // TODO - add bot provider detection and write to db (google, yahoo, amazon, etc.)
    if (empty($ua) || $ua['is_bot']) return;

    // Sec-CH-UA header processing
    // TODO: use - CONFIG_LINK_SECCH_HEADERS_ENABLE
    $ch_ismobile = $_SERVER['Sec-CH-UA-Mobile'] ?? PDO::PARAM_NULL;
    $is_mobile = $ch_ismobile === "?1" ? "1" : "0";

    $ch_arch = $_SERVER['Sec-CH-UA-Mobile'] ?? PDO::PARAM_NULL;

    $ch_model = $_SERVER['Sec-CH-UA-Model'] ?? PDO::PARAM_NULL;

    $ch_platform = $_SERVER['Sec-CH-UA-Platform'] ?? PDO::PARAM_NULL;

    $ch_platformver = PDO::PARAM_NULL;
    if ($ch_platform !== "Unknown") $ch_platformver = $_SERVER['Sec-CH-UA-Platform-Version'] ?? PDO::PARAM_NULL;

    $ch_viewport_w = $_SERVER['Sec-CH-UA-Viewport-Width'] ?? PDO::PARAM_NULL;
    $ch_viewport_h = $_SERVER['Sec-CH-UA-Viewport-Height'] ?? PDO::PARAM_NULL;

    // IP processing
    $ip = $_SERVER['REMOTE_ADDR'] ?? PDO::PARAM_NULL;

    // TODO : GEOIP processing

    // Referer and origin processing
    $http_referer = $_SERVER['REFERER'] ?? PDO::PARAM_NULL;
    $http_origin = $_SERVER['ORIGIN'] ?? PDO::PARAM_NULL;

    // TODO catch analytics trigger error
    db_exec('INSERT INTO analytics (
        `linkid`,
        `ip`,
        `http_referer`,
        `http_origin`,
        `chua_ismobile`,
        `chua_arch`,
        `chua_model`,
        `chua_platform`,
        `chua_platformver`,
        `chua_viewport_w`,
        `chua_viewport_h`,
        `ua_platform`,
        `ua_arch`,
        `ua_os`,
        `ua_osver`,
        `ua_browser`,
        `ua_browserver`
    ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )', [
        $linkid,
        $ip,
        $http_referer,
        $http_origin,
        $is_mobile,
        $ch_arch,
        $ch_model,
        $ch_platform,
        $ch_platformver,
        $ch_viewport_w,
        $ch_viewport_h,
        $ua['platform'] ?? PDO::PARAM_NULL,
        $ua['arch'] ?? PDO::PARAM_NULL,
        $ua['os'] ?? PDO::PARAM_NULL,
        $ua['os_version'] ?? PDO::PARAM_NULL,
        $ua['browser'] ?? PDO::PARAM_NULL,
        $ua['browser_version'] ?? PDO::PARAM_NULL,
    ]);
}
