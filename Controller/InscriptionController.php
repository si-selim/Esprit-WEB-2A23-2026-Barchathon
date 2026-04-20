<?php
require_once "../Model/Inscription.php";

$inscription = new Inscription();
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $inscription->supprimer($id);

    
    if (isset($_GET['from']) && $_GET['from'] == "front") {
        header("Location: ../View/FrontOffice/inscription.php");
    } else {
        header("Location: ../View/BackOffice/afficher.php");
    }

    exit;
}


/* INSERT + UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id_inscription'];
    $nb = $_POST["nb_personnes"];
    $mode = $_POST["mode_paiement"];
    $date = $_POST["date_paiement"];
    $circuit = $_POST["circuit"];

    if ($circuit == "10km") $id_parcours = 1;
    else if ($circuit == "21km") $id_parcours = 2;
    else $id_parcours = 3;
    $id = isset($_POST['id_inscription']) ? $_POST['id_inscription'] : "";
    if (empty($id)) {

    $inscription->ajouter($nb, $mode, $date, $id_parcours, 1);
    $last_id = $inscription->getLastId();
    header("Location: ../View/FrontOffice/dossard.php?id_inscription=".$last_id);
}else {
        $inscription->modifier($id, $nb, $mode, $date, $id_parcours);
        header("Location: ../View/FrontOffice/inscription.php?success=update");
    }
    exit;
    

}
?>