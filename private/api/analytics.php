<?php

declare(strict_types=1);
require_once(__DIR__ . '/../utils/analytics.php');

function api_analytics(array $params = []): void
{
    analytics_trigger_event(1);
}

function api_analytics_new_event(array $params = []): void {}

function api_analytics_get_events(array $params = []): void {}

function api_analytics_get_last1h(array $params = []): void {}
function api_analytics_get_last3h(array $params = []): void {}
function api_analytics_get_last12h(array $params = []): void {}
function api_analytics_get_last24h(array $params = []): void {}
function api_analytics_get_last3d(array $params = []): void {}
function api_analytics_get_last7d(array $params = []): void {}
