<?php
require_once "../../Model/Dossard.php";

$id = isset($_GET['id_inscription']) ? $_GET['id_inscription'] : 0;

$dossardModel = new Dossard();
$liste = $dossardModel->afficherParInscription($id);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir Dossards</title>
    <link rel="stylesheet" href="dossard.css">
</head>

<body>

<div class="page-shell">

    
    <header class="topbar">
        <div class="brand">
            <span class="brand-mark">BT</span>
            <div>
                <strong>BarchaThon</strong>
                <small>Front Office</small>
            </div>
        </div>

        <nav class="nav-links">
            <a href="index.php">Accueil</a>
            <a href="listMarathons.php">Catalogue</a>
            <a href="inscription.php">Inscription</a>
        </nav>
    </header>

    
    <main class="content-grid">

        <section class="card">

            <div class="card-title">
                <div>
                    <h1>Dossards de l'inscription #<?php echo $id; ?></h1>
                    <p>Liste des dossards générés pour ce participant</p>
                </div>
            </div>

            <?php if(empty($liste)) { ?>
                <p style="color:red;">Aucun dossard trouvé</p>
            <?php } else { ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Numéro</th>
                            <th>Taille</th>
                            <th>Couleur</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($liste as $d) { ?>
                        <tr>
                            <td><?php echo $d['nom']; ?></td>
                            <td><?php echo $d['numero']; ?></td>
                            <td><?php echo $d['taille']; ?></td>
                            <td><?php echo $d['couleur']; ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php } ?>

            <br>

            
            <div class="action-buttons">
                <a href="inscription.php" class="btn btn-secondary">
                     Retour
                </a>
            </div>

        </section>

    </main>

</div>

</body>
</html>