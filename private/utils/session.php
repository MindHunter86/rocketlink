<?php

declare(strict_types=1);

const SESSION_ACCOUNT = 'account';
const SESSION_ACCOUNT_USERID = 'userid';
const SESSION_ACCOUNT_USERNAME = 'username';
const SESSION_ACCOUNT_LOGGEDIN = 'is_logged_in';
const SESSION_ACCOUNT_SITEROLE = 'siterole';

const ROLES_ROLE_USER = 'user';
const ROLES_ROLE_ADMIN = 'admin';

function session_is_exists_accounts(): bool
{
    return empty($_SESSION[SESSION_ACCOUNT]) === false;
}


function session_is_identified(): bool
{
    if (!session_is_exists_accounts()) {
        return false;
    }

    if (!empty($_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_USERNAME])) {
        return true;
    }

    return false;
}

function session_is_authenticated(): bool
{

    if (!session_is_exists_accounts()) {
        return false;
    }

    if (empty($_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_LOGGEDIN])) {
        return false;
    }

    return $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_LOGGEDIN] === true;
}

function session_is_authorized_by_role(string $role): bool
{
    if (!session_is_exists_accounts()) {
        return false;
    }

    if (empty($_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_SITEROLE])) {
        return false;
    }

    return $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_SITEROLE] === $role;
}

function session_is_authorized_by_id(string $id): bool
{
    if (!session_is_exists_accounts()) {
        return false;
    }

    if (empty($_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_USERID])) {
        return false;
    }

    return $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_USERID] === $id;
}

function session_deauthenticate(): void
{
    $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_LOGGEDIN] = false;
}

function session_get_account_userid(): ?int
{
    if (!session_is_exists_accounts()) {
        return null;
    }

    if (!session_is_authenticated()) {
        return null;
    }

    return $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_USERID];
}

function session_get_account_username(): ?string
{
    if (!session_is_exists_accounts()) {
        return null;
    }

    if (!session_is_identified()) {
        return null;
    }

    if (!session_is_authenticated()) {
        return null;
    }

    return $_SESSION[SESSION_ACCOUNT][SESSION_ACCOUNT_USERNAME];
}
