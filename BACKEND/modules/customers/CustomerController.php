<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../core/Request.php';
require_once __DIR__ . '/../../core/Response.php';
require_once __DIR__ . '/../../config/jwt.php';
require_once __DIR__ . '/../../helpers/PasswordHelper.php';


class CustomerController
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        $name       = trim($data['name'] ?? '');
        $phone      = trim($data['phone'] ?? '');
        $email      = trim($data['email'] ?? '');
        $password   = trim($data['password'] ?? '');
        $salon_id   = $data['salon_id'] ?? null;

        // 1️⃣ Basic Validation
        if (!$name || !$password || !$salon_id) {
            Response::json(["status" => "error", "message" => "Missing required fields"], 400);
            return;
        }

        if (!$phone && !$email) {
            Response::json([
                "status" => "error",
                "message" => "Either phone or email is required"
            ], 400);
            return;
        }

        // 2️⃣ Check Salon Exists
        $stmt = $this->db->prepare("SELECT salon_id FROM salons WHERE salon_id = ?");
        $stmt->execute([$salon_id]);

        if (!$stmt->fetch()) {
            Response::json(["status" => "error", "message" => "Invalid salon"], 400);
            return;
        }

        // 3️⃣ Check Phone Uniqueness
        if ($phone) {
            $stmt = $this->db->prepare("
                SELECT customer_id FROM customers 
                WHERE salon_id = ? AND phone = ?
            ");
            $stmt->execute([$salon_id, $phone]);

            if ($stmt->fetch()) {
                Response::json(["status" => "error", "message" => "Phone already registered"], 409);
                return;
            }
        }

        // 4️⃣ Check Email Uniqueness
        if ($email) {
            $stmt = $this->db->prepare("
                SELECT customer_id FROM customers 
                WHERE salon_id = ? AND email = ?
            ");
            $stmt->execute([$salon_id, $email]);

            if ($stmt->fetch()) {
                Response::json(["status" => "error", "message" => "Email already registered"], 409);
                return;
            }
        }

        try {
            $this->db->beginTransaction();

            // 5️⃣ Insert into customers
            $stmt = $this->db->prepare("
                INSERT INTO customers 
                (salon_id, name, phone, email, customer_since, created_at, updated_at)
                VALUES (?, ?, ?, ?, CURDATE(), NOW(), NOW())
            ");

            $stmt->execute([
                $salon_id,
                $name,
                $phone ?: null,
                $email ?: null
            ]);

            $customer_id = $this->db->lastInsertId();

            // 6️⃣ Insert into authentication table
            $password_hash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $this->db->prepare("
                INSERT INTO customer_authentication
                (customer_id, salon_id, email, password_hash, status, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'ACTIVE', NOW(), NOW())
            ");

            $stmt->execute([
                $customer_id,
                $salon_id,
                $email ?: null,
                $password_hash
            ]);

            $this->db->commit();

            Response::json([
                "status" => "success",
                "message" => "Customer registered successfully"
            ], 201);

        // } catch (Exception $e) {

        //     $this->db->rollBack();

        //     Response::json([
        //         "status" => "error",
        //         "message" => "Registration failed"
        //     ], 500);
        // }

        } catch (Exception $e) {

    $this->db->rollBack();

    Response::json([
        "status" => "error",
        "message" => $e->getMessage()
    ], 500);
}
    }
}
