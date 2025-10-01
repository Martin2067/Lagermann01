<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

require_once "../config/db.php"; // připojení k DB
require_once "../utils/jwt.php"; // funkce pro JWT (vytvoříme později)

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['company_name'], $data['name'], $data['email'], $data['password'])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$company_name = $data['company_name'];
$name = $data['name'];
$email = $data['email'];
$password = password_hash($data['password'], PASSWORD_BCRYPT);

try {
    $pdo->beginTransaction();

    // Vložíme firmu
    $stmt = $pdo->prepare("INSERT INTO companies (name) VALUES (?)");
    $stmt->execute([$company_name]);
    $company_id = $pdo->lastInsertId();

    // Vložíme admin uživatele
    $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'admin')");
    $stmt->execute([$company_id, $name, $email, $password]);

    $pdo->commit();

    echo json_encode(["success" => true, "message" => "Company and admin registered"]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(["error" => $e->getMessage()]);
}
