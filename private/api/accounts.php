<?php

declare(strict_types=1);

const API_POST_AUTH_USERNAME = 'username';
const API_POST_AUTH_PASSWORD = 'password';

const API_POST_REGISTER_USERNAME = 'username';
const API_POST_REGISTER_PASSWORD = 'password';
const API_POST_REGISTER_PURPOSE = 'purpose';
const API_POST_REGISTER_EULA = 'eula';

function api_account_auth_login(): void
{
    if (session_is_authenticated()) session_deauthenticate();

    $username = post_param_validation(API_POST_AUTH_USERNAME);
    $password = post_param_validation(API_POST_AUTH_PASSWORD);

    if (empty($username) || empty($password)) {
        json_response_error(
            'recevied invalid data',
            400,
            'username or password is empty'
        );
    }

    $res = db_one('SELECT id, username, password from accounts where username=?', [$username]);
    if (!$res) {
        json_response_error('no such login or password found', 403);
    }

    if (!pass_verify($password, $res['password'])) {
        json_response_error('no such login or password found', 403);
    }

    $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_USERID] = $res['id'];
    $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_USERNAME] = $username;
    $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_LOGGEDIN] = true;
    $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_ISANONYM] = false;
    json_response(['status' => 'ok'], 204);
}

function api_account_auth_logout(): void
{
    session_deauthenticate();
    json_response(['status' => 'ok'], 204);
}

function api_account_register(): void
{
    if (session_is_authenticated()) {
        session_deauthenticate();
    }

    $username = post_param_validation(API_POST_REGISTER_USERNAME);
    $password = post_param_validation(API_POST_REGISTER_PASSWORD);
    $purpose = post_param_validation_int(API_POST_REGISTER_PURPOSE, 2); // !! BUG
    $eula = post_param_validation_bool(API_POST_REGISTER_EULA);

    if (empty($username) || empty($password) || empty($purpose) || empty($eula)) {
        json_response_error(
            'recevied invalid data',
            400,
            'username, password, purpose, eula is empty'
        );
    }

    if ($eula !== "true") {
        json_response_error('EULA was not accepted', 403);
    }

    $res = db_one('SELECT username from accounts where username=?', [$username]);
    if ($res) {
        json_response_error(
            'given username has already registered',
            409,
            'username ' . $username . ' has been already registered in the system'
        );
    }

    $res = db_exec(
        'INSERT INTO accounts (username, password, purpose) VALUES (?, ?, ?)',
        [$username, pass_hash($password), $purpose]
    );

    if ($res !== 1) {
        json_response_error('internal database error', 500, 'could not insert new account into database');
    }

    json_response(['status' => 'ok'], 204);
}

function api_account_auth_forgot(): void {}

function api_account_auth_reset(): void {}

function api_account_session_debug(): void
{
    json_response($_SESSION, 200);
}
