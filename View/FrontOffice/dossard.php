<!DOCTYPE html>
<?php
require_once "../../Model/Inscription.php";
require_once "../../Model/Dossard.php";


$id = isset($_GET['id_inscription']) ? $_GET['id_inscription'] : 0;


$model = new Inscription();
$data = $model->rechercher($id);
$nb = $data ? $data[0]['nb_personnes'] : 1;


$dossardModel = new Dossard();
$start = $dossardModel->getLastNumero();
?>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dossard</title>
    <link rel="stylesheet" href="dossard.css">
</head>

<body>

<div class="page-shell">

<header class="topbar">
    <div class="brand">
        <strong>BarchaThon</strong>
    </div>

    <nav class="nav-links">
        <a href="index.php">Accueil</a>
        <a href="listMarathons.php">Catalogue</a>
        <a href="inscription.php">Inscription</a>
    </nav>
</header>

<main class="content-grid">

<section class="card card-form">

<h1>Dossards</h1>

<form method="post" action="../../Controller/DossardController.php">

    
    <input type="hidden" name="id_inscription" value="<?php echo $id; ?>">

    
    <div class="field-group">
        <label>Nom (famille / équipe)</label>
        <input type="text" name="nom_global" required>
    </div>

    <br>

    
    <table border="1" cellpadding="10">
        <tr>
            <th>Numéro</th>
            <th>Taille</th>
            <th>Couleur</th>
        </tr>

        <?php for($i=0; $i<$nb; $i++) { ?>
        <tr>

            <td>
                <?php echo ($start + $i + 1); ?>
                <input type="hidden" name="numero[]" value="<?php echo ($start + $i + 1); ?>">
            </td>

            <td>
                <select name="taille[]">
                    <option value="S">S</option>
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                </select>
            </td>

            <td>
                <input type="text" name="couleur[]" placeholder="ex: rouge">
            </td>

        </tr>
        <?php } ?>

    </table>

    <br>

        <div class="add-button-row">
            <button type="submit" class="btn btn-primary">
                 Enregistrer les dossards
            </button>
        </div>
</form>

</section>

</main>

</div>

</body>
</html>