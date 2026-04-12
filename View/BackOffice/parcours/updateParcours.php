<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../../Controller/ParcoursController.php';
require_once __DIR__ . '/../../../Controller/MarathonController.php';
require_once __DIR__ . '/../../../Model/Parcours.php';

$controller = new ParcoursController();
$marathonController = new MarathonController();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) die('ID invalide.');

$data = $controller->showParcours($id);
if (!$data) die('Parcours introuvable.');
$marathons = $marathonController->afficherMarathon();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $p = new Parcours(
        $id,
        $_POST['nom_parcours'] ?? '',
        $_POST['point_depart'] ?? '',
        $_POST['point_arrivee'] ?? '',
        (float)($_POST['distance'] ?? 0),
        $_POST['difficulte'] ?? 'moyen',
        (int)($_POST['id_marathon'] ?? 0)
    );

    if ($controller->modifierParcours($p, $id)) {
        header('Location: listParcours.php');
        exit;
    } else {
        $error = "Erreur lors de la modification du parcours.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Modifier Parcours #<?php echo $id; ?> — BarchaThon</title>
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
        .actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:28px; }
        .btn { text-decoration:none; border:0; border-radius:14px; padding:13px 20px; font-weight:700; cursor:pointer; font-size:1rem; }
        .btn-save { background:linear-gradient(135deg,var(--sun),#f59e0b); color:var(--ink); }
        .btn-secondary { background:#edf2f7; color:var(--ink); }
        @media(max-width:700px){ .grid{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<div class="wrap">
    <a class="back-link" href="listParcours.php">← Retour à la liste</a>
    <div class="panel">
        <div class="panel-header">
            <div class="panel-icon">✏️</div>
            <div><h1>Modifier le Parcours #<?php echo $id; ?></h1></div>
        </div>
        <form method="POST" novalidate>
            <div class="grid">
                <div class="field full">
                    <label for="id_marathon">Marathon associé</label>
                    <select id="id_marathon" name="id_marathon">
                        <?php foreach ($marathons as $m): ?>
                            <option value="<?php echo $m['id_marathon']; ?>" <?php echo $data['id_marathon']==$m['id_marathon']?'selected':''; ?>>
                                <?php echo htmlspecialchars($m['nom_marathon']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="feedback" id="marathonFeedback"></div>
                </div>
                <div class="field full">
                    <label for="nom_parcours">Nom du Parcours</label>
                    <input type="text" id="nom_parcours" name="nom_parcours" value="<?php echo htmlspecialchars($data['nom_parcours']); ?>">
                    <div class="feedback" id="nomFeedback"></div>
                </div>
                <div class="field">
                    <label for="point_depart">Point de Départ</label>
                    <input type="text" id="point_depart" name="point_depart" value="<?php echo htmlspecialchars($data['point_depart']); ?>">
                    <div class="feedback" id="departFeedback"></div>
                </div>
                <div class="field">
                    <label for="point_arrivee">Point d'Arrivée</label>
                    <input type="text" id="point_arrivee" name="point_arrivee" value="<?php echo htmlspecialchars($data['point_arrivee']); ?>">
                    <div class="feedback" id="arriveeFeedback"></div>
                </div>
                <div class="field">
                    <label for="distance">Distance (km)</label>
                    <input type="number" id="distance" name="distance" min="0.1" step="0.01" value="<?php echo htmlspecialchars($data['distance']); ?>">
                    <div class="feedback" id="distanceFeedback"></div>
                </div>
                <div class="field">
                    <label for="difficulte">Difficulté</label>
                    <select id="difficulte" name="difficulte">
                        <?php foreach (['facile','moyen','difficile'] as $d): ?>
                            <option value="<?php echo $d; ?>" <?php echo $data['difficulte']===$d?'selected':''; ?>>
                                <?php echo ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$d]; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="feedback" id="difficulteFeedback"></div>
                </div>
            </div>
            <div class="actions">
                <button type="submit" class="btn btn-save">💾 Sauvegarder</button>
                <a href="listParcours.php" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');
    const fields = {
        marathon: document.getElementById('id_marathon'),
        nom: document.getElementById('nom_parcours'),
        depart: document.getElementById('point_depart'),
        arrivee: document.getElementById('point_arrivee'),
        distance: document.getElementById('distance'),
        difficulte: document.getElementById('difficulte')
    };

    function setFeedback(id, msg, type) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = msg;
        el.className = 'feedback ' + (type || '');
    }

    // Au moins une lettre obligatoire (lettres + chiffres OK, mais pas QUE des chiffres)
    const alphaNumRegex = /^(?=.*[A-Za-zÀ-ÖØ-öø-ÿ])[A-Za-zÀ-ÖØ-öø-ÿ0-9\s\-'\.]+$/;

    function validateMarathon() {
        if (!fields.marathon.value) { setFeedback('marathonFeedback', '❌ Veuillez choisir un marathon.', 'error'); return false; }
        setFeedback('marathonFeedback', '✅ Marathon sélectionné.', 'success'); return true;
    }

    function validateNom() {
        const v = fields.nom.value.trim();
        if (v.length === 0) { setFeedback('nomFeedback', '❌ Le nom du parcours est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('nomFeedback', '❌ Le nom doit contenir au moins 3 caractères.', 'error'); return false; }
        if (!alphaNumRegex.test(v)) { setFeedback('nomFeedback', '❌ Le nom doit contenir au moins une lettre (pas uniquement des chiffres).', 'error'); return false; }
        setFeedback('nomFeedback', '✅ Nom valide.', 'success'); return true;
    }

    function validateDepart() {
        const v = fields.depart.value.trim();
        if (v.length === 0) { setFeedback('departFeedback', '❌ Le point de départ est obligatoire.', 'error'); return false; }
        if (v.length < 2) { setFeedback('departFeedback', '❌ Le point de départ doit contenir au moins 2 caractères.', 'error'); return false; }
        setFeedback('departFeedback', '✅ Point de départ valide.', 'success'); return true;
    }

    function validateArrivee() {
        const v = fields.arrivee.value.trim();
        if (v.length === 0) { setFeedback('arriveeFeedback', "❌ Le point d'arrivée est obligatoire.", 'error'); return false; }
        if (v.length < 2) { setFeedback('arriveeFeedback', "❌ Le point d'arrivée doit contenir au moins 2 caractères.", 'error'); return false; }
        setFeedback('arriveeFeedback', "✅ Point d'arrivée valide.", 'success'); return true;
    }

    function validateDistance() {
        const raw = fields.distance.value.trim();
        if (raw === '') { setFeedback('distanceFeedback', '❌ La distance est obligatoire.', 'error'); return false; }
        if (!/^\d+(\.\d+)?$/.test(raw)) { setFeedback('distanceFeedback', '❌ Veuillez saisir uniquement des chiffres (ex: 10 ou 10.5).', 'error'); return false; }
        if (parseFloat(raw) <= 0) { setFeedback('distanceFeedback', '❌ La distance doit être un nombre positif supérieur à 0.', 'error'); return false; }
        setFeedback('distanceFeedback', '✅ Distance valide.', 'success'); return true;
    }

    function validateDifficulte() {
        if (!fields.difficulte.value) { setFeedback('difficulteFeedback', '❌ Veuillez choisir une difficulté.', 'error'); return false; }
        setFeedback('difficulteFeedback', '✅ Difficulté sélectionnée.', 'success'); return true;
    }

    // Bloquer les lettres dans le champ distance
    fields.distance.addEventListener('keypress', function (e) {
        if (!/[\d\.]/.test(e.key)) e.preventDefault();
        if (e.key === '.' && this.value.includes('.')) e.preventDefault();
    });

    fields.marathon.addEventListener('change', validateMarathon);
    fields.nom.addEventListener('input', validateNom);
    fields.nom.addEventListener('blur', validateNom);
    fields.depart.addEventListener('input', validateDepart);
    fields.depart.addEventListener('blur', validateDepart);
    fields.arrivee.addEventListener('input', validateArrivee);
    fields.arrivee.addEventListener('blur', validateArrivee);
    fields.distance.addEventListener('input', validateDistance);
    fields.distance.addEventListener('blur', validateDistance);
    fields.difficulte.addEventListener('change', validateDifficulte);

    form.addEventListener('submit', function (e) {
        const valid = [validateMarathon(), validateNom(), validateDepart(), validateArrivee(), validateDistance(), validateDifficulte()];
        if (valid.includes(false)) e.preventDefault();
    });
});
</script>
</body>
</html>
