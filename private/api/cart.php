<?php

declare(strict_types=1);

require_once(__DIR__ . '/../utils/cart.php');

const API_CART_PRODUCT_ID = "product_id";
const API_CART_PRODUCT_CNT = "product_cnt";

function api_cart(array $params = []): void
{
    json_response(['status' => true, 'data' => cart_list_products()], 200);
}

function api_cart_delete(array $params = []): void
{
    $id = param_validation($params['id'] ?? '');
    if ($id === null) json_response_error('invalid data recevied', 400, 'input data validation was not passed');
    if (empty($id)) json_response_error('invalid data recevied', 400, 'recevied id is not valid');

    cart_remove_product($id);
    json_response(['status' => true]);
}

function api_cart_flush(array $params = []): void
{
    cart_destroy();
    json_response(['status' => true]);
}

function api_cart_post(array $params = []): void
{
    $product_id = post_param_validation_int(API_CART_PRODUCT_ID);
    $product_cnt = post_param_validation_int(API_CART_PRODUCT_CNT);
    if (empty($product_id) || empty($product_cnt)) {
        json_response_error('invalid data recevied', 400);
    }

    $res = db_one("SELECT name,price FROM products WHERE id=?", [$product_id]);
    if (!$res) json_response_error('requested product not found', 404);

    $product = [
        'id' => $product_id,
        'name' => $res['name'],
        'price' => $res['price'],
        'count' => $product_cnt,
    ];

    cart_add_product($product);
    json_response(['status' => true], 200);
}
