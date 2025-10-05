<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { exit; }

require_once __DIR__ . '/../config/db.php';

$item_id = $_GET['item_id'] ?? null;
$company_id = $_GET['company_id'] ?? null;

if (!$item_id || !$company_id) {
  http_response_code(400);
  echo json_encode(["error" => "Missing item_id or company_id"]);
  exit;
}

// Získáme základní info o položce
$stmt = $pdo->prepare("
  SELECT i.*, w.name AS warehouse_name
  FROM items i
  JOIN warehouses w ON i.warehouse_id = w.warehouse_id
  WHERE i.item_id = ? AND i.company_id = ?
");
$stmt->execute([$item_id, $company_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

// Historie pohybů (např. tabulka `item_movements`)
$stmtHist = $pdo->prepare("
  SELECT *
  FROM item_movements
  WHERE item_id = ?
  ORDER BY moved_at DESC
");
$stmtHist->execute([$item_id]);
$history = $stmtHist->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
  "item" => $item,
  "history" => $history
]);
