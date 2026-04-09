<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un sponsoring</title>
    <style>
        :root {
            --ink:#102a43;
            --teal:#0f766e;
            --sun:#ffb703;
            --bg:#f4fbfb;
            --card:#ffffff;
            --muted:#627d98;
            --line:#d9e2ec;
        }
        * { box-sizing:border-box; }
        body { margin:0; font-family:"Segoe UI",sans-serif; color:var(--ink); background:linear-gradient(180deg,#fefaf0 0%,var(--bg)100%); }
        .page { width:min(980px,calc(100% - 32px)); margin:0 auto; padding:28px 0 56px; }
        .card { background:var(--card); border-radius:28px; padding:28px; box-shadow:0 18px 40px rgba(16,42,67,.08); border:1px solid rgba(16,42,67,.08); }
        h1 { margin:0 0 12px; font-size:2.2rem; }
        p { color:var(--muted); line-height:1.7; margin:0 0 24px; }
        .grid { display:grid; gap:18px; grid-template-columns:repeat(2,minmax(0,1fr)); }
        .field { display:grid; gap:8px; }
        label { font-weight:700; }
        input, select, .button-group { width:100%; padding:12px 14px; border-radius:14px; border:1px solid var(--line); background:#f8fafb; color:var(--ink); }
        .button-group { display:flex; gap:12px; }
        .button-group a { display:inline-flex; align-items:center; justify-content:center; padding:0 14px; border-radius:14px; background:linear-gradient(135deg,var(--teal),#14b8a6); color:#fff; text-decoration:none; font-weight:700; min-height:44px; }
        .full-width { grid-column:1 / -1; }
        .actions { display:flex; flex-wrap:wrap; gap:12px; margin-top:24px; }
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:12px 18px; border-radius:14px; font-weight:700; border:0; cursor:pointer; text-decoration:none; }
        .btn-primary { background:linear-gradient(135deg,var(--teal),#14b8a6); color:#fff; }
        .btn-secondary { background:#fff; color:var(--ink); border:1px solid rgba(16,42,67,.12); }
        .error { color:#d92d20; font-size:0.9rem; display:block; margin-top:4px; }
        @media (max-width:760px) { .grid { grid-template-columns:1fr; } }
    </style>
</head>
<body>
    <?php
    include '../../config.php';

    $selectedSponsor = null;
    $selectedMarathon = null;
    $nomSponsoring = '';
    $dateDebut = '';
    $dateFin = '';
    $montant = '';
    $etat = 'Actif';
    $idSponsor = null;
    $idMarathon = null;
    $idSponsoring = null;

    $db = config::getConnexion();

    if (isset($_GET['id'])) {
        $idSponsoring = $_GET['id'];
        $stmt = $db->prepare("SELECT * FROM sponsoring WHERE idSponsoring = ?");
        $stmt->execute([$idSponsoring]);
        $sponsoring = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($sponsoring) {
            $nomSponsoring = $sponsoring['nomSponsoring'];
            $dateDebut = $sponsoring['dateDebut'];
            $dateFin = $sponsoring['dateFin'];
            $montant = $sponsoring['montant'];
            $etat = $sponsoring['etat'];
            $idSponsor = $sponsoring['idSponsor'];
            $idMarathon = $sponsoring['idMarathon'];
        }
    }

    if (isset($_GET['idSponsor'])) {
        $idSponsor = $_GET['idSponsor'];
    }
    if (isset($_GET['idMarathon'])) {
        $idMarathon = $_GET['idMarathon'];
    }

    if ($idSponsor) {
        $stmt = $db->prepare("SELECT nom FROM sponsor WHERE idSponsor = ?");
        $stmt->execute([$idSponsor]);
        $selectedSponsor = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($idMarathon) {
        $stmt = $db->prepare("SELECT nom_marathon FROM marathon WHERE id_marathon = ?");
        $stmt->execute([$idMarathon]);
        $selectedMarathon = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    ?>
    <div class="page">
        <div class="card">
            <h1>Modifier un sponsoring</h1>
            <p>Formulaire pour modifier un contrat de sponsoring existant.</p>
            <form id="sponsoringForm" method="post" action="modifySponsoring_process.php">
                <?php if ($idSponsoring): ?>
                    <input type="hidden" name="idSponsoring" value="<?php echo htmlspecialchars($idSponsoring); ?>">
                <?php endif; ?>
                <?php if ($selectedSponsor): ?>
                    <input type="hidden" name="idSponsor" value="<?php echo htmlspecialchars($idSponsor); ?>">
                <?php endif; ?>
                <?php if ($selectedMarathon): ?>
                    <input type="hidden" name="idMarathon" value="<?php echo htmlspecialchars($idMarathon); ?>">
                <?php endif; ?>
                <div class="grid">
                    <div class="field full-width">
                        <label for="name">Nom Sponsoring</label>
                        <input id="name" name="name" type="text" placeholder="Nom du sponsoring" value="<?php echo htmlspecialchars($nomSponsoring); ?>">
                        <span id="name-error" class="error"></span>
                    </div>
                    <div class="field full-width">
                        <label>Sponsor</label>
                        <?php if ($selectedSponsor): ?>
                            <input type="text" value="<?php echo htmlspecialchars($selectedSponsor['nom']); ?>" readonly>
                            <a class="btn btn-secondary" href="chooseSponsorSponsoring.php<?php echo isset($idMarathon) ? '?idMarathon=' . $idMarathon : ''; ?>">Changer</a>
                        <?php else: ?>
                            <div class="button-group">
                                <a href="chooseSponsorSponsoring.php<?php echo isset($idMarathon) ? '?idMarathon=' . $idMarathon : ''; ?>">Choisir un sponsor</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="field full-width">
                        <label>Marathon sponsorisé</label>
                        <?php if ($selectedMarathon): ?>
                            <input type="text" value="<?php echo htmlspecialchars($selectedMarathon['nom_marathon']); ?>" readonly>
                            <a class="btn btn-secondary" href="chooseMarathon.php<?php echo isset($idSponsor) ? '?idSponsor=' . $idSponsor : ''; ?>">Changer</a>
                        <?php else: ?>
                            <div class="button-group">
                                <a href="chooseMarathon.php<?php echo isset($idSponsor) ? '?idSponsor=' . $idSponsor : ''; ?>">Choisir un marathon</a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="field">
                        <label for="dateDebut">Date Début</label>
                        <input id="dateDebut" name="dateDebut" type="date" value="<?php echo htmlspecialchars($dateDebut); ?>">
                        <span id="date-error" class="error"></span>
                    </div>
                    <div class="field">
                        <label for="dateFin">Date Fin</label>
                        <input id="dateFin" name="dateFin" type="date" value="<?php echo htmlspecialchars($dateFin); ?>">
                    </div>
                    <div class="field">
                        <label for="montant">Montant</label>
                        <input id="montant" name="montant" type="number" step="0.01" placeholder="12000.00" value="<?php echo htmlspecialchars($montant); ?>">
                        <span id="montant-error" class="error"></span>
                    </div>
                    <div class="field">
                        <label for="etat">État</label>
                        <select id="etat" name="etat">
                            <option<?php echo $etat === 'Actif' ? ' selected' : ''; ?>>Actif</option>
                            <option<?php echo $etat === 'Terminé' ? ' selected' : ''; ?>>Terminé</option>
                            <option<?php echo $etat === 'Annulé' ? ' selected' : ''; ?>>Annulé</option>
                        </select>
                    </div>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" type="submit">Mettre à jour</button>
                    <a class="btn btn-secondary" href="mesSponsors.php">Retour</a>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('sponsoringForm').addEventListener('submit', function(event) {
            var nameField = document.getElementById('name');
            var dateDebutField = document.getElementById('dateDebut');
            var dateFinField = document.getElementById('dateFin');
            var montantField = document.getElementById('montant');

            var nameError = document.getElementById('name-error');
            var dateError = document.getElementById('date-error');
            var montantError = document.getElementById('montant-error');

            nameError.textContent = '';
            dateError.textContent = '';
            montantError.textContent = '';

            var name = nameField.value.trim();
            var dateDebut = dateDebutField.value;
            var dateFin = dateFinField.value;
            var montant = montantField.value.trim();

            var hasError = false;

            if (name.length === 0 || name.length >= 51) {
                nameError.textContent = 'Le nom du sponsoring doit contenir entre 1 et 50 caractères.';
                hasError = true;
            }
            if (dateDebut === '' || dateFin === '') {
                dateError.textContent = 'Les deux dates doivent être renseignées.';
                hasError = true;
            } else if (dateDebut >= dateFin) {
                dateError.textContent = 'La date de début doit être inférieure à la date de fin.';
                hasError = true;
            }
            if (montant === '' || isNaN(montant) || parseFloat(montant) <= 0) {
                montantError.textContent = 'Le montant doit être un nombre strictement supérieur à 0.';
                hasError = true;
            }

            if (hasError) {
                event.preventDefault();
            }
        });
    </script>
</body>
</html>