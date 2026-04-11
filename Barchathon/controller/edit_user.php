<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id_user'] ?? 0);
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $nom_user = trim($_POST['nom_user'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'participant';
    $age = !empty($_POST['age']) ? (int)$_POST['age'] : null;
    $poids = !empty($_POST['poids']) ? (float)$_POST['poids'] : null;
    $taille = !empty($_POST['taille']) ? (int)$_POST['taille'] : null;
    $tel = trim($_POST['tel'] ?? '') ?: null;
    $pays = trim($_POST['pays'] ?? '') ?: null;
    $ville = trim($_POST['ville'] ?? '') ?: null;
    $occupation = trim($_POST['occupation'] ?? '') ?: null;

    if (!in_array($role, ['admin', 'participant', 'organisateur'])) {
        $role = 'participant';
    }

    if ($nom_complet === '' || $nom_user === '' || $email === '') {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Veuillez remplir tous les champs obligatoires.'));
        exit;
    }

    if (strlen($nom_complet) < 3) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Le nom complet doit contenir au moins 3 caracteres.'));
        exit;
    }

    if (strlen($nom_user) < 3 || !preg_match('/^[a-zA-Z0-9_]+$/', $nom_user)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Le nom d\'utilisateur doit contenir au moins 3 caracteres (lettres, chiffres, underscores).'));
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Veuillez entrer une adresse email valide.'));
        exit;
    }

    if ($tel !== null && !preg_match('/^\d{8}$/', $tel)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Le numero de telephone doit contenir exactement 8 chiffres.'));
        exit;
    }

    if ($age !== null && ($age < 1 || $age > 120)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('L\'age doit etre entre 1 et 120.'));
        exit;
    }

    if ($poids !== null && ($poids < 1 || $poids > 500)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Le poids doit etre entre 1 et 500 kg.'));
        exit;
    }

    if ($taille !== null && ($taille < 1 || $taille > 300)) {
        header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('La taille doit etre entre 1 et 300 cm.'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE `user` SET nom_complet=?, nom_user=?, email=?, role=?, age=?, poids=?, taille=?, tel=?, pays=?, ville=?, occupation=? WHERE id_user=?");
        $stmt->execute([$nom_complet, $nom_user, $email, $role, $age, $poids, $taille, $tel, $pays, $ville, $occupation, $id]);
        header('Location: backoffice_User.php?success=' . urlencode('Utilisateur modifie avec succes.'));
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Ce nom d\'utilisateur existe deja.'));
        } else {
            header('Location: edit_user.php?id=' . $id . '&error=' . urlencode('Erreur lors de la mise a jour.'));
        }
    }
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: backoffice_User.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM `user` WHERE id_user = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: backoffice_User.php?error=' . urlencode('Utilisateur introuvable.'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier utilisateur</title>
    <link rel="stylesheet" href="../view/assets/css/style.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="brand">
                <img class="brand-badge" src="../view/assets/images/logo_barchathon.jpg" alt="Logo Barchathon">
                <div>
                    <strong>Admin Back Office</strong><br>
                    <small>Modifier utilisateur</small>
                </div>
            </div>
            <nav class="side-nav">
                <a class="side-link" href="dashboard.php">Dashboard</a>
                <a class="side-link active" href="backoffice_User.php">Utilisateurs</a>
                <a class="side-link" href="#">Marathons</a>
                <a class="side-link" href="#">Parcours</a>
                <a class="side-link" href="#">Parametres</a>
                <a class="side-link" href="logout.php">Deconnexion</a>
            </nav>
        </aside>
        <main class="content">
            <div class="mobile-nav">
                <a class="btn btn-secondary" href="backoffice_User.php">Retour</a>
            </div>
            <div class="head">
                <div>
                    <h1>Modifier : <?= htmlspecialchars($user['nom_complet']) ?></h1>
                    <div class="muted">Modification des informations de l'utilisateur #<?= $user['id_user'] ?>.</div>
                </div>
            </div>
            <section class="section-card fade-in">
                <?php if (!empty($_GET['error'])): ?>
                    <div class="error-msg"><?= htmlspecialchars($_GET['error']) ?></div>
                <?php endif; ?>
                <form method="POST" action="edit_user.php" data-validate>
                    <input type="hidden" name="id_user" value="<?= $user['id_user'] ?>">
                    <div class="form-grid">
                        <div class="field full-width">
                            <label for="name">Nom complet</label>
                            <input id="name" name="nom_complet" type="text" value="<?= htmlspecialchars($user['nom_complet']) ?>" required minlength="3">
                        </div>
                        <div class="field">
                            <label for="username">Nom d utilisateur</label>
                            <input id="username" name="nom_user" type="text" value="<?= htmlspecialchars($user['nom_user']) ?>" required minlength="3" pattern="[a-zA-Z0-9_]+">
                        </div>
                        <div class="field">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="participant" <?= $user['role'] === 'participant' ? 'selected' : '' ?>>Participant</option>
                                <option value="organisateur" <?= $user['role'] === 'organisateur' ? 'selected' : '' ?>>Organisateur</option>
                                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                            </select>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" name="email" type="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="field">
                            <label for="age">Age</label>
                            <input id="age" name="age" type="number" value="<?= $user['age'] ?? '' ?>" min="1" max="120">
                        </div>
                        <div class="field">
                            <label for="weight">Poids (kg)</label>
                            <input id="weight" name="poids" type="number" value="<?= $user['poids'] ?? '' ?>" min="1" max="500" step="0.1">
                        </div>
                        <div class="field">
                            <label for="height">Taille (cm)</label>
                            <input id="height" name="taille" type="number" value="<?= $user['taille'] ?? '' ?>" min="1" max="300">
                        </div>
                        <div class="field">
                            <label for="phone">Telephone</label>
                            <input id="phone" name="tel" type="tel" value="<?= htmlspecialchars($user['tel'] ?? '') ?>" pattern="[0-9]{8}" title="Le numero doit contenir exactement 8 chiffres">
                        </div>
                        <div class="field">
                            <label for="country">Pays</label>
                            <input id="country" name="pays" type="text" value="<?= htmlspecialchars($user['pays'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="city">Ville / adresse exacte</label>
                            <input id="city" name="ville" type="text" value="<?= htmlspecialchars($user['ville'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="occupation">Occupation</label>
                            <select id="occupation" name="occupation">
                                <option value="">-</option>
                                <option value="Etudiant" <?= ($user['occupation'] ?? '') === 'Etudiant' ? 'selected' : '' ?>>Etudiant</option>
                                <option value="Employe" <?= ($user['occupation'] ?? '') === 'Employe' ? 'selected' : '' ?>>Employe</option>
                                <option value="Retraite" <?= ($user['occupation'] ?? '') === 'Retraite' ? 'selected' : '' ?>>Retraite</option>
                            </select>
                        </div>
                    </div>
                    <div class="actions">
                        <button class="btn btn-primary" type="submit">Enregistrer</button>
                        <a class="btn btn-secondary" href="backoffice_User.php">Annuler</a>
                    </div>
                </form>
            </section>
        </main>
    </div>

    <div id="feedback-modal" class="modal-overlay">
        <div class="modal-box">
            <div id="feedback-icon" class="feedback-icon success"></div>
            <p id="feedback-message"></p>
        </div>
    </div>

    <script src="../view/assets/js/app.js"></script>
</body>
</html>
