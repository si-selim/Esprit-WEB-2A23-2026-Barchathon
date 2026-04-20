<?php
class Dossard {

    private $conn;

    public function __construct() {
        $this->conn = new PDO("mysql:host=localhost;dbname=projetwebinscription+dossard", "root", "");
    }

    
    public function ajouter($nom, $numero, $taille, $couleur, $id_inscription) {

        $sql = "INSERT INTO dossard (nom, numero, taille, couleur, id_inscription)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$nom, $numero, $taille, $couleur, $id_inscription]);
    }

    public function afficherParInscription($id) {

        $sql = "SELECT * FROM dossard WHERE id_inscription = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getLastNumero() {
    $sql = "SELECT MAX(numero) as max_num FROM dossard";
    $result = $this->conn->query($sql)->fetch(PDO::FETCH_ASSOC);

    return $result['max_num'] !== null ? (int)$result['max_num'] : -1;
}
    public function supprimer($id)
{
    $sql = "DELETE FROM dossard WHERE id_dossard = ?";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([$id]);
}
public function afficherTous()
{
    $sql = "SELECT * FROM dossard ORDER BY id_dossard DESC";
    $stmt = $this->conn->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
public function deleteByInscription($id) {
    $sql = "DELETE FROM dossard WHERE id_inscription = ?";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute([$id]);
}
}
?>
