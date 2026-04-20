<!DOCTYPE html>
<?php
require_once "../../Model/Inscription.php";
require_once "../../Model/Dossard.php";

$id = isset($_GET['id_inscription']) ? $_GET['id_inscription'] : 0;

$model = new Inscription();
$data = $model->rechercher($id);
$nbFromInscription = $data ? $data[0]['nb_personnes'] : 1;

$dossardModel = new Dossard();
$start = 0;


$dossardsExistants = $dossardModel->afficherParInscription($id);
$nbExistants = count($dossardsExistants);


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

    <!-- ID -->
    <input type="hidden" name="id_inscription" value="<?php echo $id; ?>">

    <!-- NOM GLOBAL -->
    <div class="field-group">
        <label>Nom (famille / équipe)</label>
        <input type="text" name="nom_global" id="nom_global">
        <small id="error-nom_global"></small>
    </div>

    <br>

    <!-- TABLE -->
    <table border="1" cellpadding="10">
<tr>
    <th>Numéro</th>
    <th>Taille</th>
    <th>Couleur</th>
</tr>

<?php for($i=0; $i<$nbFromInscription; $i++) { ?>

<tr>

    <!-- NUMERO -->
    <td>
    <?php 
        if(isset($dossardsExistants[$i])) {
            echo $dossardsExistants[$i]['numero'];
            $numero = $dossardsExistants[$i]['numero'];
        } else {
            $numero = $i + 1;
            echo $numero;
        }
    ?>
    <input type="hidden" name="numero[]" value="<?php echo $numero; ?>">
    </td>

    <td>
<select name="taille[]" class="taille">
    <option value="">--Choisir--</option>
    <option value="S" <?php if(isset($dossardsExistants[$i]) && $dossardsExistants[$i]['taille']=="S") echo "selected"; ?>>S</option>
    <option value="M" <?php if(isset($dossardsExistants[$i]) && $dossardsExistants[$i]['taille']=="M") echo "selected"; ?>>M</option>
    <option value="L" <?php if(isset($dossardsExistants[$i]) && $dossardsExistants[$i]['taille']=="L") echo "selected"; ?>>L</option>
    <option value="XL" <?php if(isset($dossardsExistants[$i]) && $dossardsExistants[$i]['taille']=="XL") echo "selected"; ?>>XL</option>
</select>


<small class="error-taille"></small>

</td>

    <!-- COULEUR -->
    <td>
<input type="text" name="couleur[]" class="couleur"
value="<?php echo isset($dossardsExistants[$i]) ? $dossardsExistants[$i]['couleur'] : ''; ?>">


<small class="error-couleur"></small>

</td>
</tr>

<?php } ?>

</table>
    <br>

    <!-- BOUTON -->
    <div class="add-button-row">
        <button type="submit" class="btn btn-primary">
            Enregistrer les dossards
        </button>
    </div>

</form>

</section>

</main>

</div>

<!-- JS -->
<script src="dossard.js"></script>

</body>
</html>