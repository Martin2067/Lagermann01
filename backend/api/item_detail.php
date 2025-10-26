<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "../config/db.php";

$item_id = $_GET['item_id'] ?? $_GET['id'] ?? null;
$company_id = $_GET['company_id'] ?? 1;

if (!$item_id) {
    echo json_encode(["error" => "Missing item_id or company_id"]);
    exit;
}

try {
    // 🔹 DETAIL POLOŽKY
    $stmt = $pdo->prepare("
        SELECT i.*, w.name AS warehouse_name, w.location AS warehouse_location
        FROM items i
        JOIN warehouses w ON i.warehouse_id = w.warehouse_id
        WHERE i.item_id = ? AND i.company_id = ?
        LIMIT 1
    ");
    $stmt->execute([$item_id, $company_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        echo json_encode(["error" => "Item not found"]);
        exit;
    }

    // 🔹 HISTORIE POHYBŮ
    $stmt2 = $pdo->prepare("
    SELECT 
        m.movement_id,
        m.action,
        m.quantity,
        fw.name AS from_location,
        tw.name AS to_location,
        m.movement_date AS moved_at,
        m.user_name
    FROM item_movements m
    LEFT JOIN warehouses fw ON m.from_warehouse = fw.warehouse_id
    LEFT JOIN warehouses tw ON m.to_warehouse = tw.warehouse_id
    WHERE m.item_id = ?
    ORDER BY m.movement_date DESC
");

    $stmt2->execute([$item_id]);
    $history = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "item" => $item,
        "history" => $history
    ]);

} catch (Exception $e) {
    echo json_encode([
        "error" => "Database error",
        "details" => $e->getMessage()
    ]);
}
