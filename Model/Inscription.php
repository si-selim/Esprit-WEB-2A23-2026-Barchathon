<?php

class Inscription {

    private $conn;

    public function __construct() {
        $this->conn = new PDO("mysql:host=localhost;dbname=projetwebinscription+dossard", "root", "");
    }

    public function ajouter($nb, $mode, $date_paiement, $id_parcours, $id_user) {

        $sql = "INSERT INTO inscription 
                (nb_personnes, mode_de_paiement, date_inscription, date_paiement, id_user, id_parcours) 
                VALUES (?, ?, NOW(), ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nb, $mode, $date_paiement, $id_user, $id_parcours]);
    }
    public function afficher() {
    $sql = "SELECT * FROM inscription ORDER BY id_inscription DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function supprimer($id)
{
    $sql = "DELETE FROM inscription WHERE id_inscription = ?";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([$id]);
}
    public function modifier($id, $nb, $mode, $date_paiement, $id_parcours) {

    $sql = "UPDATE inscription 
            SET nb_personnes = ?, 
                mode_de_paiement = ?, 
                date_paiement = ?, 
                id_parcours = ?
            WHERE id_inscription = ?";

    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([$nb, $mode, $date_paiement, $id_parcours, $id]);
}
    public function rechercher($id) {
    $sql = "SELECT * FROM inscription WHERE id_inscription = ?";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute([$id]);

    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $result ? $result : [];
}
    public function getLastId() {
    return $this->conn->lastInsertId();
}
    
}
?>