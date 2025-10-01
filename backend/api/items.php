<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "GET":
        if (isset($_GET['warehouse_id'])) {
            $stmt = $pdo->prepare("SELECT * FROM items WHERE warehouse_id = ?");
            $stmt->execute([$_GET['warehouse_id']]);
            echo json_encode($stmt->fetchAll());
        } else {
            echo json_encode(["error" => "Missing warehouse_id"]);
        }
        break;

    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['warehouse_id'], $data['company_id'], $data['name'])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO items (warehouse_id, company_id, name, code, supplier, quantity, location_in_warehouse) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['warehouse_id'],
            $data['company_id'],
            $data['name'],
            $data['code'] ?? null,
            $data['supplier'] ?? null,
            $data['quantity'] ?? 0,
            $data['location_in_warehouse'] ?? null
        ]);
        echo json_encode(["success" => true, "id" => $pdo->lastInsertId()]);
        break;

    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['item_id'])) {
            echo json_encode(["error" => "Missing item_id"]);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE items SET name=?, code=?, supplier=?, quantity=?, location_in_warehouse=? WHERE item_id=?");
        $stmt->execute([
            $data['name'] ?? null,
            $data['code'] ?? null,
            $data['supplier'] ?? null,
            $data['quantity'] ?? 0,
            $data['location_in_warehouse'] ?? null,
            $data['item_id']
        ]);
        echo json_encode(["success" => true]);
        break;

    case "DELETE":
        parse_str(file_get_contents("php://input"), $data);
        if (!isset($data['item_id'])) {
            echo json_encode(["error" => "Missing item_id"]);
            exit;
        }
        $stmt = $pdo->prepare("DELETE FROM items WHERE item_id=?");
        $stmt->execute([$data['item_id']]);
        echo json_encode(["success" => true]);
        break;

    default:
        echo json_encode(["error" => "Invalid method"]);
}
