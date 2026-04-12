<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/ParcoursController.php';

$controller = new ParcoursController();

// CRUD READ (liste simple)
$parcours = $controller->afficherParcours();

// stats (optionnel mais CRUD-friendly)
$stats = $controller->statsParcours();

$currentPage = 'catalogue';
?
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Parcours — BarchaThon</title>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:radial-gradient(circle at top left,rgba(15,118,110,.1),transparent 30%),linear-gradient(180deg,#f0fff8,#f7f1e5); }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }
        .hero-strip { display:grid; grid-template-columns:1.3fr .7fr; gap:18px; margin-bottom:26px; }
        .hero-card { background:linear-gradient(135deg,#0f766e,#102a43); color:white; border-radius:28px; padding:32px; box-shadow:0 20px 40px rgba(15,118,110,.2); position:relative; overflow:hidden; }
        .hero-card::after { content:'🗺️'; position:absolute; right:24px; bottom:10px; font-size:6rem; opacity:.12; }
        .hero-card h1 { font-size:clamp(1.6rem,3.5vw,2.6rem); line-height:1.1; margin-bottom:10px; }
        .hero-card p { opacity:.9; line-height:1.7; }
        .hero-stats { background:white; border-radius:28px; padding:24px; box-shadow:0 12px 28px rgba(16,42,67,.07); display:grid; grid-template-columns:1fr 1fr; gap:14px; align-content:center; }
        .mini-stat { text-align:center; }
        .mini-stat .val { font-size:1.9rem; font-weight:900; color:var(--teal); }
        .mini-stat .lbl { color:#627d98; font-size:0.82rem; margin-top:3px; }
        .filter-section { background:white; border-radius:18px; padding:18px 20px; margin-bottom:24px; box-shadow:0 6px 18px rgba(16,42,67,.06); }
        .filter-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .filter-bar input, .filter-bar select { border-radius:12px; border:1px solid #cbd5e1; padding:10px 14px; font:inherit; flex:1; min-width:180px; }
        .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color:var(--teal); }
        .section-title { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .section-title h2 { font-size:1.8rem; }
        .count-badge { background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:5px 14px; font-weight:700; font-size:.86rem; }
        .catalog { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:22px; }
        .card { background:rgba(255,255,255,.92); border:1px solid rgba(16,42,67,.08); border-radius:24px; box-shadow:0 14px 36px rgba(16,42,67,.08); overflow:hidden; transition:transform .22s,box-shadow .22s; }
        .card:hover { transform:translateY(-5px); box-shadow:0 24px 50px rgba(16,42,67,.13); }
        .diff-banner { padding:12px 20px; font-weight:900; font-size:0.88rem; letter-spacing:.04em; }
        .diff-facile { background:linear-gradient(135deg,#d1fae5,#a7f3d0); color:#065f46; }
        .diff-moyen { background:linear-gradient(135deg,#fef9c3,#fde68a); color:#92400e; }
        .diff-difficile { background:linear-gradient(135deg,#fee2e2,#fecaca); color:#991b1b; }
        .card-body { padding:20px; }
        .marathon-tag { display:inline-block; background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:5px 12px; font-size:0.82rem; font-weight:700; margin-bottom:12px; }
        .card h3 { font-size:1.18rem; margin-bottom:14px; }
        .route-info { background:#f8fafc; border-radius:14px; padding:14px; display:grid; gap:8px; font-size:0.92rem; margin-bottom:14px; }
        .route-row { display:flex; align-items:center; gap:8px; color:#486581; }
        .dist-row { display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid #e6edf3; }
        .dist-val { font-size:1.5rem; font-weight:900; color:var(--teal); }
        .empty-state { text-align:center; padding:60px; color:#627d98; }
        @media(max-width:768px){ .hero-strip{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">
    <section class="hero-strip">
        <div class="hero-card">
            <h1>Parcours de Course</h1>
            <p>Découvrez tous les tracés disponibles par niveau de difficulté.</p>
        </div>
        <div class="hero-stats">
            <div class="mini-stat"><div class="val"><?php echo (int)($stats['total']??0); ?></div><div class="lbl">Total parcours</div></div>
            <div class="mini-stat"><div class="val" style="color:#059669;"><?php echo (int)($stats['facile']??0); ?></div><div class="lbl">🟢 Faciles</div></div>
            <div class="mini-stat"><div class="val" style="color:#d97706;"><?php echo (int)($stats['moyen']??0); ?></div><div class="lbl">🟡 Moyens</div></div>
            <div class="mini-stat"><div class="val" style="color:var(--coral);"><?php echo (int)($stats['difficile']??0); ?></div><div class="lbl">🔴 Difficiles</div></div>
        </div>
    </section>

    <div class="filter-section">
        <form method="GET" id="filterForm" class="filter-bar">
            <input type="text" name="search" id="searchInput" placeholder="🔍 Rechercher par nom de parcours..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="difficulte" id="diffSelect">
                <option value="">Toutes les difficultés</option>
                <option value="facile" <?php echo $filterDiff==='facile'?'selected':''; ?>>🟢 Facile</option>
                <option value="moyen" <?php echo $filterDiff==='moyen'?'selected':''; ?>>🟡 Moyen</option>
                <option value="difficile" <?php echo $filterDiff==='difficile'?'selected':''; ?>>🔴 Difficile</option>
            </select>
        </form>
    </div>

    <div class="section-title">
        <h2>Tous les Parcours</h2>
        <span class="count-badge"><?php echo count($parcours); ?> résultats</span>
    </div>

    <?php if (empty($parcours)): ?>
        <div class="empty-state"><div style="font-size:3rem;margin-bottom:14px;">🗺️</div><h3>Aucun parcours trouvé</h3></div>
    <?php else: ?>
    <section class="catalog">
        <?php foreach ($parcours as $p):
            $dc = ['facile'=>'diff-facile','moyen'=>'diff-moyen','difficile'=>'diff-difficile'][$p['difficulte']] ?? 'diff-moyen';
            $dl = ['facile'=>'🟢 Facile','moyen'=>'🟡 Moyen','difficile'=>'🔴 Difficile'][$p['difficulte']] ?? $p['difficulte'];
        ?>
        <article class="card">
            <div class="diff-banner <?php echo $dc; ?>"><?php echo $dl; ?></div>
            <div class="card-body">
                <span class="marathon-tag">🏃 <?php echo htmlspecialchars($p['nom_marathon']); ?></span>
                <h3><?php echo htmlspecialchars($p['nom_parcours']); ?></h3>
                <div class="route-info">
                    <div class="route-row">📍 <span><strong>Départ :</strong> <?php echo htmlspecialchars($p['point_depart']); ?></span></div>
                    <div class="route-row">🏁 <span><strong>Arrivée :</strong> <?php echo htmlspecialchars($p['point_arrivee']); ?></span></div>
                </div>
                <div class="dist-row">
                    <div><div class="dist-val"><?php echo number_format((float)$p['distance'],2); ?> km</div><div style="color:#627d98;font-size:.82rem;">Distance totale</div></div>
                    <span style="font-size:2rem;">🏅</span>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
<script>
document.getElementById('diffSelect').addEventListener('change',function(){document.getElementById('filterForm').submit();});
let t; document.getElementById('searchInput').addEventListener('input',function(){clearTimeout(t);t=setTimeout(function(){document.getElementById('filterForm').submit();},500);});
</script>
</body>
</html>
