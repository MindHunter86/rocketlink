<?php

declare(strict_types=1);

function api_get_order(array $params = []): void
{
    $id = param_validation($params['id'] ?? '');
    if ($id === null) json_response_error('invalid data recevied', 400, 'input data validation was not passed');
    if (empty($id)) json_response_error('invalid data recevied', 400, 'recevied id is not valid');

    if (!session_is_authenticated()) json_response_error('authentication required for looking for order', 403);

    $res = db_one('SELECT owner WHERE id=?', [$id]);
    if (!$res) json_response_error('order not found', 404, 'recevied id could not be found in db');

    if (!session_is_authorized_by_id($res['owner']))
        json_response_error('order not found', 404, 'insufficient permissions for looking for order');

    json_response([
        'status' => true,
        'data' => [
            'id' => $id,
            'owner' => $res['owner'] === 'NULL' ? 'anonymous' : session_get_account_username() ?? 'undefined',
        ]
    ], 200);
}
