<?php

declare(strict_types=1);

require_once(__DIR__ . '../../../config/shorten.php');

const API_LINK_DESTINATION = 'destination';

function api_get_link(array $params = []): void
{
    $id = param_validation($params['id'] ?? '');
    if ($id === null) json_response_error('invalid data recevied', 400, 'input data validation was not passed');
    if (empty($id)) json_response_error('invalid data recevied', 400, 'recevied id is not valid');

    $res = db_one('SELECT id,owner FROM links where shortenid=?', [$id]);
    if (!$res) json_response_error('requested link not found', 404);

    if (!session_is_authenticated() || !session_is_authorized_by_id($res['owner']))
        json_response_error('non-authorized access to not owned link', 403);

    json_response(['status' => true, 'data' => $res], 200);
}

// TODO:
function api_update_link(): void {}

// TODO:
// !! fix mysql NULL assignment
// !! save link in session of anonnymous user
function api_post_link(array $params = []): void
{
    $destination = post_param_validation(API_LINK_DESTINATION);
    if (empty($destination)) json_response_error('invalid data recevied', 400);

    // link generation
    $id = "";
    for ($attmp = 0; $attmp < 3; $attmp++) {
        $id = link_random_id_generate(CONFIG_LINK_LENGTH);

        $res = db_one("SELECT id FROM links WHERE shortenid = ?", [$id]);
        if ($res) continue;

        break;
    }

    // secrets for anonymous generation
    $secret = "";
    $secret = link_random_id_generate(CONFIG_LINK_SECRET_LENGTH);

    $owner = session_get_account_userid() ?? PDO::PARAM_NULL;

    // inserting new link
    try {
        $res = db_exec(
            "INSERT INTO links (shortenid, destination, secret, owner) VALUES (?, ?, ?, ?)",
            [$id, $destination, $secret, $owner]
        );

        json_response([
            'status' => true,
            'data' => [
                'id' => pdo()->lastInsertId(),
                'shorten' => 'trm.sh/' . $id,
                'destination' => $destination,
                'secret' => $secret,
                'owner' => $owner === 'NULL' ? 'anonymous' : session_get_account_username() ?? 'undefined',
            ]
        ], 201);
    } catch (Exception $e) {
        json_response_error('could not create new link', 500, 'database error - ' . $e);
    }
}

function link_random_id_generate(int $chars): string
{
    $id = "";
    $voclen = strlen(linkIdVocabular);
    for ($i = 0; $i < $chars; $i++) {
        $id .= linkIdVocabular[random_int(0, $voclen - 1)];
    }

    return $id;
}
