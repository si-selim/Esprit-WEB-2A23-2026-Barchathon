<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/StandController.php';
require_once __DIR__ . '/../partials/session.php';

// Verifier l'authentification Admin
if (!isAdmin()) { 
    http_response_code(403);
    echo json_encode(["error" => "Acces refuse"]);
    exit;
}

$search = $_GET['search'] ?? '';

if (empty($search)) {
    http_response_code(400);
    echo json_encode(["error" => "Veuillez fournir l'ID ou le nom du stand recherche."]);
    exit;
}

$sCtrl = new StandController();
$recommendations = $sCtrl->getIntelligentRecommendations($search);

echo json_encode($recommendations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
