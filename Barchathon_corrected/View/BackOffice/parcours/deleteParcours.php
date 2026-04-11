<?php
include '../../controller/ParcoursController.php';

$controller = new ParcoursController();

if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $controller->supprimerParcours((int)$_GET["id"]);
}

header('Location: listParcours.php');
exit;
?>