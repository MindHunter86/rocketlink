<?php

declare(strict_types=1);

const linkIdVocabular = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

function post_param_validation(string $key, ?int $maxlen = 0, ?bool $isempty = false): string
{
    if (empty($_POST[$key])) {
        return "";
    };

    $payload = trim($_POST[$key]);
    if (strlen($payload) == 0 && !$isempty) {
        return "";
    }

    if ($maxlen != 0 && !post_param_validation_len($payload, $maxlen)) {
        return "";
    }

    return $payload;
}

// TODO:
// !! BUG WITH RANGE VALIDATION
function post_param_validation_int(string $key, ?int $max = 0, ?int $min = 0, ?bool $isempty = false): string
{
    if (empty($_POST[$key])) {
        return "";
    };

    $payload = trim($_POST[$key]);
    if (strlen($payload) == 0 && !$isempty) {
        return "";
    }

    if (!is_numeric($payload) || !ctype_digit($payload)) {
        return "";
    }

    if ($max !== 0 || $min !== 0) {
        $ipayload = intval($payload);
        if ($ipayload < $min || $ipayload > $max) {
            return "";
        }

        return $payload;
    }

    return $payload;
}

function post_param_validation_bool(string $key): string
{
    if (empty($_POST[$key])) {
        return "";
    };

    $payload = trim($_POST[$key]);
    if ($payload === "true" || $payload === "false") {
        return $payload;
    }

    return "";
}

function post_param_validation_len(string $payload, int $to, ?int $from = 0): bool
{
    if (strlen($payload) > $to || strlen($payload) < $from) {
        return false;
    }

    return true;
}
