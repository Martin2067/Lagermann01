<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

require_once __DIR__ . '/../config/db.php';


$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['company_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM warehouses WHERE company_id = ?");
            $stmt->execute([$_GET['company_id']]);
            echo json_encode($stmt->fetchAll());
        } else {
            echo json_encode(["error" => "Missing company_id"]);
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['company_id'], $data['name'])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO warehouses (company_id, name, location, size_info, responsible_person) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['company_id'],
            $data['name'],
            $data['location'] ?? null,
            $data['size_info'] ?? null,
            $data['responsible_person'] ?? null
        ]);
        echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
        break;

    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['warehouse_id'])) {
            echo json_encode(["error" => "Missing warehouse_id"]);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE warehouses SET name=?, location=?, size_info=?, responsible_person=? WHERE warehouse_id=?");
        $stmt->execute([
            $data['name'] ?? null,
            $data['location'] ?? null,
            $data['size_info'] ?? null,
            $data['responsible_person'] ?? null,
            $data['warehouse_id']
        ]);
        echo json_encode(["success" => true]);
        break;

    case "DELETE":
        parse_str(file_get_contents("php://input"), $data);
        if (!isset($data['warehouse_id'])) {
            echo json_encode(["error" => "Missing warehouse_id"]);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM warehouses WHERE warehouse_id=?");
        $stmt->execute([$data['warehouse_id']]);
        echo json_encode(["success" => true]);
        break;

    default:
        echo json_encode(["error" => "Invalid method"]);
}
