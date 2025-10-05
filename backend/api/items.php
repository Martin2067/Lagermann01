<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");

require_once "../config/db.php";

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    // =======================
    // GET (výpis zboží)
    // =======================
    case "GET":
        if (isset($_GET['warehouse_id'])) {
            // Výpis zboží z konkrétního skladu
            $stmt = $pdo->prepare("SELECT i.*, w.name as warehouse_name 
                                   FROM items i 
                                   JOIN warehouses w ON i.warehouse_id = w.warehouse_id
                                   WHERE i.warehouse_id = ?");
            $stmt->execute([$_GET['warehouse_id']]);
            echo json_encode($stmt->fetchAll());
        } elseif (isset($_GET['company_id'])) {
            // Výpis všeho zboží firmy
            $stmt = $pdo->prepare("SELECT i.*, w.name as warehouse_name 
                                   FROM items i 
                                   JOIN warehouses w ON i.warehouse_id = w.warehouse_id
                                   WHERE i.company_id = ?");
            $stmt->execute([$_GET['company_id']]);
            echo json_encode($stmt->fetchAll());
        } else {
            echo json_encode(["error" => "Missing warehouse_id or company_id"]);
        }
        break;

    // =======================
    // POST (nová položka)
    // =======================
    case "POST":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['warehouse_id'], $data['company_id'], $data['name'])) {
            echo json_encode(["error" => "Missing required fields"]);
            exit;
        }
        $stmt = $pdo->prepare("INSERT INTO items 
            (warehouse_id, company_id, name, code, supplier, quantity, location_in_warehouse) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
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

    // =======================
    // PUT (úprava položky)
    // =======================
    case "PUT":
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['item_id'])) {
            echo json_encode(["error" => "Missing item_id"]);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE items 
                               SET name=?, code=?, supplier=?, quantity=?, location_in_warehouse=? 
                               WHERE item_id=?");
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

    // =======================
    // DELETE (smazání položky)
    // =======================
    case "DELETE":
        $data = json_decode(file_get_contents("php://input"), true);
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
