<?php
require_once __DIR__ . "/../Model/config.php";
require_once __DIR__ . "/../Model/Dossard.php";
require_once __DIR__ . "/../lib/phpqrcode/qrlib.php";
class DossardController {

    public function add(Dossard $dossard) {

    $db = Config::getConnexion();

    // 1. INSERT
    $sql = "INSERT INTO dossard (nom, numero, taille, couleur, id_inscription)
            VALUES (:nom, :numero, :taille, :couleur, :id_inscription)";

    $stmt = $db->prepare($sql);
    $stmt->execute([
        'nom' => $dossard->getNom(),
        'numero' => $dossard->getNumero(),
        'taille' => $dossard->getTaille(),
        'couleur' => $dossard->getCouleur(),
        'id_inscription' => $dossard->getIdInscription()
    ]);

    // 2. ID généré
    $id = $db->lastInsertId();

    // 3. contenu QR CORRECT
    $dataQR =
"===== DOSSARD =====\n".
"ID Dossard: ".$id."\n".
"ID Inscription: ".$dossard->getIdInscription()."\n".
"Nom: ".$dossard->getNom()."\n".
"Numero: ".$dossard->getNumero()."\n".
"Taille: ".$dossard->getTaille()."\n".
"Couleur: ".$dossard->getCouleur()."\n".
"====================";

    // 4. chemin QR
    $fileName = "qr_" . $id . ".png";
    $filePath = __DIR__ . "/../qr/" . $fileName;

    // 5. génération QR
    \QRcode::png($dataQR, $filePath, QR_ECLEVEL_L, 12);

    // 6. update DB
    $stmt = $db->prepare("UPDATE dossard SET qr_code=? WHERE id_dossard=?");
    $stmt->execute([$fileName, $id]);
}

    public function delete($id) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM dossard WHERE id_dossard=?");
        $stmt->execute([$id]);
    }

    public function deleteByInscription($id_inscription) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("DELETE FROM dossard WHERE id_inscription=?");
        $stmt->execute([$id_inscription]);
    }

    public function getByInscription($id_inscription) {
        $db = Config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM dossard WHERE id_inscription=? ORDER BY numero ASC");
        $stmt->execute([$id_inscription]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $db = Config::getConnexion();
        return $db->query("SELECT * FROM dossard")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastNumero() {
        $db = Config::getConnexion();
        $row = $db->query("SELECT MAX(numero) as max_num FROM dossard")
                  ->fetch(PDO::FETCH_ASSOC);

        return $row['max_num'] ?? 0;
    }
}

// ✅ IMPORTANT : CODE EXECUTION ICI (APRÈS LA CLASSE)

$controller = new DossardController();

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_inscription = $_POST['id_inscription'];
    $nom_global = $_POST['nom_global'];

    $existing = $controller->getByInscription($id_inscription);
    $countExisting = count($existing);

    for ($i = 0; $i < count($_POST['numero']); $i++) {

        // ➤ si déjà existe → UPDATE (PAS DELETE)
        if (isset($existing[$i])) {

            $db = Config::getConnexion();
            $stmt = $db->prepare("
                UPDATE dossard SET
                taille = ?,
                couleur = ?
                WHERE id_dossard = ?
            ");

            $stmt->execute([
                $_POST['taille'][$i],
                $_POST['couleur'][$i],
                $existing[$i]['id_dossard']
            ]);

        } else {

            // ➤ sinon INSERT
            $d = new Dossard(
                null,
                $nom_global,
                $_POST['numero'][$i],
                $_POST['taille'][$i],
                $_POST['couleur'][$i],
                $id_inscription
            );

            $controller->add($d);
        }
    }

    header("Location: ../View/FrontOffice/voirDossard.php?id_inscription=" . $id_inscription);
    exit;
}