<?php
require_once "../../Model/Dossard.php";
require_once "../../Model/Inscription.php";

// 1️⃣ récupérer ID
$id = isset($_GET['id_inscription']) ? $_GET['id_inscription'] : 0;

// 2️⃣ récupérer dossards
$dossardModel = new Dossard();
$liste = $dossardModel->afficherParInscription($id);

// 3️⃣ récupérer nb personnes
$inscriptionModel = new Inscription();
$data = $inscriptionModel->rechercher($id);

$nb = $data ? $data[0]['nb_personnes'] : 0;
$nbExistants = count($liste);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Voir Dossards</title>

    <style>
        body {
            margin:0;
            font-family:Segoe UI;
            background:#f4fbfb;
        }

        .page-shell {
            width:90%;
            margin:auto;
        }

        .topbar {
            display:flex;
            justify-content:space-between;
            align-items:center;
            padding:15px;
            background:white;
            border-radius:10px;
            margin:20px 0;
        }

        .nav-links a {
            margin:0 10px;
            text-decoration:none;
            color:#0b2032;
            font-weight:bold;
        }

        .card {
            background:white;
            padding:20px;
            border-radius:15px;
            box-shadow:0 5px 15px rgba(0,0,0,0.08);
        }

        /* 🔵 TABLE STYLE BACKOFFICE */
        .table-wrapper table {
            width:100%;
            border-collapse:collapse;
            border-radius:15px;
            overflow:hidden;
        }

        .table-wrapper thead th {
            background:#0b2032; /* BLEU */
            color:white;
            padding:12px;
            text-align:center;
        }

        .table-wrapper tbody td {
            padding:12px;
            border-bottom:1px solid #ddd;
            text-align:center;
        }

        .table-wrapper tbody tr:hover {
            background:#f0f4f8;
        }

        .btn {
            display:inline-block;
            padding:10px 15px;
            background:#0f766e;
            color:white;
            border-radius:10px;
            text-decoration:none;
        }
    </style>
</head>

<body>

<div class="page-shell">

    <header class="topbar">
        <div>
            <strong>BarchaThon</strong>
        </div>

        <nav class="nav-links">
            <a href="inscription.php">Inscription</a>
        </nav>
    </header>

    <main>

        <div class="card">

            <h2>Dossards de l'inscription #<?php echo $id; ?></h2>

            <?php if($nb == 0) { ?>
                <p style="color:red;">Aucun dossard trouvé</p>
            <?php } else { ?>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Action</th>
                            <th>Nom</th>
                            <th>Numéro</th>
                            <th>Taille</th>
                            <th>Couleur</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php for($i = 0; $i < $nb; $i++) { ?>
                    <tr>

    <!-- ACTION -->
    <td>
        <?php if(!isset($liste[$i])) { ?>
            
            <a href="dossard.php?id_inscription=<?php echo $id; ?>" 
               class="btn"
               style="background:orange;">
               Compléter
            </a>

        <?php } else { ?>
            ✔
        <?php } ?>
    </td>

   
    <td>
        <?php echo isset($liste[$i]) ? $liste[$i]['nom'] : "—"; ?>
    </td>

    
    <td>
        <?php echo isset($liste[$i]) ? $liste[$i]['numero'] : ($i + 1); ?>
    </td>

    
    <td>
        <?php echo isset($liste[$i]) ? $liste[$i]['taille'] : "Non rempli"; ?>
    </td>

   
    <td>
        <?php echo isset($liste[$i]) ? $liste[$i]['couleur'] : "Non rempli"; ?>
    </td>

</tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>

            <?php } ?>

            <br>

            <a href="inscription.php" class="btn">Retour</a>

        </div>

    </main>

</div>

</body>
</html>