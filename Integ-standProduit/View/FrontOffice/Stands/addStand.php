<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: listStandsFront.php'); exit; }
require_once __DIR__ . '/../../../Controller/standcontroller.php';

$controller = new StandController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $idStand     = isset($_POST['ID_stand']) && !empty($_POST['ID_stand']) ? (int) $_POST['ID_stand'] : null;
    $idParcours  = isset($_POST['ID_parcours']) ? (int) $_POST['ID_parcours'] : null;
    $nomStand    = isset($_POST['nom_stand']) ? trim($_POST['nom_stand']) : null;
    $position    = isset($_POST['position']) ? trim($_POST['position']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;

    if ($idParcours && $nomStand && $position && $description) {

        // Crée l'objet Stand
        $stand = new Stand(
            $idStand,
            $idParcours,
            $nomStand,
            $position,
            $description
        );

        // Vérifier si l'ID existe déjà pour faire UPDATE au lieu de INSERT
        $existingStand = null;
        if ($idStand) {
            $existingStand = $controller->getStandByValue((string)$idStand);
        }

        if ($existingStand) {
            // C'est un UPDATE
            if ($controller->updateStand($stand, $idStand)) {
                echo "<script>alert('✅ Stand modifié avec succès !'); window.location.href='listStandsFront.php';</script>";
                exit;
            } else {
                echo "<script>alert('❌ Erreur lors de la modification du stand.'); window.location.href='listStandsFront.php';</script>";
                exit;
            }
        } else {
            // C'est un INSERT
            if ($controller->addStand($stand)) {
                echo "<script>alert('✅ Stand ajouté avec succès !'); window.location.href='listStandsFront.php';</script>";
                exit;
            } else {
                echo "<script>alert('❌ Erreur lors de l\'ajout du stand.'); window.location.href='listStandsFront.php';</script>";
                exit;
            }
        }

    } else {
        echo "<script>alert('❌ Erreur: tous les champs sont obligatoires.'); window.location.href='listStandsFront.php';</script>";
        exit;
    }
}
?>
