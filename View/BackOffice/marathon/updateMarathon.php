<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/MarathonController.php';

$controller = new MarathonController();
$error = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('ID invalide.');
}

/* Charger les données existantes */
$data = $controller->showMarathon($id);
if (!$data) {
    die('Marathon introuvable.');
}

/* UPDATE */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom_marathon'] ?? '');
    $org = trim($_POST['organisateur_marathon'] ?? '');
    $region = trim($_POST['region_marathon'] ?? '');
    $date = $_POST['date_marathon'] ?? '';
    $places = (int)($_POST['nb_places_dispo'] ?? 0);
    $prix = (float)($_POST['prix_marathon'] ?? 0);

    if ($nom !== '' && $org !== '' && $region !== '' && $date !== '') {

        // 🔥 image actuelle par défaut
        $image = $data['image_marathon'];

        // 🔥 nouvelle image si upload
        if (!empty($_FILES['image_marathon']['name']) && $_FILES['image_marathon']['error'] === 0) {
            $newImage = $controller->saveUploadedImage($_FILES['image_marathon']);
            if ($newImage) {
                $image = $newImage;
            }
        }

        // 🔥 création objet
        $m = new Marathon(
            $id,
            $nom,
            $image,
            $org,
            $region,
            $date,
            $places,
            $prix
        );

        // 🔥 update DB
        $controller->modifierMarathon($m, $id);

        header('Location: listMarathons.php');
        exit;

    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Modifier Marathon #<?php echo $id; ?> — BarchaThon</title>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --bg:#f4fbfb; --sand:#fff8e7; }
        * { box-sizing:border-box; }
        body { margin:0; font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,var(--sand),var(--bg)); }
        .wrap { width:min(980px,calc(100% - 32px)); margin:0 auto; padding:32px 0 48px; }
        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:20px; }
        .panel { background:#fff; border-radius:28px; padding:32px; box-shadow:0 18px 40px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        .panel-header { display:flex; align-items:center; gap:14px; margin-bottom:24px; padding-bottom:20px; border-bottom:1px solid #e6edf3; }
        .panel-icon { width:52px; height:52px; border-radius:18px; background:linear-gradient(135deg,var(--sun),#f59e0b); display:grid; place-items:center; font-size:1.4rem; }
        h1 { margin:0; font-size:1.9rem; }
        .grid { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px; }
        .field { display:grid; gap:8px; }
        .field.full { grid-column:1/-1; }
        label { font-weight:700; font-size:0.95rem; }
        input, select { width:100%; border-radius:14px; border:1px solid #cbd5e1; padding:12px 14px; font:inherit; transition:border .2s; }
        input:focus, select:focus { outline:none; border-color:var(--teal); box-shadow:0 0 0 3px rgba(15,118,110,.12); }
        .feedback { font-size:0.88rem; min-height:18px; }
        .feedback.error { color:#b42318; }
        .feedback.success { color:#027a48; }
        .current-img { display:flex; align-items:center; gap:12px; padding:12px; background:#f8fafc; border-radius:12px; margin-top:6px; }
        .current-img img { width:80px; height:56px; object-fit:cover; border-radius:8px; }
        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:28px; }
        .btn { text-decoration:none; border:0; border-radius:14px; padding:13px 20px; font-weight:700; cursor:pointer; font-size:1rem; }
        .btn-save { background:linear-gradient(135deg,var(--sun),#f59e0b); color:var(--ink); }
        .btn-secondary { background:#edf2f7; color:var(--ink); }
        @media(max-width:700px){ .grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<div class="wrap">
    <a class="back-link" href="listMarathons.php">← Retour à la liste</a>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-icon">✏️</div>
            <div>
                <h1>Modifier le Marathon #<?php echo $id; ?></h1>
            </div>
        </div>

        <form method="POST" enctype="multipart/form-data" novalidate>
            <div class="grid">
                <div class="field">
                    <label for="nom_marathon">Nom du Marathon</label>
                    <input type="text" id="nom_marathon" name="nom_marathon" value="<?php echo htmlspecialchars($data['nom_marathon']); ?>">
                    <div class="feedback" id="nomFeedback"></div>
                </div>
                <div class="field">
                    <label for="organisateur_marathon">Organisateur</label>
                    <input type="text" id="organisateur_marathon" name="organisateur_marathon" value="<?php echo htmlspecialchars($data['organisateur_marathon']); ?>">
                    <div class="feedback" id="organisateurFeedback"></div>
                </div>
                <div class="field">
                    <label for="region_marathon">Région</label>
                    <input type="text" id="region_marathon" name="region_marathon" value="<?php echo htmlspecialchars($data['region_marathon']); ?>">
                    <div class="feedback" id="regionFeedback"></div>
                </div>
                <div class="field">
                    <label for="date_marathon">Date du Marathon</label>
                    <input type="date" id="date_marathon" name="date_marathon" value="<?php echo htmlspecialchars($data['date_marathon']); ?>">
                    <div class="feedback" id="dateFeedback"></div>
                </div>
                <div class="field">
                    <label for="nb_places_dispo">Places Disponibles</label>
                    <input type="number" id="nb_places_dispo" name="nb_places_dispo" min="1" value="<?php echo (int)$data['nb_places_dispo']; ?>">
                    <div class="feedback" id="placesFeedback"></div>
                </div>
                <div class="field">
                    <label for="prix_marathon">Prix d'inscription (TND)</label>
                    <input type="number" id="prix_marathon" name="prix_marathon" min="0" step="0.01" value="<?php echo htmlspecialchars($data['prix_marathon']); ?>">
                    <div class="feedback" id="prixFeedback"></div>
                </div>
                <div class="field full">
                    <label for="image_marathon">Changer la Photo du Marathon</label>
                    <input type="file" id="image_marathon" name="image_marathon" accept=".jpg,.jpeg,.png,.webp">
                    <div class="current-img" id="previewContainer">
                        <img id="previewImg" src="../../FrontOffice/<?php echo htmlspecialchars($data['image_marathon']); ?>" alt="" onerror="this.src='../../FrontOffice/images/img1.svg'">
                        <span id="previewLabel" style="color:#627d98;font-size:0.9rem;">Image actuelle : <?php echo basename($data['image_marathon']); ?></span>
                    </div>
                    <div class="feedback" id="imageFeedback"></div>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-save">💾 Sauvegarder les modifications</button>
                <a href="listMarathons.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script>
// Aperçu live de la nouvelle image choisie
document.getElementById('image_marathon').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('previewImg').src = e.target.result;
        document.getElementById('previewLabel').textContent = '📸 Nouvelle image : ' + file.name;
        document.getElementById('previewLabel').style.color = '#0f766e';
    };
    reader.readAsDataURL(file);
});

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const fields = {
        nom: document.getElementById('nom_marathon'),
        organisateur: document.getElementById('organisateur_marathon'),
        region: document.getElementById('region_marathon'),
        date: document.getElementById('date_marathon'),
        places: document.getElementById('nb_places_dispo'),
        prix: document.getElementById('prix_marathon')
    };

    function setFeedback(id, msg, type) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = msg;
        el.className = 'feedback ' + (type || '');
    }

    // Au moins une lettre obligatoire (lettres + chiffres OK, mais pas QUE des chiffres)
    const alphaNumRegex = /^(?=.*[A-Za-zÀ-ÖØ-öø-ÿ])[A-Za-zÀ-ÖØ-öø-ÿ0-9\s\-'\.]+$/;
    // Lettres uniquement (pas de chiffres du tout)
    const alphaOnlyRegex = /^[A-Za-zÀ-ÖØ-öø-ÿ\s\-'\.]+$/;

    function validateNom() {
        const v = fields.nom.value.trim();
        if (v.length === 0) { setFeedback('nomFeedback', '❌ Le nom est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('nomFeedback', '❌ Le nom doit contenir au moins 3 caractères.', 'error'); return false; }
        if (!alphaNumRegex.test(v)) { setFeedback('nomFeedback', '❌ Le nom doit contenir au moins une lettre (pas uniquement des chiffres).', 'error'); return false; }
        setFeedback('nomFeedback', '✅ Nom valide.', 'success'); return true;
    }

    function validateOrganisateur() {
        const v = fields.organisateur.value.trim();
        if (v.length === 0) { setFeedback('organisateurFeedback', "❌ L'organisateur est obligatoire.", 'error'); return false; }
        if (v.length < 3) { setFeedback('organisateurFeedback', "❌ L'organisateur doit contenir au moins 3 caractères.", 'error'); return false; }
        if (!alphaOnlyRegex.test(v)) { setFeedback('organisateurFeedback', "❌ L'organisateur doit contenir uniquement des lettres (pas de chiffres).", 'error'); return false; }
        setFeedback('organisateurFeedback', '✅ Organisateur valide.', 'success'); return true;
    }

    function validateRegion() {
        const v = fields.region.value.trim();
        if (v.length === 0) { setFeedback('regionFeedback', '❌ La région est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('regionFeedback', '❌ La région doit contenir au moins 3 caractères.', 'error'); return false; }
        if (!alphaNumRegex.test(v)) { setFeedback('regionFeedback', '❌ La région doit contenir au moins une lettre (pas uniquement des chiffres).', 'error'); return false; }
        setFeedback('regionFeedback', '✅ Région valide.', 'success'); return true;
    }

    function validateDate() {
        const v = fields.date.value;
        if (!v) { setFeedback('dateFeedback', '❌ La date est obligatoire.', 'error'); return false; }
        const sel = new Date(v + 'T00:00:00'), today = new Date();
        today.setHours(0, 0, 0, 0);
        if (sel <= today) { setFeedback('dateFeedback', '❌ La date doit être dans le futur.', 'error'); return false; }
        setFeedback('dateFeedback', '✅ Date valide.', 'success'); return true;
    }

    function validatePlaces() {
        const raw = fields.places.value.trim();
        if (raw === '') { setFeedback('placesFeedback', '❌ Le nombre de places est obligatoire.', 'error'); return false; }
        if (!/^\d+$/.test(raw)) { setFeedback('placesFeedback', '❌ Veuillez saisir uniquement des chiffres entiers.', 'error'); return false; }
        if (parseInt(raw, 10) < 1) { setFeedback('placesFeedback', '❌ Le nombre de places doit être un entier positif (≥ 1).', 'error'); return false; }
        setFeedback('placesFeedback', '✅ Nombre de places valide.', 'success'); return true;
    }

    function validatePrix() {
        const raw = fields.prix.value.trim();
        if (raw === '') { setFeedback('prixFeedback', '❌ Le prix est obligatoire.', 'error'); return false; }
        if (!/^\d+(\.\d{1,2})?$/.test(raw)) { setFeedback('prixFeedback', '❌ Veuillez saisir uniquement des chiffres (ex: 30 ou 30.50).', 'error'); return false; }
        if (parseFloat(raw) < 0) { setFeedback('prixFeedback', '❌ Le prix doit être un nombre positif ou zéro.', 'error'); return false; }
        setFeedback('prixFeedback', '✅ Prix valide.', 'success'); return true;
    }

    // Bloquer les lettres dans les champs numeriques
    fields.places.addEventListener('keypress', function (e) {
        if (!/[\d]/.test(e.key)) e.preventDefault();
    });
    fields.prix.addEventListener('keypress', function (e) {
        if (!/[\d\.]/.test(e.key)) e.preventDefault();
        if (e.key === '.' && this.value.includes('.')) e.preventDefault();
    });

    fields.nom.addEventListener('input', validateNom);
    fields.nom.addEventListener('blur', validateNom);
    fields.organisateur.addEventListener('input', validateOrganisateur);
    fields.organisateur.addEventListener('blur', validateOrganisateur);
    fields.region.addEventListener('input', validateRegion);
    fields.region.addEventListener('blur', validateRegion);
    fields.date.addEventListener('change', validateDate);
    fields.places.addEventListener('input', validatePlaces);
    fields.places.addEventListener('blur', validatePlaces);
    fields.prix.addEventListener('input', validatePrix);
    fields.prix.addEventListener('blur', validatePrix);

    form.addEventListener('submit', function (e) {
        const valid = [validateNom(), validateOrganisateur(), validateRegion(), validateDate(), validatePlaces(), validatePrix()];
        if (valid.includes(false)) e.preventDefault();
    });
});
</script>
</body>
</html>
