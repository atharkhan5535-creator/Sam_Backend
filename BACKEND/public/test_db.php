<?php

require_once '../config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    echo "Database Connected Successfully";
} catch (Exception $e) {
    echo "Connection Failed: " . $e->getMessage();
}
