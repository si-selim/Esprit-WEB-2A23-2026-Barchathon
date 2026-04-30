<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: listStandsFront.php'); exit; }
require_once __DIR__ . '/../../../Controller/standcontroller.php';

$controller = new StandController();

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    $controller->deleteStand((int) $_GET['id']);
    if (isset($_GET['redirect']) && $_GET['redirect'] === 'front') {
        header("Location: listStandsFront.php?msg=deleted");
    } else {
        header("Location: ../../BackOffice/dashboard.php?section=stands&msg=deleted");
    }
    exit;
} else {
    if (isset($_GET['redirect']) && $_GET['redirect'] === 'front') {
        header("Location: listStandsFront.php");
    } else {
        header("Location: ../../BackOffice/dashboard.php?section=stands");
    }
    exit;
}
