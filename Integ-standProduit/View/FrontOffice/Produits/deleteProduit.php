<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
if (!isOrganisateur()) { header('Location: listStandsFront.php'); exit; }
require_once __DIR__ . '/../../Controller/produitcontroller.php';

$controller = new ProduitController();

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    if ($controller->deleteProduit((int)$_GET['id'])) {
        header("Location: ../BackOffice/tab_stand.php?section=produits&msg=deleted");
    } else {
        header("Location: ../BackOffice/tab_stand.php?section=produits&msg=error");
    }
} else {
    header("Location: ../BackOffice/tab_stand.php?section=produits");
}
exit;
?>
