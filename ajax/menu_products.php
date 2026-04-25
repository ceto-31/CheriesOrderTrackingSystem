<?php
/**
 * AJAX endpoint: return filtered/sorted product list as JSON.
 * Used by menu.php for real-time search, category filter, and sort.
 */
require_once '../config/config.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Please log in to view the menu.']);
    exit;
}

$db = (new Database())->getConnection();

// ── Input sanitisation ──────────────────────────────────────────────────
$category = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search   = isset($_GET['search'])   ? trim($_GET['search'])  : '';
$sort_raw = isset($_GET['sort'])     ? $_GET['sort']          : 'name_asc';

// Whitelist sort options (prevents ORDER BY injection)
$sort_map = [
    'name_asc'   => 'p.name ASC',
    'name_desc'  => 'p.name DESC',
    'price_asc'  => 'p.price ASC',
    'price_desc' => 'p.price DESC',
];
$order_by = $sort_map[$sort_raw] ?? 'p.name ASC';

// ── Build query ─────────────────────────────────────────────────────────
$params = [];
$where  = ['p.is_available = 1'];

if ($category > 0) {
    $where[]             = 'p.category_id = :category';
    $params[':category'] = $category;
}

if ($search !== '') {
    $where[]          = '(p.name LIKE :search OR p.description LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

$sql = "SELECT p.id, p.name, p.description, p.price, p.stock,
               p.image, p.low_stock_threshold,
               c.name AS category_name
        FROM   products p
        LEFT JOIN categories c ON c.id = p.category_id
        WHERE  " . implode(' AND ', $where) . "
        ORDER  BY {$order_by}";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

// ── Shape output ────────────────────────────────────────────────────────
$products = [];
foreach ($rows as $row) {
    $image_url = !empty($row['image'])
        ? UPLOAD_URL . rawurlencode($row['image'])
        : null;

    $products[] = [
        'id'             => (int)$row['id'],
        'name'           => $row['name'],
        'description'    => $row['description'] ?? '',
        'price'          => (float)$row['price'],
        'price_formatted'=> format_price($row['price']),
        'stock'          => (int)$row['stock'],
        'low_stock'      => (int)$row['stock'] > 0 && (int)$row['stock'] <= (int)$row['low_stock_threshold'],
        'image_url'      => $image_url,
        'category_name'  => $row['category_name'] ?? '',
    ];
}

echo json_encode(['success' => true, 'products' => $products], JSON_UNESCAPED_UNICODE);
