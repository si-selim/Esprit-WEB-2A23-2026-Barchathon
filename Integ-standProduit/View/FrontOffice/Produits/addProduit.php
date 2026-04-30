<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
if (!isOrganisateur()) { header('Location: ../Stands/listStandsFront.php'); exit; }
require_once __DIR__ . '/../../../Controller/produitcontroller.php';

$controller = new ProduitController();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idProduit    = isset($_POST['idProduit']) && !empty($_POST['idProduit']) ? (int)$_POST['idProduit'] : null;
    $idStand      = isset($_POST['idStand']) ? (int)$_POST['idStand'] : null;
    $nomProduit   = isset($_POST['nomProduit']) ? trim($_POST['nomProduit']) : null;
    $type         = isset($_POST['type']) ? trim($_POST['type']) : null;
    $prixProduit  = isset($_POST['prixProduit']) ? (float)$_POST['prixProduit'] : null;
    $qteStock     = isset($_POST['quantiteStock']) ? (int)$_POST['quantiteStock'] : null;
    $enOutStock   = isset($_POST['stock']) ? $_POST['stock'] : null;

    // On garde directement 1 ou 0 comme demandé par l'utilisateur
    if ($enOutStock === "1" || $enOutStock === 1 || (is_string($enOutStock) && stripos($enOutStock, 'dispo') !== false)) {
        $enOutStock = "1";
    } else {
        $enOutStock = "0";
    }

    $action = $_POST['action'] ?? 'add';

    // Gestion de l'upload de l'image
    $imageName = null;
    if (isset($_FILES['photoProduit']) && $_FILES['photoProduit']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../../uploads/produits/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileExt = strtolower(pathinfo($_FILES['photoProduit']['name'], PATHINFO_EXTENSION));
        // Générer un nom unique
        $imageName = uniqid('prod_') . '.' . $fileExt;
        $targetFile = $uploadDir . $imageName;
        
        if (!move_uploaded_file($_FILES['photoProduit']['tmp_name'], $targetFile)) {
            $imageName = null; // En cas d'erreur
        }
    }

    if ($idStand && $nomProduit && $type && $prixProduit !== null && $qteStock !== null && $enOutStock !== null) {
        try {
            if ($action === 'update' && $idProduit) {
                // Si l'image n'est pas modifiée, on garde l'ancienne.
                // Le modèle est déjà géré pour ignorer null
                $produit = new Produit($idProduit, $idStand, $nomProduit, $type, $prixProduit, $qteStock, $enOutStock, $imageName);
                if ($controller->updateProduit($produit, $idProduit)) {
                    echo "<script>alert('✅ Produit modifié avec succès !'); window.location.href='produit.php?stand_id=$idStand';</script>";
                } else {
                    echo "<script>alert('❌ Erreur lors de la modification. Vérifiez si l\'ID existe.'); history.back();</script>";
                }
            } else {
                // ADD MODE
                $produit = new Produit(null, $idStand, $nomProduit, $type, $prixProduit, $qteStock, $enOutStock, $imageName);
                
                // On essaie d'ajouter et on capture les erreurs précises
                if ($controller->addProduit($produit)) {
                    echo "<script>alert('✅ Produit ajouté avec succès !'); window.location.href='produit.php?stand_id=$idStand';</script>";
                } else {
                    echo "<script>alert('❌ L\'ajout a échoué. Vérifiez que l\'ID Stand ($idStand) existe bien dans la table stands.'); history.back();</script>";
                }
            }
        } catch (Exception $e) {
            echo "<h1>Erreur Système</h1>";
            echo "Détails : " . htmlspecialchars($e->getMessage());
            echo "<br><br><button onclick='history.back()'>Retour</button>";
            exit;
        }
    } else {
        echo "<script>alert('❌ Erreur : Certains champs sont vides ou invalides.'); history.back();</script>";
    }
}
?>
