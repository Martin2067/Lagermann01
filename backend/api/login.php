<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Content-Type: application/json");

require_once "../config/db.php";
require_once "../utils/jwt.php";

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['email'], $data['password'])) {
    echo json_encode(["error" => "Missing credentials"]);
    exit;
}

$email = $data['email'];
$password = $data['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user['password'])) {
    $token = create_jwt([
        "user_id" => $user['user_id'],
        "company_id" => $user['company_id'],
        "role" => $user['role']
    ]);

    echo json_encode(["success" => true, "token" => $token]);
} else {
    echo json_encode(["error" => "Invalid email or password"]);
}
