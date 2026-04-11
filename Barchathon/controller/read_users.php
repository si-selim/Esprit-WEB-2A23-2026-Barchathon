<?php
require_once __DIR__ . '/../config/db.php';

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;

$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$paysFilter = trim($_GET['pays'] ?? '');
$sortBy = $_GET['sort'] ?? '';
$sortDir = (isset($_GET['dir']) && strtolower($_GET['dir']) === 'asc') ? 'ASC' : 'DESC';

$allowedSorts = ['nom_complet', 'age', 'poids', 'taille', 'ville'];
if (!in_array($sortBy, $allowedSorts)) {
    $sortBy = '';
}

$where = [];
$params = [];

if ($search !== '') {
    $where[] = "(nom_user LIKE ? OR email LIKE ? OR pays LIKE ? OR nom_complet LIKE ?)";
    $s = "%$search%";
    $params = array_merge($params, [$s, $s, $s, $s]);
}

if ($roleFilter !== '') {
    $where[] = "role = ?";
    $params[] = $roleFilter;
}

if ($paysFilter !== '') {
    $where[] = "pays = ?";
    $params[] = $paysFilter;
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM `user` $whereSQL");
$countStmt->execute($params);
$totalUsers = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalUsers / $perPage));

$orderSQL = $sortBy !== '' ? "$sortBy $sortDir, id_user DESC" : "id_user DESC";
$sql = "SELECT * FROM `user` $whereSQL ORDER BY $orderSQL LIMIT $perPage OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
