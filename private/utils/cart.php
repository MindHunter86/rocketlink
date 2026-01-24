<?php

declare(strict_types=1);

const SESSION_CART = 'cart';
const SESSION_CART_INIT = 'initied';
const SESSION_CART_PRODUCTS = 'products';

function cart_is_exists(): bool
{
    if (empty($_SESSION) || !isset($_SESSION[SESSION_CART])) return false;
    if (!$_SESSION[SESSION_CART][SESSION_CART_INIT]) return false;
    return true;
}

function cart_is_empty(): bool
{
    return empty($_SESSION[SESSION_CART][SESSION_CART_PRODUCTS]);
}

function cart_destroy(): void
{
    unset($_SESSION[SESSION_CART]);
}

function cart_initialize(): void
{
    if (cart_is_exists()) cart_destroy();
    $_SESSION[SESSION_CART][SESSION_CART_INIT] = true;
}

function cart_add_product(mixed $product): bool
{
    if (!cart_is_exists()) cart_initialize();
    if (!isset($_SESSION[SESSION_CART][SESSION_CART_PRODUCTS][$product['id']]))
        return array_push(
            $_SESSION[SESSION_CART][SESSION_CART_PRODUCTS] ?? [],
            [$product['id'] => $product]
        ) === 1;

    $_SESSION[SESSION_CART][SESSION_CART_PRODUCTS][$product['id']]['count'] += $product['count'];
    return true;
}

function cart_remove_product(string $id): void
{
    if (!cart_is_exists()) cart_initialize();
    unset($_SESSION[SESSION_CART][SESSION_CART_PRODUCTS][$id]);
}

function cart_list_products(): array
{
    if (!cart_is_exists()) cart_initialize();
    if (cart_is_empty()) return [];

    $products = [];
    foreach ($_SESSION[SESSION_CART][SESSION_CART_PRODUCTS] as $id => $prod) {
        if (!isset($prod) || empty($prod)) continue;
        array_push($products, [$id => $prod]);
    }

    return $products;
}
