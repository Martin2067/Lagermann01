<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once "../config/db.php";

// Zpracování JSON dat
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['item_id'], $data['quantity'], $data['target_warehouse'])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$item_id = intval($data['item_id']);
$move_qty = intval($data['quantity']);
$target_warehouse = intval($data['target_warehouse']);

// 1️⃣ Načteme původní zboží
$stmt = $pdo->prepare("SELECT * FROM items WHERE item_id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo json_encode(["error" => "Item not found"]);
    exit;
}

// Kontrola množství
if ($item['quantity'] < $move_qty) {
    echo json_encode(["error" => "Nedostatek kusů ve skladu"]);
    exit;
}

$source_warehouse = $item['warehouse_id'];
$company_id = $item['company_id'];

// 2️⃣ Odečteme množství ze zdrojového skladu
$stmt = $pdo->prepare("UPDATE items SET quantity = quantity - ? WHERE item_id = ?");
$stmt->execute([$move_qty, $item_id]);

// 3️⃣ Zkusíme zjistit, jestli stejné zboží už existuje v cílovém skladu
$stmt = $pdo->prepare("
    SELECT item_id FROM items
    WHERE warehouse_id = ? AND name = ? AND code = ? AND company_id = ?
    LIMIT 1
");
$stmt->execute([$target_warehouse, $item['name'], $item['code'], $company_id]);
$target_item = $stmt->fetch(PDO::FETCH_ASSOC);

// 4️⃣ Pokud existuje – přičteme kusy, jinak vytvoříme novou položku
if ($target_item) {
    $stmt = $pdo->prepare("UPDATE items SET quantity = quantity + ? WHERE item_id = ?");
    $stmt->execute([$move_qty, $target_item['item_id']]);
    $target_item_id = $target_item['item_id'];
} else {
    $stmt = $pdo->prepare("
        INSERT INTO items (warehouse_id, company_id, name, code, supplier, quantity, location_in_warehouse, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $target_warehouse,
        $company_id,
        $item['name'],
        $item['code'],
        $item['supplier'],
        $move_qty,
        $item['location_in_warehouse']
    ]);
    $target_item_id = $pdo->lastInsertId();
}

// 5️⃣ Zapíšeme pohyb do historie (tabulka item_movements)
$stmt = $pdo->prepare("
    INSERT INTO item_movements (item_id, company_id, from_warehouse, to_warehouse, quantity, action, movement_date)
    VALUES (?, ?, ?, ?, ?, 'presun', NOW())
");
$stmt->execute([$item_id, $company_id, $source_warehouse, $target_warehouse, $move_qty]);

echo json_encode([
    "success" => true,
    "message" => "Zboží přesunuto úspěšně",
    "from" => $source_warehouse,
    "to" => $target_warehouse,
    "moved_qty" => $move_qty
]);
