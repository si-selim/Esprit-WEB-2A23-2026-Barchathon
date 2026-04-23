<?php
require_once "../../Controller/DossardController.php";
require_once "../../Controller/InscriptionController.php";

$id = $_GET['id_inscription'] ?? 0;

$inscriptionController = new InscriptionController();
$dossardController = new DossardController();

// inscription
$data = $inscriptionController->getById($id);
$nbFromInscription = $data['nb_personnes'] ?? 1;

// dossards existants
$dossardsExistants = $dossardController->getByInscription($id);

// nombre existant
$nbExistants = count($dossardsExistants);

// total lignes à afficher
$total = max($nbFromInscription, $nbExistants);

// nom global
$nom_global = $dossardsExistants[0]['nom'] ?? "";

// 🔥 DERNIER NUMERO GLOBAL
$nextNumero = $dossardController->getLastNumero();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dossards</title>
<link rel="stylesheet" href="dossard.css">
</head>

<body>

<div class="page-shell">

<header class="topbar">
    <div class="brand">
        <div class="brand-mark">BT</div>
        <div>
            <strong>BarchaThon</strong>
            <small>Dossards</small>
        </div>
    </div>

    <nav class="nav-links">
        <a href="inscription.php">Inscription</a>
    </nav>
</header>

<main class="content-grid">

<section class="card">

<h1>Dossards #<?php echo $id; ?></h1>

<form method="post" action="../../Controller/DossardController.php">

<input type="hidden" name="id_inscription" value="<?php echo $id; ?>">

<!-- NOM GLOBAL -->
<div class="field-group">
    <label>Nom / Équipe</label>
    <input type="text" name="nom_global" id="nom_global"
           value="<?php echo $nom_global; ?>">
    <small id="error-nom_global"></small>
</div>

<!-- TABLE -->
<div class="table-wrapper">

<table>

<thead>
<tr>
    <th>Numéro</th>
    <th>Taille</th>
    <th>Couleur</th>
</tr>
</thead>

<tbody>

<?php for ($i = 0; $i < $total; $i++) {

    // 🔥 NUMÉROTATION GLOBALE
    if (isset($dossardsExistants[$i])) {
        $numero = $dossardsExistants[$i]['numero'];
    } else {
        $nextNumero++;
        $numero = $nextNumero;
    }
?>

<tr>

    <!-- NUMERO -->
    <td>
        <?php echo $numero; ?>
        <input type="hidden" name="numero[]" value="<?php echo $numero; ?>">
    </td>

    <!-- TAILLE -->
    <td>
        <select name="taille[]" class="taille">
            <option value="">--Choisir--</option>
            <option value="S" <?php if(($dossardsExistants[$i]['taille'] ?? '')=='S') echo "selected"; ?>>S</option>
            <option value="M" <?php if(($dossardsExistants[$i]['taille'] ?? '')=='M') echo "selected"; ?>>M</option>
            <option value="L" <?php if(($dossardsExistants[$i]['taille'] ?? '')=='L') echo "selected"; ?>>L</option>
            <option value="XL" <?php if(($dossardsExistants[$i]['taille'] ?? '')=='XL') echo "selected"; ?>>XL</option>
        </select>
        <small class="error-taille"></small>
    </td>

    <!-- COULEUR -->
    <td>
        <input type="text" name="couleur[]" class="couleur"
               value="<?php echo $dossardsExistants[$i]['couleur'] ?? ''; ?>">
        <small class="error-couleur"></small>
    </td>

</tr>

<?php } ?>

</tbody>
</table>

</div>

<!-- BUTTON -->
<div class="add-button-row">
    <button type="submit" class="btn btn-primary">
        Enregistrer
    </button>
</div>

</form>

</section>

</main>

</div>

<script src="dossard.js"></script>

</body>
</html>