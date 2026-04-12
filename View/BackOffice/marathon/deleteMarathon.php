<?php
include '../../controller/MarathonController.php';

$controller = new MarathonController();

if (isset($_GET["id"]) && !empty($_GET["id"])) {
    $controller->supprimerMarathon((int)$_GET["id"]);
}

header('Location: listMarathons.php');
exit;
?>