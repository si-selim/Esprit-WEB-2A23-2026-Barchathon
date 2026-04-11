<?php
require_once "../../Model/Inscription.php";
require_once "../../Model/Dossard.php";

$inscriptionModel = new Inscription();
$liste = $inscriptionModel->afficher();

$dossardModel = new Dossard();
$dossards = $dossardModel->afficherTous();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BackOffice - Inscriptions & Dossards</title>
    <link rel="stylesheet" href="afficher.css">
</head>

<body>

<div class="page-shell">

<header class="topbar">
    <div class="brand">
        <span class="brand-mark">BT</span>
        <div>
            <strong>BarchaThon</strong>
            <small>Back Office</small>
        </div>
    </div>

    <nav class="nav-links">
        <a href="index.php">Accueil</a>
        <a href="afficher.php" class="active">Gérer Inscriptions</a>
    </nav>

    <div class="user-badge">Admin</div>
</header>

<main class="content-grid">


<section class="card">

<h1>Gestion des Inscriptions</h1>

<table class="table-wrapper">
    <thead>
        <tr>
            <th>Mode paiement</th>
            <th>Circuit</th>
            <th>Nb personnes</th>
            <th>Date paiement</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach($liste as $row) { ?>
        <tr>
            <td><?php echo $row['mode_de_paiement']; ?></td>
            <td><?php echo $row['id_parcours']; ?></td>
            <td><?php echo $row['nb_personnes']; ?></td>
            <td><?php echo $row['date_paiement']; ?></td>

            <td class="table-actions">

                
                <a href="voirDossard.php?id_inscription=<?php echo $row['id_inscription']; ?>"
                   class="btn btn-secondary btn-small">
                    Voir
                </a>

                
                <a href="../../Controller/InscriptionController.php?delete=<?php echo $row['id_inscription']; ?>"
                   class="btn btn-danger btn-small"
                   onclick="return confirm('Supprimer inscription ?')">
                    Supprimer
                </a>

            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

</section>


<section class="card">

<h1>Gestion des Dossards</h1>

<table class="table-wrapper">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Numéro</th>
            <th>Taille</th>
            <th>Couleur</th>
            <th>Actions</th>
        </tr>
    </thead>

    <tbody>
    <?php foreach($dossards as $d) { ?>
        <tr>
            <td><?php echo $d['nom']; ?></td>
            <td><?php echo $d['numero']; ?></td>
            <td><?php echo $d['taille']; ?></td>
            <td><?php echo $d['couleur']; ?></td>

            <td class="table-actions">

                
                <a href="../../Controller/DossardController.php?delete=<?php echo $d['id_dossard']; ?>"
                   class="btn btn-danger btn-small"
                   onclick="return confirm('Supprimer dossard ?')">
                    Supprimer
                </a>

            </td>
        </tr>
    <?php } ?>
    </tbody>
</table>

</section>

</main>

</div>

</body>
</html>