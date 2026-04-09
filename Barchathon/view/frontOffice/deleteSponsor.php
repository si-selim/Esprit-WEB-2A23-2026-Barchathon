<?php
include '../../controller/sponsorController.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $controller = new sponsorController();
    $controller->deleteSponsor($id);
}

header('Location: mesSponsors.php');
exit;
?>