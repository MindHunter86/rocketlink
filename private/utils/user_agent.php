<?php

declare(strict_types=1);

// !! IMPORTANT NOTICE !! //
/*
 * NOTICE
 * This file contains code initially generated with the assistance of AI tooling (this file only not the project as a whole).
 * The resulting implementation has been reviewed, tested, and, where necessary,
 * corrected by the project maintainer prior to publication.
 */

function parse_user_agent(string $payload): array
{
    if ($payload === "") return [];

    // case insesitive shiyou
    $ua = strtolower($payload);

    // bot detection, cheap version and regexp
    $maybe_bot =
        (strpos($ua, 'bot') !== false) || (strpos($ua, 'spider') !== false) || (strpos($ua, 'crawl') !== false) ||
        (strpos($ua, 'slurp') !== false) || (strpos($ua, 'preview') !== false) ||
        (strpos($ua, 'httpclient') !== false) || (strpos($ua, 'python-requests') !== false) ||
        (strpos($ua, 'okhttp') !== false) || (strpos($ua, 'curl') !== false) ||
        (strpos($ua, 'wget') !== false) || (strpos($ua, 'scrapy') !== false);

    if ($maybe_bot) {
        static $botregexp = '~\b(googlebot|bingbot|yandexbot|duckduckbot|baiduspider|slurp|facebookexternalhit|twitterbot|telegrambot|whatsapp|discordbot|crawler|spider|crawl|preview|httpclient|python-requests|okhttp|curl|wget|scrapy|bot)\b~';
        if (preg_match($botregexp, $ua) === 1) {
            return [
                'is_bot' => true,
            ];
        }
    }

    // arch parse
    $arch = null;
    if (strpos($ua, 'aarch64') !== false || strpos($ua, 'arm64') !== false || strpos($ua, 'armv8') !== false) {
        $arch = 'arm64';
    } elseif (strpos($ua, 'armv7') !== false || strpos($ua, 'armv6') !== false || strpos($ua, 'arm;') !== false) {
        $arch = 'arm';
    } elseif (
        strpos($ua, 'x86_64') !== false || strpos($ua, 'amd64') !== false || strpos($ua, 'win64') !== false
        || strpos($ua, 'wow64') !== false || strpos($ua, 'x64') !== false
    ) {
        $arch = 'x86_64';
    } elseif (strpos($ua, 'i686') !== false || strpos($ua, 'i386') !== false || strpos($ua, 'x86') !== false) {
        $arch = 'x86';
    } elseif (strpos($ua, 'ppc64') !== false || strpos($ua, 'powerpc64') !== false) {
        $arch = 'ppc64';
    } elseif (strpos($ua, 'ppc') !== false || strpos($ua, 'powerpc') !== false) {
        $arch = 'ppc';
    } elseif (strpos($ua, 'mips64') !== false) {
        $arch = 'mips64';
    } elseif (strpos($ua, 'mips') !== false) {
        $arch = 'mips';
    } elseif (strpos($ua, 'riscv64') !== false) {
        $arch = 'riscv64';
    }

    // platform (tablet/mobile/desktop)
    if (strpos($ua, 'ipad') !== false || strpos($ua, 'tablet') !== false) $platform = 'tablet';
    elseif (strpos($ua, 'mobi') !== false || strpos($ua, 'iphone') !== false || strpos($ua, 'android') !== false) $platform = 'mobile';
    else $platform = 'desktop';

    // os + version
    $os = 'Unknown';
    $osv = null;

    if (strpos($ua, 'windows nt') !== false && preg_match('/windows nt ([0-9.]+)/i', $ua, $m)) {
        $os = 'Windows';
        $osv = $m[1];
    } elseif (strpos($ua, 'android') !== false && preg_match('/android ([0-9.]+)/i', $ua, $m)) {
        $os = 'Android';
        $osv = $m[1];
    } elseif ((strpos($ua, 'iphone os') !== false || strpos($ua, 'cpu iphone os') !== false || strpos($ua, 'cpu os') !== false)
        && preg_match('/(?:iphone os|cpu (?:iphone )?os) ([0-9_]+)/i', $ua, $m)
    ) {
        $os = 'iOS';
        $osv = strtr($m[1], ['_' => '.']);
    } elseif (strpos($ua, 'ipad') !== false && preg_match('/ipad; cpu os ([0-9_]+)/i', $ua, $m)) {
        $os = 'iPadOS';
        $osv = strtr($m[1], ['_' => '.']);
    } elseif (strpos($ua, 'mac os x') !== false && preg_match('/mac os x ([0-9_]+)/i', $ua, $m)) {
        $os = 'macOS';
        $osv = strtr($m[1], ['_' => '.']);
    } elseif (strpos($ua, 'linux') !== false) {
        $os = 'Linux';
    }

    // browser + version (if elsif prioritized)
    $browser = 'Unknown';
    $bv = null;

    if (strpos($ua, 'edg') !== false && preg_match('/edg(e|a|ios)?\/([0-9.]+)/i', $ua, $m)) {
        $browser = 'Edge';
        $bv = $m[2] ?? null;
    } elseif ((strpos($ua, 'opr/') !== false || strpos($ua, 'opera') !== false) && preg_match('/(?:opr|opera)\/([0-9.]+)/i', $ua, $m)) {
        $browser = 'Opera';
        $bv = $m[1];
    } elseif (strpos($ua, 'samsungbrowser') !== false && preg_match('/samsungbrowser\/([0-9.]+)/i', $ua, $m)) {
        $browser = 'Samsung';
        $bv = $m[1];
    } elseif ((strpos($ua, 'crios/') !== false || strpos($ua, 'chrome/') !== false) && preg_match('/(?:crios|chrome)\/([0-9.]+)/i', $ua, $m)) {
        $browser = 'Chrome';
        $bv = $m[1];
    } elseif ((strpos($ua, 'fxios/') !== false || strpos($ua, 'firefox/') !== false) && preg_match('/(?:fxios|firefox)\/([0-9.]+)/i', $ua, $m)) {
        $browser = 'Firefox';
        $bv = $m[1];
    } elseif (strpos($ua, 'safari') !== false && preg_match('/version\/([0-9.]+).*safari\/[0-9.]+/i', $ua, $m)) {
        $browser = 'Safari';
        $bv = $m[1];
    } elseif (strpos($ua, 'msie') !== false && preg_match('/msie ([0-9.]+)/i', $ua, $m)) {
        $browser = 'IE';
        $bv = $m[1];
    } elseif (strpos($ua, 'trident') !== false && preg_match('/trident\/.*rv:([0-9.]+)/i', $ua, $m)) {
        $browser = 'IE';
        $bv = $m[1];
    }

    return [
        'ua' => $payload,
        'is_bot' => false,
        'platform' => $platform,
        'os' => $os,
        'os_version' => $osv,
        'arch' => $arch,
        'browser' => $browser,
        'browser_version' => $bv,
    ];
}
