<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

if (!isAdmin()) { header('Location: accueil.php'); exit; }

$mCtrl = new MarathonController();
$pCtrl = new ParcoursController();

// Suppression uniquement
if (isset($_GET['del_m'])) { $mCtrl->supprimerMarathon((int)$_GET['del_m']); header('Location: dashboard.php?tab=marathons'); exit; }
if (isset($_GET['del_p'])) { $pCtrl->supprimerParcours((int)$_GET['del_p']); header('Location: dashboard.php?tab=parcours'); exit; }

$activeTab = $_GET['tab'] ?? 'home';
$marathons  = $mCtrl->afficherMarathon();
$parcours   = $pCtrl->afficherParcours();
$statsM     = $mCtrl->statsNbMarathonsDispo();
$statsP     = $pCtrl->statsParcours();
$regions    = $mCtrl->getRegions();

$searchM = $_GET['searchM'] ?? '';
$filterRegion = $_GET['region'] ?? '';
if ($searchM !== '') $marathons = $mCtrl->rechercherMarathon($searchM);
elseif ($filterRegion !== '') $marathons = $mCtrl->filtrerMarathon($filterRegion);

$searchP = $_GET['searchP'] ?? '';
$filterDiff = $_GET['difficulte'] ?? '';
if ($searchP !== '') $parcours = $pCtrl->rechercherParcoursParNom($searchP);
elseif ($filterDiff !== '') $parcours = $pCtrl->filtrerParcours($filterDiff);

$user = getCurrentUser();
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Dashboard Admin — BarchaThon</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --ink:#102a43; --teal:#0f766e; --sun:#ffb703;
            --coral:#e76f51; --bg:#f4fbfb;
        }
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            font-family:"Segoe UI",sans-serif;
            color:var(--ink);
            background:linear-gradient(180deg,#fefaf0,var(--bg));
            min-height:100vh;
        }

        /* ====== LAYOUT ====== */
        .layout {
            display:flex;
            min-height:100vh;
            align-items:stretch;
        }

        /* ====== SIDEBAR ====== */
        .sidebar {
            width:260px;
            min-width:260px;
            flex-shrink:0;
            background:linear-gradient(180deg,#0b2032 0%,#12314a 100%);
            color:white;
            padding:24px 16px;
            position:sticky;
            top:0;
            height:100vh;
            overflow-y:auto;
            display:flex;
            flex-direction:column;
            gap:4px;
        }
        .sb-brand {
            display:flex; flex-direction:column; align-items:center;
            gap:8px; padding-bottom:18px;
            border-bottom:1px solid rgba(255,255,255,.12);
            margin-bottom:8px;
        }
        .sb-brand img { width:70px; border-radius:16px; }
        .sb-brand-name { font-weight:900; font-size:0.95rem; text-align:center; }
        .sb-brand-sub { color:rgba(255,255,255,.55); font-size:0.72rem; text-align:center; line-height:1.5; }
        .sb-link {
            display:flex; align-items:center; gap:10px;
            text-decoration:none; color:rgba(255,255,255,.85);
            border-radius:14px; padding:11px 14px;
            font-weight:700; font-size:0.88rem;
            transition:background .18s;
            border:1px solid transparent;
        }
        .sb-link:hover { background:rgba(255,255,255,.09); color:white; }
        .sb-link.active {
            background:linear-gradient(135deg,var(--teal),#14b8a6);
            color:white;
            box-shadow:0 6px 18px rgba(15,118,110,.3);
        }
        .sb-link i { width:20px; text-align:center; font-size:0.95rem; }
        .sb-divider { border:none; border-top:1px solid rgba(255,255,255,.1); margin:6px 0; }
        .sb-logout { margin-top:auto; }

        /* ====== MAIN ====== */
        .main {
            flex:1;
            min-width:0;
            padding:30px 28px;
            overflow-x:hidden;
        }

        .main-header { margin-bottom:24px; }
        .main-header h1 { font-size:1.85rem; margin-bottom:4px; }
        .main-header p { color:#627d98; font-size:0.92rem; }

        .page-head {
            display:flex; justify-content:space-between;
            align-items:flex-start; flex-wrap:wrap;
            gap:12px; margin-bottom:24px;
        }
        .page-head h1 { font-size:1.85rem; margin-bottom:4px; }
        .page-head p { color:#627d98; font-size:0.92rem; }

        /* STATS */
        .stats-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(148px,1fr));
            gap:14px; margin-bottom:20px;
        }
        .stat-card {
            background:white; border-radius:18px; padding:18px 14px;
            box-shadow:0 8px 22px rgba(16,42,67,.07);
            border:1px solid rgba(16,42,67,.06); text-align:center;
        }
        .stat-val { font-size:1.95rem; font-weight:900; color:var(--teal); }
        .stat-lbl { color:#627d98; font-size:0.82rem; margin-top:4px; }

        /* PANEL */
        .panel {
            background:white; border-radius:20px; padding:20px;
            box-shadow:0 8px 26px rgba(16,42,67,.07);
            border:1px solid rgba(16,42,67,.07);
            margin-bottom:14px;
        }
        .panel-header {
            display:flex; justify-content:space-between; align-items:center;
            margin-bottom:16px; flex-wrap:wrap; gap:10px;
        }
        .panel-header h2 { font-size:1.2rem; font-weight:800; }

        /* FILTRE */
        .filter-bar { display:flex; gap:10px; flex-wrap:wrap; }
        .filter-bar input,
        .filter-bar select {
            border-radius:11px; border:1px solid #cbd5e1;
            padding:9px 13px; font:inherit;
            flex:1; min-width:160px; font-size:0.88rem; background:white;
        }
        .filter-bar input:focus,
        .filter-bar select:focus { outline:none; border-color:var(--teal); }

        /* BOUTONS */
        .btn {
            text-decoration:none; padding:9px 14px; border-radius:11px;
            font-weight:700; border:none; cursor:pointer;
            display:inline-flex; align-items:center; gap:7px;
            font-size:0.88rem; transition:transform .15s, opacity .15s;
            white-space:nowrap;
        }
        .btn:hover { transform:translateY(-1px); opacity:.9; }
        .btn-del    { background:var(--coral); color:white; }
        .btn-danger { background:var(--coral); color:white; }
        .btn-pdf    { background:#1a1a2e; color:white; }
        .btn-secondary { background:#f1f5f9; color:var(--ink); border:1px solid #cbd5e1; }

        /* TAGS */
        .tag      { display:inline-block; padding:4px 10px; border-radius:999px; background:rgba(15,118,110,.1); color:var(--teal); font-weight:700; font-size:.8rem; }
        .tag-no   { background:rgba(231,111,81,.1); color:var(--coral); }
        .tag-easy { background:rgba(16,185,129,.1); color:#059669; }
        .tag-med  { background:rgba(245,158,11,.1);  color:#d97706; }
        .tag-hard { background:rgba(231,111,81,.1);  color:var(--coral); }

        /* TABLEAU */
        .table-shell { overflow-x:auto; }
        table { width:100%; border-collapse:collapse; min-width:680px; }
        th {
            background:#102a43; color:white;
            padding:12px 10px; text-align:left;
            font-size:0.82rem; letter-spacing:.02em;
        }
        th:first-child { border-radius:10px 0 0 0; }
        th:last-child  { border-radius:0 10px 0 0; }
        td {
            padding:11px 10px; border-bottom:1px solid #e6edf3;
            vertical-align:middle; font-size:0.87rem;
        }
        tr:hover td { background:#f8fafc; }
        .marathon-img { width:54px; height:38px; object-fit:cover; border-radius:7px; }
        .table-actions { display:flex; gap:7px; }

        /* USER THUMB — exactement comme backoffice_User.html */
        .user-thumb {
            width:36px; height:36px; border-radius:50%;
            display:inline-grid; place-items:center;
            color:#fff; font-weight:900; font-size:.8rem;
            background:linear-gradient(135deg,var(--teal),var(--sun));
        }

        /* TOOLBAR UTILISATEURS — exactement comme backoffice_User.html */
        .toolbar {
            display:flex; flex-wrap:wrap; gap:16px;
            align-items:flex-start; margin-bottom:18px;
        }
        .search-box { flex:1; min-width:220px; }
        .search-box input {
            width:100%; border:1.5px solid #cbd5e1; border-radius:11px;
            padding:10px 14px; font:inherit; font-size:0.9rem; background:white;
        }
        .search-box input:focus { outline:none; border-color:var(--teal); }
        .filter-group { display:flex; flex-wrap:wrap; gap:12px; }
        .filter-group label {
            display:flex; flex-direction:column; gap:5px;
            font-size:0.83rem; font-weight:700; color:#627d98;
        }
        .filter-group select {
            border:1.5px solid #cbd5e1; border-radius:10px;
            padding:8px 12px; font:inherit; font-size:0.88rem;
            background:white; min-width:140px;
        }
        .filter-group select:focus { outline:none; border-color:var(--teal); }
        .section-note { color:#627d98; font-size:0.82rem; margin-top:14px; text-align:right; }

        /* HOME CARDS */
        .home-cards {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(175px,1fr));
            gap:16px;
        }
        .home-card {
            background:white; border-radius:20px; padding:26px 16px;
            text-align:center;
            box-shadow:0 8px 24px rgba(16,42,67,.07);
            border:1px solid rgba(16,42,67,.06);
            transition:transform .2s, box-shadow .2s;
            text-decoration:none; color:var(--ink); display:block;
        }
        .home-card:hover { transform:translateY(-4px); box-shadow:0 16px 36px rgba(16,42,67,.12); }
        .home-card i { font-size:2rem; color:var(--teal); margin-bottom:10px; display:block; }
        .home-card h3 { font-size:1rem; margin-bottom:5px; }
        .home-card p  { color:#627d98; font-size:0.82rem; }

        /* MODAL CONFIRM — comme backoffice_User.html */
        .modal-overlay {
            display:none; position:fixed; inset:0;
            background:rgba(16,42,67,.45); z-index:1000;
            align-items:center; justify-content:center;
            backdrop-filter:blur(3px);
        }
        .modal-overlay.open { display:flex; }
        .modal-box {
            background:white; border-radius:22px; padding:32px 28px;
            width:min(420px,calc(100% - 32px));
            box-shadow:0 24px 60px rgba(16,42,67,.18);
            text-align:center;
        }
        .modal-box h3 { font-size:1.25rem; margin-bottom:12px; }
        .modal-box p  { color:#627d98; margin-bottom:22px; }
        .modal-actions { display:flex; gap:12px; justify-content:center; }

        @media(max-width:900px){
            .sidebar { display:none; }
            .main { padding:20px 14px; }
        }
    </style>
</head>
<body>
<div class="layout">

<!-- ====== SIDEBAR ====== -->
<aside class="sidebar">
    <div class="sb-brand">
        <img src="images/logobarchathon.jpg" alt="BarchaThon">
        <div class="sb-brand-name">BarchaThon</div>
        <div class="sb-brand-sub">Admin Back Office<br><?php echo htmlspecialchars($user['nom']); ?> — admin</div>
    </div>

    <a class="sb-link <?php echo $activeTab==='home'?'active':''; ?>" href="dashboard.php?tab=home">
        <i class="fas fa-th-large"></i> Dashboard
    </a>
    <a class="sb-link <?php echo $activeTab==='utilisateurs'?'active':''; ?>" href="dashboard.php?tab=utilisateurs">
        <i class="fas fa-users"></i> Utilisateurs
    </a>
    <a class="sb-link <?php echo $activeTab==='marathons'?'active':''; ?>" href="dashboard.php?tab=marathons">
        <i class="fas fa-running"></i> Marathons
    </a>
    <a class="sb-link <?php echo $activeTab==='parcours'?'active':''; ?>" href="dashboard.php?tab=parcours">
        <i class="fas fa-map-marked-alt"></i> Parcours
    </a>
    <a class="sb-link <?php echo $activeTab==='stands'?'active':''; ?>" href="dashboard.php?tab=stands">
        <i class="fas fa-store-alt"></i> Stands
    </a>
    <a class="sb-link <?php echo $activeTab==='sponsors'?'active':''; ?>" href="dashboard.php?tab=sponsors">
        <i class="fas fa-handshake"></i> Sponsors
    </a>

    <div class="sb-logout">
        <hr style="border:none;border-top:1px solid rgba(255,255,255,.1);margin:10px 0;">
        <a class="sb-link" href="accueil.php">
            <i class="fas fa-arrow-left"></i> Retour
        </a>
        <a class="sb-link" href="logout.php">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
        </a>
    </div>
</aside>

<!-- ====== CONTENU PRINCIPAL ====== -->
<main class="main">

<?php if ($activeTab === 'home'): ?>

    <div class="main-header">
        <h1>⚙️ Administration générale</h1>
        <p>Consultez et gérez les marathons et parcours de BarchaThon.</p>
    </div>
    <div class="stats-grid">
        <div class="stat-card"><div class="stat-val"><?php echo count($mCtrl->afficherMarathon()); ?></div><div class="stat-lbl">Marathons actifs</div></div>
        <div class="stat-card"><div class="stat-val"><?php echo number_format((float)($statsM['total_places']??0)); ?></div><div class="stat-lbl">Places disponibles</div></div>
        <div class="stat-card"><div class="stat-val"><?php echo (int)($statsP['total']??0); ?></div><div class="stat-lbl">Parcours</div></div>
        <div class="stat-card"><div class="stat-val">3</div><div class="stat-lbl">Stands partenaires</div></div>
    </div>
    <div class="home-cards">
        <a class="home-card" href="dashboard.php?tab=utilisateurs"><i class="fas fa-users"></i><h3>Utilisateurs</h3><p>Consulter et supprimer</p></a>
        <a class="home-card" href="dashboard.php?tab=marathons"><i class="fas fa-running"></i><h3>Marathons</h3><p>Consulter et supprimer</p></a>
        <a class="home-card" href="dashboard.php?tab=parcours"><i class="fas fa-map-marked-alt"></i><h3>Parcours</h3><p>Consulter et supprimer</p></a>
        <a class="home-card" href="dashboard.php?tab=stands"><i class="fas fa-store-alt"></i><h3>Stands</h3><p>Voir les stands</p></a>
        <a class="home-card" href="dashboard.php?tab=sponsors"><i class="fas fa-handshake"></i><h3>Sponsors</h3><p>Voir les sponsors</p></a>
    </div>

<?php elseif ($activeTab === 'utilisateurs'): ?>

    <!-- ===== UTILISATEURS : exactement comme backoffice_User.html ===== -->
    <div class="main-header">
        <h1>Section utilisateurs</h1>
        <p>Vue administrative pour consulter et gérer les utilisateurs.</p>
    </div>

    <div class="panel">
        <h2 style="font-size:1.3rem;font-weight:800;margin-bottom:18px;">Utilisateurs</h2>

        <!-- TOOLBAR : recherche + filtres -->
        <div class="toolbar">
            <div class="search-box">
                <input type="search" placeholder="Rechercher un utilisateur, un email ou un pays">
            </div>
            <div class="filter-group">
                <label>
                    Filtrer par rôle
                    <select>
                        <option>Tout</option>
                        <option>Participant</option>
                        <option>Organisateur</option>
                        <option>Admin</option>
                    </select>
                </label>
                <label>
                    Filtrer par pays
                    <select>
                        <option>Tout</option>
                        <option>Tunisie</option>
                        <option>France</option>
                        <option>Maroc</option>
                    </select>
                </label>
            </div>
        </div>

        <!-- TABLEAU UTILISATEURS -->
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Photo</th>
                        <th>Nom</th>
                        <th>Nom utilisateur</th>
                        <th>Rôle</th>
                        <th>Email</th>
                        <th>Pays</th>
                        <th>Ville / zone</th>
                        <th>Téléphone</th>
                        <th>Occupation</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>1</td>
                        <td><span class="user-thumb">A</span></td>
                        <td>Ahmed Ben Ali</td>
                        <td>ahmed_runner</td>
                        <td><span class="tag">participant</span></td>
                        <td>ahmed@email.com</td>
                        <td>Tunisie</td>
                        <td>Tunis, Lac 2</td>
                        <td>12345678</td>
                        <td>Employé</td>
                        <td>
                            <button class="btn btn-danger" onclick="openConfirm('Supprimer cet utilisateur ?')">Supprimer</button>
                        </td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><span class="user-thumb">S</span></td>
                        <td>Sami Trabelsi</td>
                        <td>sami_org</td>
                        <td><span class="tag">organisateur</span></td>
                        <td>sami@marathon.tn</td>
                        <td>Tunisie</td>
                        <td>Sousse</td>
                        <td>98765432</td>
                        <td>Employé</td>
                        <td>
                            <button class="btn btn-danger" onclick="openConfirm('Supprimer cet utilisateur ?')">Supprimer</button>
                        </td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><span class="user-thumb">F</span></td>
                        <td>Fatma Jaziri</td>
                        <td>fatma_run</td>
                        <td><span class="tag">participant</span></td>
                        <td>fatma@email.com</td>
                        <td>Tunisie</td>
                        <td>Sfax</td>
                        <td>54321098</td>
                        <td>Etudiant</td>
                        <td>
                            <button class="btn btn-danger" onclick="openConfirm('Supprimer cet utilisateur ?')">Supprimer</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="section-note">Affichage des utilisateurs.</div>
    </div>

<?php elseif ($activeTab === 'marathons'): ?>

    <div class="page-head">
        <div>
            <h1>🏃 Gestion des Marathons</h1>
            <p>Liste complète — consultation et suppression uniquement.</p>
        </div>
        <a class="btn btn-pdf" href="#"><i class="fas fa-file-pdf"></i> Exporter PDF</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val"><?php echo count($mCtrl->afficherMarathon()); ?></div>
            <div class="stat-lbl">Marathons affichés</div>
        </div>
        <div class="stat-card">
            <div class="stat-val"><?php echo number_format((float)($statsM['total_places']??0)); ?></div>
            <div class="stat-lbl">Places disponibles</div>
        </div>
    </div>

    <div class="panel">
        <form method="GET" id="fmM" class="filter-bar">
            <input type="hidden" name="tab" value="marathons">
            <input type="text" name="searchM" id="sM" placeholder="🔍 Rechercher par nom..." value="<?php echo htmlspecialchars($searchM); ?>">
            <select name="region" id="rM">
                <option value="">Toutes les régions</option>
                <?php foreach ($regions as $r): ?>
                    <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $filterRegion===$r?'selected':''; ?>><?php echo htmlspecialchars($r); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Liste des Marathons</h2>
            <span class="tag"><?php echo count($marathons); ?> résultats</span>
        </div>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Nom du Marathon</th>
                        <th>Organisateur</th>
                        <th>Région</th>
                        <th>Date</th>
                        <th>Places dispo</th>
                        <th>Prix (TND)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($marathons)): ?>
                        <tr><td colspan="9" style="text-align:center;padding:28px;color:#627d98;">Aucun marathon trouvé.</td></tr>
                    <?php else: foreach($marathons as $m2): ?>
                    <tr>
                        <td><strong>#<?php echo $m2['id_marathon']; ?></strong></td>
                        <td>
                            <img class="marathon-img"
                                 src="../FrontOffice/<?php echo htmlspecialchars($m2['image_marathon']); ?>"
                                 onerror="this.src='../FrontOffice/images/img1.png'" alt="">
                        </td>
                        <td><strong><?php echo htmlspecialchars($m2['nom_marathon']); ?></strong></td>
                        <td><?php echo htmlspecialchars($m2['organisateur_marathon']); ?></td>
                        <td><span class="tag">📍 <?php echo htmlspecialchars($m2['region_marathon']); ?></span></td>
                        <td><?php echo date('d/m/Y', strtotime($m2['date_marathon'])); ?></td>
                        <td>
                            <?php if($m2['nb_places_dispo'] > 0): ?>
                                <span class="tag">✅ <?php echo $m2['nb_places_dispo']; ?></span>
                            <?php else: ?>
                                <span class="tag tag-no">❌ Complet</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?php echo number_format($m2['prix_marathon'],2); ?></strong></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-del"
                                   href="dashboard.php?tab=marathons&del_m=<?php echo $m2['id_marathon']; ?>"
                                   onclick="return confirm('Supprimer ce marathon ?')">🗑️ Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($activeTab === 'parcours'): ?>

    <div class="page-head">
        <div>
            <h1>🗺️ Gestion des Parcours</h1>
            <p>Liste complète — consultation et suppression uniquement.</p>
        </div>
        <a class="btn btn-pdf" href="#"><i class="fas fa-file-pdf"></i> Exporter PDF</a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-val"><?php echo (int)($statsP['total']??0); ?></div>
            <div class="stat-lbl">Total Parcours</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #059669;">
            <div class="stat-val" style="color:#059669;"><?php echo (int)($statsP['facile']??0); ?></div>
            <div class="stat-lbl">🟢 Faciles</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #d97706;">
            <div class="stat-val" style="color:#d97706;"><?php echo (int)($statsP['moyen']??0); ?></div>
            <div class="stat-lbl">🟡 Moyens</div>
        </div>
        <div class="stat-card" style="border-top:3px solid var(--coral);">
            <div class="stat-val" style="color:var(--coral);"><?php echo (int)($statsP['difficile']??0); ?></div>
            <div class="stat-lbl">🔴 Difficiles</div>
        </div>
    </div>

    <div class="panel">
        <form method="GET" id="fmP" class="filter-bar">
            <input type="hidden" name="tab" value="parcours">
            <input type="text" name="searchP" id="sP" placeholder="🔍 Rechercher par nom de parcours..." value="<?php echo htmlspecialchars($searchP); ?>">
            <select name="difficulte" id="dP">
                <option value="">Toutes les difficultés</option>
                <option value="facile"    <?php echo $filterDiff==='facile'?'selected':''; ?>>🟢 Facile</option>
                <option value="moyen"     <?php echo $filterDiff==='moyen'?'selected':''; ?>>🟡 Moyen</option>
                <option value="difficile" <?php echo $filterDiff==='difficile'?'selected':''; ?>>🔴 Difficile</option>
            </select>
        </form>
    </div>

    <div class="panel">
        <div class="panel-header">
            <h2>Liste des Parcours</h2>
            <span class="tag"><?php echo count($parcours); ?> résultats</span>
        </div>
        <div class="table-shell">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom du Parcours</th>
                        <th>Point Départ</th>
                        <th>Point Arrivée</th>
                        <th>Distance (km)</th>
                        <th>Difficulté</th>
                        <th>Marathon</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($parcours)): ?>
                        <tr><td colspan="8" style="text-align:center;padding:28px;color:#627d98;">Aucun parcours trouvé.</td></tr>
                    <?php else: foreach($parcours as $p2):
                        $dc = ['facile'=>'tag-easy','moyen'=>'tag-med','difficile'=>'tag-hard'][$p2['difficulte']]??'';
                        $di = ['facile'=>'🟢','moyen'=>'🟡','difficile'=>'🔴'][$p2['difficulte']]??'';
                    ?>
                    <tr>
                        <td><strong>#<?php echo $p2['id_parcours']; ?></strong></td>
                        <td><strong><?php echo htmlspecialchars($p2['nom_parcours']); ?></strong></td>
                        <td>📍 <?php echo htmlspecialchars($p2['point_depart']); ?></td>
                        <td>🏁 <?php echo htmlspecialchars($p2['point_arrivee']); ?></td>
                        <td><strong><?php echo number_format((float)$p2['distance'],2); ?> km</strong></td>
                        <td><span class="tag <?php echo $dc; ?>"><?php echo $di.' '.htmlspecialchars($p2['difficulte']); ?></span></td>
                        <td><span class="tag">🏃 <?php echo htmlspecialchars($p2['nom_marathon']); ?></span></td>
                        <td>
                            <div class="table-actions">
                                <a class="btn btn-del"
                                   href="dashboard.php?tab=parcours&del_p=<?php echo $p2['id_parcours']; ?>"
                                   onclick="return confirm('Supprimer ce parcours ?')">🗑️ Supprimer</a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($activeTab === 'stands'): ?>

    <div class="main-header">
        <h1>🏪 Stands</h1>
        <p>Liste des stands partenaires.</p>
    </div>
    <div class="panel">
        <p style="color:#627d98;padding:20px;text-align:center;">Module Stands en cours d'intégration.</p>
    </div>

<?php elseif ($activeTab === 'sponsors'): ?>

    <div class="main-header">
        <h1>🤝 Sponsors</h1>
        <p>Liste des sponsors partenaires.</p>
    </div>
    <div class="panel">
        <p style="color:#627d98;padding:20px;text-align:center;">Module Sponsors en cours d'intégration.</p>
    </div>

<?php endif; ?>

</main>
</div><!-- /.layout -->

<!-- MODAL CONFIRMATION -->
<div id="confirm-modal" class="modal-overlay">
    <div class="modal-box">
        <h3>Confirmation</h3>
        <p id="confirm-message"></p>
        <div class="modal-actions">
            <button class="btn btn-danger" onclick="closeConfirm()">Oui, supprimer</button>
            <button class="btn btn-secondary" onclick="closeConfirm()">Annuler</button>
        </div>
    </div>
</div>

<script>
function openConfirm(msg) {
    document.getElementById('confirm-message').textContent = msg;
    document.getElementById('confirm-modal').classList.add('open');
}
function closeConfirm() {
    document.getElementById('confirm-modal').classList.remove('open');
}

<?php if ($activeTab === 'marathons'): ?>
document.getElementById('rM').addEventListener('change', function(){ document.getElementById('fmM').submit(); });
let tM;
document.getElementById('sM').addEventListener('input', function(){
    clearTimeout(tM); tM = setTimeout(function(){ document.getElementById('fmM').submit(); }, 500);
});
<?php endif; ?>

<?php if ($activeTab === 'parcours'): ?>
document.getElementById('dP').addEventListener('change', function(){ document.getElementById('fmP').submit(); });
let tP;
document.getElementById('sP').addEventListener('input', function(){
    clearTimeout(tP); tP = setTimeout(function(){ document.getElementById('fmP').submit(); }, 600);
});
<?php endif; ?>
</script>
</body>
</html>
