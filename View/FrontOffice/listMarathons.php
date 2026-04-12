<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/partials/session.php';
require_once __DIR__ . '/../../Controller/MarathonController.php';
$controller = new MarathonController();

$search = $_GET['search'] ?? '';
$filterRegion = $_GET['region'] ?? '';

if ($search !== '') $marathons = $controller->rechercherMarathon($search);
elseif ($filterRegion !== '') $marathons = $controller->filtrerMarathon($filterRegion);
else $marathons = $controller->afficherMarathon();

$stats = $controller->statsNbMarathonsDispo();
$regions = $controller->getRegions();
$currentPage = 'catalogue';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Catalogue Marathons — BarchaThon</title>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:radial-gradient(circle at top right,rgba(255,183,3,.15),transparent 30%),linear-gradient(180deg,#fff9ef,#f2fbfb); }
        .page { width:min(1180px,calc(100% - 32px)); margin:0 auto; padding:28px 0 0; }

        /* HERO */
        .hero-strip { display:grid; grid-template-columns:1.3fr .7fr; gap:18px; margin-bottom:26px; }
        .hero-card { background:linear-gradient(135deg,#102a43,#0f766e); color:white; border-radius:28px; padding:32px; box-shadow:0 20px 40px rgba(16,42,67,.15); position:relative; overflow:hidden; }
        .hero-card::after { content:'🏃'; position:absolute; right:24px; bottom:10px; font-size:6rem; opacity:.12; }
        .hero-card h1 { font-size:clamp(1.6rem,3.5vw,2.6rem); line-height:1.1; margin-bottom:10px; }
        .hero-card p { opacity:.9; line-height:1.7; }
        .hero-stats { background:white; border-radius:28px; padding:24px; box-shadow:0 12px 28px rgba(16,42,67,.07); display:grid; grid-template-columns:1fr 1fr; gap:16px; align-content:center; }
        .mini-stat { text-align:center; }
        .mini-stat .val { font-size:2rem; font-weight:900; color:var(--teal); }
        .mini-stat .lbl { color:#627d98; font-size:0.84rem; margin-top:3px; }

        /* FILTRE */
        .filter-section { background:white; border-radius:18px; padding:18px 20px; margin-bottom:24px; box-shadow:0 6px 18px rgba(16,42,67,.06); }
        .filter-bar { display:flex; gap:12px; flex-wrap:wrap; align-items:center; }
        .filter-bar input, .filter-bar select { border-radius:12px; border:1px solid #cbd5e1; padding:10px 14px; font:inherit; flex:1; min-width:180px; }
        .filter-bar input:focus, .filter-bar select:focus { outline:none; border-color:var(--teal); }

        /* CATALOGUE */
        .section-title { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
        .section-title h2 { font-size:1.8rem; }
        .count-badge { background:rgba(15,118,110,.1); color:var(--teal); border-radius:999px; padding:5px 14px; font-weight:700; font-size:.86rem; }
        .catalog { display:grid; grid-template-columns:repeat(auto-fill,minmax(300px,1fr)); gap:24px; }

        /* CARD - image agrandie + effet clic */
        .card {
            background:rgba(255,255,255,.92); border:1px solid rgba(16,42,67,.08);
            border-radius:24px; box-shadow:0 14px 36px rgba(16,42,67,.08);
            overflow:hidden; cursor:pointer;
            transform:translateY(16px); opacity:0; animation:rise 0.6s ease forwards;
            transition:transform .22s, box-shadow .22s;
            text-decoration:none; color:inherit; display:block;
        }
        .card:nth-child(2){animation-delay:.07s} .card:nth-child(3){animation-delay:.14s}
        .card:nth-child(4){animation-delay:.21s} .card:nth-child(5){animation-delay:.28s}
        .card:hover { transform:translateY(-6px); box-shadow:0 28px 56px rgba(16,42,67,.15); }
        .card:active { transform:scale(0.97); box-shadow:0 8px 20px rgba(16,42,67,.12); }
        .card-img-wrap { position:relative; }
        /* IMAGE AGRANDIE */
        .card img { width:100%; height:240px; object-fit:cover; display:block; }
        .card-id { position:absolute; top:12px; left:12px; background:rgba(16,42,67,.8); color:white; border-radius:8px; padding:5px 12px; font-size:0.82rem; font-weight:700; backdrop-filter:blur(6px); }
        .card-body { padding:20px; }
        .pill-row { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:10px; }
        .pill { font-size:0.8rem; padding:5px 10px; border-radius:999px; background:rgba(15,118,110,.1); color:var(--teal); font-weight:700; }
        .pill-sun { background:rgba(255,183,3,.15); color:#92400e; }
        .card h3 { font-size:1.2rem; margin-bottom:10px; }
        .meta { font-size:0.9rem; color:#486581; display:grid; gap:5px; margin-bottom:14px; }
        .card-footer { display:flex; justify-content:space-between; align-items:center; padding-top:12px; border-top:1px solid #e6edf3; }
        .price { font-size:1.2rem; font-weight:900; color:var(--coral); }
        .btn-detail { display:inline-block; padding:10px 16px; border-radius:11px; font-weight:700; background:linear-gradient(135deg,var(--teal),#14b8a6); color:white; font-size:0.88rem; }
        .empty-state { text-align:center; padding:60px 20px; color:#627d98; }
        @keyframes rise { to { opacity:1; transform:translateY(0); } }
        @media(max-width:768px){ .hero-strip{grid-template-columns:1fr;} }
    </style>
</head>
<body>
<?php require __DIR__ . '/partials/topbar.php'; ?>
<div class="page">

    <section class="hero-strip">
        <div class="hero-card">
            <h1>Catalogue des Marathons</h1>
            <p>Explorez tous les événements de course en Tunisie.</p>
        </div>
        <div class="hero-stats">
            <div class="mini-stat">
                <div class="val"><?php echo count($marathons); ?></div>
                <div class="lbl">Marathons affichés</div>
            </div>
            <div class="mini-stat">
                <div class="val"><?php echo number_format((float)($stats['total_places']??0)); ?></div>
                <div class="lbl">Places disponibles</div>
            </div>
        </div>
    </section>

    <div class="filter-section">
        <form method="GET" id="filterForm" class="filter-bar">
            <input type="text" name="search" id="searchInput" placeholder="🔍 Rechercher par nom..." value="<?php echo htmlspecialchars($search); ?>">
            <select name="region" id="regionSelect">
                <option value="">🌍 Toutes les régions</option>
                <?php foreach ($regions as $r): ?>
                    <option value="<?php echo htmlspecialchars($r); ?>" <?php echo $filterRegion===$r?'selected':''; ?>><?php echo htmlspecialchars($r); ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <div class="section-title">
        <h2>Tous les Marathons</h2>
        <span class="count-badge"><?php echo count($marathons); ?> résultats</span>
    </div>

    <?php if (empty($marathons)): ?>
        <div class="empty-state">
            <div style="font-size:3rem;margin-bottom:14px;">🏃</div>
            <h3>Aucun marathon trouvé</h3>
        </div>
    <?php else: ?>
    <section class="catalog">
        <?php foreach ($marathons as $m): ?>
        <a class="card" href="detailMarathon.php?id=<?php echo $m['id_marathon']; ?>">
            <div class="card-img-wrap">
                <img src="<?php echo htmlspecialchars($m['image_marathon']); ?>" alt="<?php echo htmlspecialchars($m['nom_marathon']); ?>" onerror="this.src='images/img1.png'">
                <span class="card-id">#<?php echo $m['id_marathon']; ?></span>
            </div>
            <div class="card-body">
                <div class="pill-row">
                    <span class="pill">📍 <?php echo htmlspecialchars($m['region_marathon']); ?></span>
                    <span class="pill pill-sun"><?php echo $m['nb_places_dispo']>0?'✅ '.$m['nb_places_dispo'].' places':'❌ Complet'; ?></span>
                </div>
                <h3><?php echo htmlspecialchars($m['nom_marathon']); ?></h3>
                <div class="meta">
                    <span>👤 <?php echo htmlspecialchars($m['organisateur_marathon']); ?></span>
                    <span>📅 <?php echo date('d/m/Y',strtotime($m['date_marathon'])); ?></span>
                </div>
                <div class="card-footer">
                    <span class="price"><?php echo number_format($m['prix_marathon'],2); ?> TND</span>
                    <span class="btn-detail">Voir détail →</span>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/partials/footer.php'; ?>
<script>
document.getElementById('regionSelect').addEventListener('change',function(){document.getElementById('filterForm').submit();});
let t; document.getElementById('searchInput').addEventListener('input',function(){clearTimeout(t);t=setTimeout(function(){document.getElementById('filterForm').submit();},500);});
</script>
</body>
</html>
