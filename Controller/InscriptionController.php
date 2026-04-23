<?php
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Inscription.php";

class InscriptionController {

    // ➤ AJOUT inscription + retour ID
    public function add(Inscription $inscription) {

        $sql = "INSERT INTO inscription
        (nb_personnes, mode_de_paiement, date_inscription, date_paiement, id_user, id_parcours)
        VALUES (:nb, :mode, NOW(), :date, :user, :parcours)";

        $db = Config::getConnexion();
        $stmt = $db->prepare($sql);

        $stmt->execute([
            'nb' => $inscription->getNbPersonnes(),
            'mode' => $inscription->getModePaiement(),
            'date' => date('Y-m-d H:i:s'),
            'user' => $inscription->getIdUser(),
            'parcours' => $inscription->getIdParcours()
        ]);

        // 🔥 IMPORTANT : ID pour redirection vers dossard
        return $db->lastInsertId();
    }

    // ➤ DELETE
    public function delete($id) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM inscription WHERE id_inscription=?");
        $stmt->execute([$id]);
    }

    // ➤ GET ALL
    public function getAll() {
        $db = Config::getConnexion();
        return $db->query("SELECT * FROM inscription ORDER BY id_inscription DESC")
                  ->fetchAll(PDO::FETCH_ASSOC);
    }

    // ➤ GET BY ID (corrigé)
    public function getById($id) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM inscription WHERE id_inscription=?");
        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC); // ✔ IMPORTANT FIX
    }

    // ➤ UPDATE
    public function update(Inscription $inscription, $id) {

        $db = Config::getConnexion();

        $stmt = $db->prepare("
            UPDATE inscription SET
            nb_personnes=:nb,
            mode_de_paiement=:mode,
            date_paiement=:date,
            id_parcours=:parcours
            WHERE id_inscription=:id
        ");

        $stmt->execute([
            'nb' => $inscription->getNbPersonnes(),
            'mode' => $inscription->getModePaiement(),
            'date' => $inscription->getDatePaiement(),
            'parcours' => $inscription->getIdParcours(),
            'id' => $id
        ]);
    }
}

/* =====================================================
   🔥 EXECUTION (POST + DELETE)
===================================================== */

$controller = new InscriptionController();

/* ➤ DELETE */
if (isset($_GET['delete'])) {

    $controller->delete($_GET['delete']);

    $redirect = $_GET['redirect'] ?? 'back';

    if ($redirect == "front_inscription") {
        header("Location: ../View/FrontOffice/inscription.php");
    } elseif ($redirect == "front_afficher") {
        header("Location: ../View/FrontOffice/afficher.php");
    } else {
        header("Location: ../View/BackOffice/afficher.php");
    }
    exit;
}



?>