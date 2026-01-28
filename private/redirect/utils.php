<?php

declare(strict_types=1);

const linkIdVocabular = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

function param_validation(string $payload, ?int $maxlen = 0): ?string
{
    $payload = trim($payload);
    if (empty($payload)) return null;
    if (strlen($payload) === 0) return null;
    if ($maxlen !== 0 && strlen($payload) > $maxlen) return null;

    return $payload;
}
