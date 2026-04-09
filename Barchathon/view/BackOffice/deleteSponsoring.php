<?php
include '../../controller/sponsoringController.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $controller = new sponsoringController();
    $controller->deleteSponsoring($id);
}

header('Location: backoffice_Sponsor.php');
exit;
