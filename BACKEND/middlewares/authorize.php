<?php

require_once __DIR__ . '/authenticate.php';
require_once __DIR__ . '/../core/Response.php';

/*
|--------------------------------------------------------------------------
| AUTHORIZE MIDDLEWARE
|--------------------------------------------------------------------------
| Usage:
| authorize(['ADMIN'])
| authorize(['ADMIN','STAFF'])
*/

function authorize(array $allowedRoles)
{
    authenticate();

    $user = $GLOBALS['auth_user'] ?? null;

    if (!$user || !in_array($user['role'], $allowedRoles)) {
        Response::json([
            "status" => "error",
            "message" => "Forbidden"
        ], 403);
    }
}
