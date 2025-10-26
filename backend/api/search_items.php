<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

require_once "../config/db.php";

$q = $_GET['q'] ?? '';
$company_id = $_GET['company_id'] ?? 1; // zatím staticky, později z tokenu

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo->prepare("
    SELECT item_id, name, code, quantity, warehouse_id
    FROM items
    WHERE company_id = ?
      AND (name LIKE ? OR code LIKE ? OR supplier LIKE ?)
    LIMIT 10
");
$like = "%$q%";
$stmt->execute([$company_id, $like, $like, $like]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
