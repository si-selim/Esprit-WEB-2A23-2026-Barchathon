<?php
include '../../controller/sponsorController.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check required fields
    $requiredFields = ['name', 'type', 'address', 'contact', 'email', 'idUser'];
    $errors = [];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $errors[] = $field;
        }
    }
    if (!empty($errors)) {
        // Redirect back with error
        header('Location: addSponsor.php?error=missing_fields');
        exit;
    }

    // Create Sponsor object
    $sponsor = new Sponsor(
        null,
        $_POST['name'],
        $_POST['type'],
        $_POST['address'],
        $_POST['contact'],
        $_POST['email'],
        $_POST['website'],
        intval($_POST['idUser'])
    );

    // Add to database
    $controller = new sponsorController();
    $controller->addSponsor($sponsor);

    // Redirect back to sponsors page
    header('Location: mesSponsors.php');
    exit;
} else {
    // If not POST, redirect or show error
    header('Location: addSponsor.php');
    exit;
}
?>