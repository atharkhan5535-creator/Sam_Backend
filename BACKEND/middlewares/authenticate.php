<?php

require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../config/JWT.php';

/*
|--------------------------------------------------------------------------
| AUTHENTICATE MIDDLEWARE
|--------------------------------------------------------------------------
| - Validates access token
| - Verifies JWT signature & expiry
| - Stores authenticated user globally
| - Ensures required payload structure
*/

function authenticate()
{
    $token = Request::getBearerToken();

    if (!$token) {
        Response::json([
            "status" => "error",
            "message" => "Access token required"
        ], 401);
    }

    $payload = JWT::verify($token);

    if (!$payload) {
        Response::json([
            "status" => "error",
            "message" => "Invalid or expired token"
        ], 401);
    }

    // Validate essential fields
    if (
        !isset($payload['user_id']) ||
        !isset($payload['role'])
    ) {
        Response::json([
            "status" => "error",
            "message" => "Malformed token payload"
        ], 401);
    }

    // Store authenticated user globally
    $GLOBALS['auth_user'] = $payload;
}
