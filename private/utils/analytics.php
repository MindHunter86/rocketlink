<?php

declare(strict_types=1);

function analytics_trigger_event(int $linkid): void
{
    $a_useragent = $_SERVER['HTTP_USER_AGENT'] ?? "";
    $a_useragent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:135.0) Gecko/20100101 Firefox/135.0";
    print_r(get_browser($a_useragent));

    $res = db_exec('INSERT INTO analytics (
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
        `ua_codename`,
        `ua_version`,
        `ua_build`,
        `ua_os`,
        `ua_product`,
        `ua_platform`,
        `ua_browser`,
        `ua_browserver`,
    ) VALUES (

    )');
}
