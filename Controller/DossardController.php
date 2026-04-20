<?php
require_once "../Model/Dossard.php";

$dossard = new Dossard();
if (isset($_GET['delete'])) {

    $id = $_GET['delete'];

    require_once "../Model/Dossard.php";
    $dossard = new Dossard();

    $dossard->supprimer($id);

    header("Location: ../View/BackOffice/afficher.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nom_global = $_POST['nom_global'];
    $numeros = $_POST['numero'];
    $tailles = $_POST['taille'];
    $couleurs = $_POST['couleur'];
    $id_inscription = $_POST['id_inscription'];
    $dossard->deleteByInscription($id_inscription);
    for ($i = 0; $i < count($numeros); $i++) {

        $dossard->ajouter(
            $nom_global,
            $numeros[$i],
            $tailles[$i],
            $couleurs[$i],
            $id_inscription
        );
    }

    header("Location: ../View/FrontOffice/inscription.php?success=dossard");
exit;
}
?>