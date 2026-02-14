<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../config/jwt.php';
require_once __DIR__ . '/../../helpers/PasswordHelper.php';

class AuthController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login()
{
    $data = json_decode(file_get_contents("php://input"), true);

    $email = $data['email'] ?? null;
    $phone = $data['phone'] ?? null;
    $password = $data['password'] ?? null;
    $salon_id = $data['salon_id'] ?? null;
    $login_type = $data['login_type'] ?? null; 
    // Expected: SUPER_ADMIN | ADMIN | STAFF | CUSTOMER

    if (!$login_type || !$password) {
        Response::json(["status" => "error", "message" => "Missing credentials"], 400);
    }

    switch ($login_type) {

        /*
        |--------------------------------------------------------------------------
        | SUPER ADMIN LOGIN
        |--------------------------------------------------------------------------
        */
        case "SUPER_ADMIN":

            if (!$email) {
                Response::json(["status" => "error", "message" => "Email required"], 400);
            }

            $stmt = $this->db->prepare("
                SELECT super_admin_id AS user_id, password_hash
                FROM super_admins
                WHERE email = ?
                AND status = 'ACTIVE'
            ");
            $stmt->execute([$email]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$admin || !password_verify($password, $admin['password_hash'])) {
                Response::json(["status" => "error", "message" => "Invalid credentials"], 401);
            }

            $this->generateTokens($admin['user_id'], "SUPER_ADMIN", null);
            break;


        /*
        |--------------------------------------------------------------------------
        | ADMIN / STAFF LOGIN
        |--------------------------------------------------------------------------
        */
        case "ADMIN/STAFF":

            if (!$salon_id || !$email) {
                Response::json(["status" => "error", "message" => "Salon ID and email required"], 400);
            }

            $stmt = $this->db->prepare("
                SELECT user_id, role, password_hash
                FROM users
                WHERE salon_id = ?
                AND email = ?
                AND status = 'ACTIVE'
            ");
            $stmt->execute([$salon_id, $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password_hash'])) {
                Response::json(["status" => "error", "message" => "Invalid credentials"], 401);
            }

            $this->generateTokens($user['user_id'], $user['role'], $salon_id);
            break;


        /*
        |--------------------------------------------------------------------------
        | CUSTOMER LOGIN
        |--------------------------------------------------------------------------
        */
        case "CUSTOMER":

            if (!$salon_id || (!$email && !$phone)) {
                Response::json(["status" => "error", "message" => "Salon ID and email or phone required"], 400);
            }

            if ($email) {
                $stmt = $this->db->prepare("
                    SELECT c.customer_id AS user_id, ca.password_hash
                    FROM customers c
                    JOIN customer_authentication ca 
                        ON c.customer_id = ca.customer_id
                    WHERE c.salon_id = ?
                    AND ca.email = ?
                    AND c.status = 'ACTIVE'
                ");
                $stmt->execute([$salon_id, $email]);
            } else {
                $stmt = $this->db->prepare("
                    SELECT c.customer_id AS user_id, ca.password_hash
                    FROM customers c
                    JOIN customer_authentication ca 
                        ON c.customer_id = ca.customer_id
                    WHERE c.salon_id = ?
                    AND c.phone = ?
                    AND c.status = 'ACTIVE'
                ");
                $stmt->execute([$salon_id, $phone]);
            }

            $customer = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$customer || !password_verify($password, $customer['password_hash'])) {
                Response::json(["status" => "error", "message" => "Invalid credentials"], 401);
            }

            $this->generateTokens($customer['user_id'], "CUSTOMER", $salon_id);
            break;

        default:
            Response::json(["status" => "error", "message" => "Invalid login type"], 400);
    }
}


    /*
    |--------------------------------------------------------------------------
    | TOKEN GENERATION (Single Source of Truth)
    |--------------------------------------------------------------------------
    */

    private function generateTokens($user_id, $role, $salon_id = null, $replacedTokenHash = null)
{
    // Access Token
    $payload = [
        'user_id' => $user_id,
        'role' => $role,
        'salon_id' => $salon_id
    ];

    $accessToken = JWT::generate($payload);

    // Generate Refresh Token
    $refreshToken = bin2hex(random_bytes(64));
    $hashedRefresh = hash('sha256', $refreshToken);

    $expiry = date('Y-m-d H:i:s', strtotime('+7 days'));

    $stmt = $this->db->prepare("
        INSERT INTO refresh_tokens 
        (user_type, user_id, salon_id, token_hash, expires_at, is_revoked, replaced_by)
        VALUES (?, ?, ?, ?, ?, 0, NULL)
    ");

    $stmt->execute([
        $role,
        $user_id,
        $salon_id,
        $hashedRefresh,
        $expiry
    ]);

    // If this was rotation, update old token
    if ($replacedTokenHash) {
        $update = $this->db->prepare("
            UPDATE refresh_tokens
            SET is_revoked = 1,
                replaced_by = ?
            WHERE token_hash = ?
        ");
        $update->execute([$hashedRefresh, $replacedTokenHash]);
    }

    Response::json([
        "status" => "success",
        "data" => [
            "access_token" => $accessToken,
            "refresh_token" => $refreshToken,
            "expires_in" => JWT_EXPIRY_SECONDS
        ]
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | REFRESH
    |--------------------------------------------------------------------------
    */

public function refresh()
{
    $data = json_decode(file_get_contents("php://input"), true);
    $incomingToken = $data['refresh_token'] ?? null;

    if (!$incomingToken) {
        Response::json(["status" => "error", "message" => "Refresh token required"], 400);
    }

    $hashedIncoming = hash('sha256', $incomingToken);

    $stmt = $this->db->prepare("
        SELECT user_id, user_type, salon_id
        FROM refresh_tokens
        WHERE token_hash = ?
        AND is_revoked = 0
        AND expires_at > NOW()
    ");

    $stmt->execute([$hashedIncoming]);
    $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tokenData) {
        Response::json(["status" => "error", "message" => "Invalid or expired token"], 401);
    }

    // Rotate token properly with correct salon_id
    $this->generateTokens(
        $tokenData['user_id'],
        $tokenData['user_type'],
        $tokenData['salon_id'],  // ✅ restored
        $hashedIncoming
    );
}



    /*
    |--------------------------------------------------------------------------
    | LOGOUT
    |--------------------------------------------------------------------------
    */

    public function logout()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $incomingToken = $data['refresh_token'] ?? null;

        if (!$incomingToken) {
            Response::json(["status" => "error", "message" => "Refresh token required"], 400);
        }

        $hashedIncoming = hash('sha256', $incomingToken);

       $stmt = $this->db->prepare("
            UPDATE refresh_tokens
            SET is_revoked = 1
            WHERE token_hash = ?
        ");
        $stmt->execute([$hashedIncoming]);

        Response::json([
            "status" => "success",
            "message" => "Logged out successfully"
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | ME
    |--------------------------------------------------------------------------
    */

    public function me()
    {
        $token = Request::getBearerToken();

        if (!$token) {
            Response::json(['message' => 'Access token required'], 401);
        }

        $payload = JWT::verify($token);

        if (!$payload) {
            Response::json(['message' => 'Invalid or expired token'], 401);
        }

        Response::json([
            'status' => 'success',
            'data' => $payload
        ]);
    }
}
