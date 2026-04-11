<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$search = trim($_GET['search'] ?? '');
$roleFilter = trim($_GET['role'] ?? '');
$paysFilter = trim($_GET['pays'] ?? '');

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

$stmt = $pdo->prepare("SELECT id_user, nom_complet, nom_user, role, email, age, poids, taille, pays, ville, tel, occupation FROM `user` $whereSQL ORDER BY id_user DESC");
$stmt->execute($params);
$users = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="utilisateurs_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

fputcsv($output, ['ID', 'Nom complet', 'Nom utilisateur', 'Role', 'Email', 'Age', 'Poids (kg)', 'Taille (cm)', 'Pays', 'Ville', 'Telephone', 'Occupation'], ';');

foreach ($users as $u) {
    fputcsv($output, [
        $u['id_user'],
        $u['nom_complet'],
        $u['nom_user'],
        $u['role'],
        $u['email'],
        $u['age'] ?? '',
        $u['poids'] ?? '',
        $u['taille'] ?? '',
        $u['pays'] ?? '',
        $u['ville'] ?? '',
        $u['tel'] ?? '',
        $u['occupation'] ?? ''
    ], ';');
}

fclose($output);
exit;
