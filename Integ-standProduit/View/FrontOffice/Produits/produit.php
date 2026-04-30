<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../partials/session.php';
include '../../../Controller/CommandeController.php';
include '../../../Controller/LigneCommandeController.php';
include '../../../Controller/ProduitController.php';

$prodCtrl = new ProduitController();

$stand_id = isset($_GET['stand_id']) ? (int)$_GET['stand_id'] : null;
$parcours_id = isset($_GET['parcours_id']) ? (int)$_GET['parcours_id'] : null;
if (!$stand_id) {
    die('Stand ID requis.');
}

$products = $prodCtrl->afficherProduitsParStand($stand_id);

// Convert to array with id as key for compatibility
$productsAssoc = [];
foreach ($products as $prod) {
    $id = $prod['id_produit'] ?? $prod['ID_produit'] ?? 0;
    if ($id) {
        $productsAssoc[$id] = [
            'nom' => $prod['nom_produit'] ?? $prod['Nom_produit'] ?? 'Inconnu',
            'prix' => $prod['prix_produit'] ?? $prod['Prix_produit'] ?? 0,
            'qte_stock' => $prod['qte_stock'] ?? $prod['Qte_stock'] ?? 0,
            'en_out_stock' => $prod['en_out_stock'] ?? $prod['En_out_stock'] ?? 0,
            'image' => $prod['image'] ?? null,
            'type' => $prod['type'] ?? 'Produit'
        ];
    }
}
$products = $productsAssoc;

$stands = [
    // Assuming we have stand names, but for now, just use stand_id
];
$stand_name = "Stand #$stand_id"; // Placeholder, can fetch from DB if needed

if ($stand_id) {
    // Already filtered
}

$currentPage = 'catalogue';
$user = getCurrentUser();
if (!$user) {
    header('Location: ../login.php');
    exit;
}
$role = $user['role'] ?? 'visiteur';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$message = '';
$success = '';
$cart = &$_SESSION['cart'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'], $_POST['product_id'], $_POST['quantity'])) {
        $productId = (int) $_POST['product_id'];
        $quantity = max(0, (int) $_POST['quantity']);

        if (!isset($products[$productId])) {
            $message = 'Produit invalide.';
        } else {
            $item = $products[$productId];
            if ($quantity <= 0) {
                unset($cart[$productId]);
                $success = "Produit {$item['nom']} supprimé du panier.";
            } else {
                $cart[$productId] = [
                    'idproduit' => $productId,
                    'nom' => $item['nom'],
                    'prix' => $item['prix'],
                    'quantite' => $quantity,
                ];
                $success = "Quantité pour {$item['nom']} mise à jour à {$quantity}.";
            }
        }
    }

    if (isset($_POST['delete_product_id'])) {
        $deleteId = (int) $_POST['delete_product_id'];
        if (isset($cart[$deleteId])) {
            unset($cart[$deleteId]);
            $success = 'Produit supprimé du panier.';
        }
    }

    if (isset($_POST['clear_cart'])) {
        $_SESSION['cart'] = [];
        $cart = &$_SESSION['cart'];
        $success = 'Panier vidé.';
    }

    if (isset($_POST['validate_order'])) {
        if (empty($cart)) {
            $message = 'Le panier est vide, impossible de valider la commande.';
        } else {
            $total = 0;
            foreach ($cart as $item) {
                $total += $item['quantite'] * $item['prix'];
            }

            if ($total == 0) {
                // Commande gratuite
                $commandeC = new CommandeController();
                $ligneC = new LigneCommandeController();
                $userId = $user['id_user'] ?? $user['id'];
                $commande = new Commande(null, $userId, $stand_id, date('Y-m-d H:i:s'), 'paye', $total);
                $newCommandeId = $commandeC->addCommande($commande);

                if ($newCommandeId) {
                    foreach ($cart as $item) {
                        $ligne = new LigneCommande(null, $newCommandeId, $item['idproduit'], $item['quantite'], $item['prix']);
                        $ligneC->addLigneCommande($ligne);
                    }

                    $_SESSION['cart'] = [];
                    header('Location: produit.php?created=' . urlencode($newCommandeId) . '&success=' . urlencode('Commande gratuite validée !'));
                    exit;
                }

                $message = 'Erreur lors de la création de la commande. Veuillez réessayer.';
            } else {
                // Redirection vers paiement
                header('Location: ../paiement.php?type=commande&id=0&montant=' . $total . '&stand_id=' . $stand_id);
                exit;
            }
        }
    }

    if (isset($_POST['update_cart']) && !isset($_POST['delete_product_id']) && isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $id => $qty) {
            $id = (int) $id;
            $qty = max(0, (int) $qty);
            if ($qty > 0 && isset($products[$id])) {
                $cart[$id]['quantite'] = $qty;
            } else {
                unset($cart[$id]);
            }
        }
        $success = 'Panier mis à jour.';
    }
}

$cartTotal = 0;
foreach ($cart as $item) {
    $cartTotal += $item['quantite'] * $item['prix'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $stand_name ? "Produits du $stand_name" : 'Catalogue produits'; ?> — BarchaThon</title>
    <style>
        :root { --ink:#102a43; --teal:#0f766e; --sun:#ffb703; --coral:#e76f51; }
        * { box-sizing:border-box; margin:0; padding:0; }
        body { font-family:"Segoe UI",sans-serif; color:var(--ink); background:#f8fafc; }
        .page { width:min(1200px,calc(100% - 32px)); margin:0 auto; padding:28px 0 60px; }

        .back-link { display:inline-flex; align-items:center; gap:8px; text-decoration:none; color:var(--teal); font-weight:700; margin-bottom:16px; padding:9px 16px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(16,42,67,.05); font-size:0.92rem; transition:transform .2s; }
        .back-link:hover { transform:translateY(-2px); }

        /* Clean Hero */
        .clean-hero { background: white; border-radius: 24px; padding: 50px 60px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 10px 40px rgba(0,0,0,0.03); margin-bottom: 40px; position: relative; overflow: hidden; }
        .clean-hero-text { max-width: 55%; position: relative; z-index: 2; }
        .clean-hero-text h1 { font-size: 3.2rem; color: var(--ink); font-weight: 900; text-transform: uppercase; letter-spacing: -1px; margin-bottom: 15px; line-height: 1.1; }
        .clean-hero-text p { color: #64748b; font-size: 1.1rem; line-height: 1.6; margin-bottom: 25px; }
        .clean-hero-img { font-size: 10rem; opacity: 0.9; position: relative; z-index: 2; filter: drop-shadow(0 20px 30px rgba(15,118,110,0.2)); transform: rotate(-10deg); transition: transform 0.5s; }
        .clean-hero:hover .clean-hero-img { transform: rotate(0deg) scale(1.05); }
        .clean-hero::after { content:''; position:absolute; top:-50%; right:-10%; width:400px; height:400px; background:radial-gradient(circle, rgba(20,184,166,0.1) 0%, rgba(255,255,255,0) 70%); border-radius:50%; z-index:1; }

        /* Promo Blocks */
        .promo-blocks { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 50px; }
        .promo-box { padding: 30px 20px; text-align: center; border-radius: 16px; display: flex; flex-direction: column; justify-content: center; box-shadow: 0 10px 25px rgba(15,118,110,0.15); color: white; transition: transform .3s ease; }
        .promo-box:hover { transform: translateY(-5px); }
        .promo-box h3 { font-size: 1.6rem; margin-bottom: 8px; font-weight: 800; }
        .promo-box p { font-size: 0.95rem; opacity: 0.9; font-weight: 500; }

        /* Layout */
        .panel { background: transparent; padding: 0; box-shadow: none; }
        .grid { display:grid; grid-template-columns:3fr 1fr; gap:30px; align-items:start; }
        
        .section-title-center { text-align: center; font-size: 1.2rem; color: #64748b; letter-spacing: 2px; margin-bottom: 40px; font-weight: 600; text-transform: uppercase; }

        /* Product Grid */
        /* Minimal circular product cards like the photo */
        .product-grid { display:flex; flex-wrap:wrap; gap:30px; justify-content:center; }
        
        /* Effect to hide/dim other products when hovering one */
        .product-grid:hover .product-card-item {
            opacity: 0.15;
            filter: grayscale(80%) blur(2px);
            transition: all 0.4s ease;
        }
        .product-grid .product-card-item:hover {
            opacity: 1 !important;
            filter: grayscale(0%) blur(0) !important;
            z-index: 50;
        }

        .product-card-item { 
            background: transparent; 
            border: none; 
            padding: 10px; 
            text-align: center; 
            cursor: pointer; 
            position: relative; 
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); 
            width: 220px;
            box-shadow: none;
        }
        .product-card-item:hover { transform: translateY(-10px) scale(1.1); }
        
        .product-img { 
            width: 200px; 
            height: 200px; 
            background: #fff; 
            border-radius: 50%; 
            margin: 0 auto 15px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 4rem; 
            transition: all 0.4s ease; 
            overflow: hidden; 
            border: 4px solid #e2e8f0; 
            padding: 5px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.05);
        }
        
        /* Colorful Borders - rotating colors like the photo */
        .product-card-item:nth-child(4n+1) .product-img { border-color: #ec4899; } /* Pink */
        .product-card-item:nth-child(4n+2) .product-img { border-color: #3b82f6; } /* Blue */
        .product-card-item:nth-child(4n+3) .product-img { border-color: #f59e0b; } /* Yellow */
        .product-card-item:nth-child(4n+4) .product-img { border-color: #10b981; } /* Green */
        
        .product-img img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        
        .product-card-item:hover .product-img { 
            box-shadow: 0 12px 30px rgba(0,0,0,0.15);
            transform: rotate(3deg);
        }
        
        .product-name { 
            font-weight: 600; 
            font-size: 0.95rem; 
            color: var(--ink); 
            margin-bottom: 5px; 
            transition: color 0.3s;
        }
        .product-card-item:hover .product-name { color: var(--teal); }
        
        .product-price { color: #64748b; font-weight: 700; font-size: 1rem; }
        
        .badge-round { position: absolute; top: 5px; right: 20px; background: #10b981; color: white; width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; font-weight: 800; z-index: 5; }
        .badge-round.out { background: var(--coral); }

        /* Tooltip style window - Positioned to the right & BIGGER */
        .product-info-tooltip {
            position: absolute;
            top: 50%;
            left: 110%; /* Position to the right of the card */
            transform: translateY(-50%) scale(0.8);
            width: 320px; /* Much bigger */
            background: rgba(255, 255, 255, 0.99);
            backdrop-filter: blur(15px);
            border-radius: 30px;
            padding: 35px;
            box-shadow: 30px 30px 80px rgba(16, 42, 67, 0.25);
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 200; 
            text-align: left;
            border: 1px solid rgba(15, 118, 110, 0.15);
            pointer-events: none;
        }
        .product-card-item:hover .product-info-tooltip {
            opacity: 1;
            visibility: visible;
            transform: translateY(-50%) scale(1);
        }
        .tooltip-row { margin-bottom: 15px; display: flex; flex-direction: column; }
        .tooltip-label { color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; margin-bottom: 4px; }
        .tooltip-value { color: var(--ink); font-weight: 700; font-size: 1.1rem; }
        .tooltip-stock { font-weight: 900; margin-top: 10px; display: inline-block; padding: 8px 18px; border-radius: 12px; font-size: 0.9rem; text-transform: uppercase; }
        .stock-yes { background: #dcfce7; color: #166534; }
        .stock-no { background: #fee2e2; color: #991b1b; }

        /* Cart */
        .cart-card { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); border: none; position: sticky; top: 100px; }
        .cart-card h2 { font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; color: var(--ink); border-bottom: 2px solid #f1f5f9; padding-bottom: 15px; margin-bottom: 20px; font-weight: 800; }
        
        input[type=number] { width: 70px; border-radius: 10px; border: 1px solid #cbd5e1; padding: 8px; text-align: center; font-weight: 600; color: var(--ink); }
        input[type=number]:focus { outline: none; border-color: var(--teal); box-shadow: 0 0 0 3px rgba(15,118,110,0.1); }
        
        .cart-item { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:15px 0; border-bottom: 1px dashed #e2e8f0; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item-info { display:flex; flex-direction:column; gap:4px; }
        .cart-item-name { font-weight:700; color:var(--ink); font-size: 0.95rem; }
        .cart-item-price { color:var(--teal); font-weight: 600; font-size: 0.9rem; }
        .cart-item-actions { display:flex; align-items:center; gap:8px; }
        
        .btn-sm { padding:6px 10px; font-size:0.85rem; border-radius: 8px; }
        .btn-delete { background:#fef2f2; color:#ef4444; border: 1px solid #fecaca; }
        .btn-delete:hover { background: #ef4444; color: white; }
        
        .cart-total { margin-top:20px; font-weight:900; color:var(--ink); font-size: 1.3rem; text-align: right; border-top: 2px solid #f1f5f9; padding-top: 15px; }
        
        .btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:999px; padding:14px 24px; font-weight:800; cursor:pointer; text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.9rem; transition: all 0.2s; }
        .btn-primary { background:var(--teal); color:#fff; width: 100%; margin-top: 15px; box-shadow: 0 8px 20px rgba(15,118,110,0.2); }
        .btn-primary:hover { background: #0d665e; transform: translateY(-2px); box-shadow: 0 12px 25px rgba(15,118,110,0.3); }
        .btn-danger { background:white; color:var(--coral); border:2px solid var(--coral); width: 100%; margin-top: 10px; }
        .btn-danger:hover { background: var(--coral); color: white; }
        
        .message { padding:16px 20px; border-radius:16px; margin-bottom:24px; font-weight: 600; }
        .message.success { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
        .message.error { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }
        @keyframes fadeOut { 0% { opacity:1; } 100% { opacity:0; display:none; } }
        .message.fade-out { animation: fadeOut .5s ease-in forwards; }
        
        .btn-manipuler { position: absolute; top: 30px; right: 30px; background: white; color: var(--teal); text-decoration: none; box-shadow: 0 4px 15px rgba(0,0,0,0.08); padding: 10px 20px; border-radius: 999px; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 8px; transition: transform 0.2s; z-index: 10; }
        .btn-manipuler:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.12); }

        @media (max-width:960px) { 
            .grid { grid-template-columns:1fr; }
            .promo-blocks { grid-template-columns: 1fr; }
            .clean-hero { flex-direction: column; text-align: center; padding: 40px 20px; }
            .clean-hero-text { max-width: 100%; margin-bottom: 30px; }
            .clean-hero-img { font-size: 6rem; }
            .btn-manipuler { top: auto; bottom: 20px; right: 50%; transform: translateX(50%); }
        }
    </style>
</head>
<body>
<?php require __DIR__ . '/../partials/topbar.php'; ?>
<div class="page">
    <a class="back-link" href="../detailParcours.php?id=<?php echo $parcours_id; ?>">← Retour au parcours</a>

    <section class="clean-hero">
        <div class="clean-hero-text">
            <h1><?php echo $stand_name ? "Produits du $stand_name" : 'Catalogue produits'; ?></h1>
            <p><?php echo $stand_name ? "Découvrez notre sélection exclusive de produits d'hydratation et de nutrition pour optimiser votre marathon." : 'Choisissez vos produits et définissez les quantités. Cliquez sur Actualiser pour mettre à jour le panier.'; ?></p>
            <a href="#popular-products" class="btn btn-primary" style="display:inline-block; width:auto;">Découvrir les produits</a>
        </div>
        <div class="clean-hero-img">
            🥤
        </div>
        <?php if (isOrganisateur()): ?>
            <a href="crud-produit.php?stand_id=<?php echo $stand_id; ?>&parcours_id=<?php echo $parcours_id; ?>" class="btn-manipuler">⚙️ Manipuler Produits</a>
        <?php endif; ?>
    </section>

    <div class="promo-blocks">
        <div class="promo-box" style="background: linear-gradient(135deg, #10b981, #059669);">
            <h3>-10% Off</h3>
            <p>Sur votre première commande en ligne</p>
        </div>
        <div class="promo-box" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
            <h3>Retrait Rapide</h3>
            <p>Directement au stand du parcours</p>
        </div>
        <div class="promo-box" style="background: linear-gradient(135deg, #0f766e, #0f172a);">
            <h3>Besoin d'aide ?</h3>
            <p>Nos organisateurs sont à votre écoute</p>
        </div>
    </div>

    <section class="panel" id="popular-products">
            <?php if ($message !== ''): ?>
                <div class="message error"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if ($success !== ''): ?>
                <div class="message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="grid">
                <div>
                    <h2 class="section-title-center">Produits Populaires</h2>
                    <div class="product-grid">
                        <?php foreach ($products as $id => $product): ?>
                            <div class="product-card-item" onclick="addToCart(<?php echo $id; ?>)">
                                <div class="product-info-tooltip">
                                    <div class="tooltip-row">
                                        <span class="tooltip-label">Type</span>
                                        <span class="tooltip-value"><?php echo htmlspecialchars($product['type']); ?></span>
                                    </div>
                                    <div class="tooltip-row">
                                        <span class="tooltip-label">Nom</span>
                                        <span class="tooltip-value"><?php echo htmlspecialchars($product['nom']); ?></span>
                                    </div>
                                    <div class="tooltip-row">
                                        <span class="tooltip-label">Prix</span>
                                        <span class="tooltip-value" style="color:var(--teal);"><?php echo number_format($product['prix'], 2, ',', ' '); ?> TND</span>
                                    </div>
                                    <div class="tooltip-stock <?php echo $product['en_out_stock'] ? 'stock-yes' : 'stock-no'; ?>">
                                        <?php echo $product['en_out_stock'] ? '✅ En Stock' : '❌ Rupture'; ?>
                                    </div>
                                </div>

                                <?php if ($product['en_out_stock']): ?>
                                    <div class="badge-round">DISPO</div>
                                <?php else: ?>
                                    <div class="badge-round out">ÉPUISÉ</div>
                                <?php endif; ?>
                                
                                <div class="product-img">
                                    <?php if ($product['image'] && file_exists(__DIR__ . '/../../../uploads/produits/' . $product['image'])): ?>
                                        <img src="../../../uploads/produits/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['nom']); ?>">
                                    <?php else: ?>
                                        📦
                                    <?php endif; ?>
                                </div>
                                <div class="product-name"><?php echo htmlspecialchars($product['nom']); ?></div>
                                <div class="product-price"><?php echo number_format($product['prix'], 2, ',', ' '); ?> TND</div>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($products)): ?>
                            <p style="text-align:center; color:#64748b; font-style:italic; grid-column:1/-1; padding: 40px;">Aucun produit disponible pour ce stand actuellement.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div>
                    <div class="cart-card">
                        <h2>🛒 Panier actuel</h2>
                        <?php if (empty($cart)): ?>
                            <p style="color:#64748b; text-align:center; padding: 20px 0; font-style:italic;">Votre panier est vide.</p>
                        <?php else: ?>
                            <form method="post" id="cartForm">
                                <input type="hidden" name="update_cart" value="1">
                                <?php foreach ($cart as $id => $item): ?>
                                    <div class="cart-item">
                                        <div class="cart-item-info">
                                            <span class="cart-item-name"><?php echo htmlspecialchars($item['nom']); ?></span>
                                            <span class="cart-item-price"><?php echo number_format($item['prix'], 2, ',', ' '); ?> TND</span>
                                        </div>
                                        <div class="cart-item-actions">
                                            <input type="number" name="quantities[<?php echo $id; ?>]" value="<?php echo htmlspecialchars($item['quantite']); ?>" min="0" max="<?php echo $products[$id]['qte_stock'] ?? 999; ?>" class="cart-qty-input" />
                                            <button type="submit" name="delete_product_id" value="<?php echo $id; ?>" class="btn btn-sm btn-delete" title="Supprimer">✕</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <div class="cart-total"><?php echo number_format($cartTotal, 2, ',', ' '); ?> TND</div>
                                <div>
                                    <button type="submit" name="validate_order" class="btn btn-primary">Valider la commande</button>
                                    <button type="submit" name="clear_cart" class="btn btn-danger">Vider le panier</button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        function addToCart(productId) {
            const form = document.createElement('form');
            form.method = 'post';
            form.style.display = 'none';
            const inputId = document.createElement('input');
            inputId.name = 'product_id';
            inputId.value = productId;
            const inputQty = document.createElement('input');
            inputQty.name = 'quantity';
            inputQty.value = '1';
            const inputUpdate = document.createElement('input');
            inputUpdate.name = 'update_quantity';
            inputUpdate.value = '1';
            form.appendChild(inputId);
            form.appendChild(inputQty);
            form.appendChild(inputUpdate);
            document.body.appendChild(form);
            form.submit();
        }

        document.addEventListener('DOMContentLoaded', function () {
            const cartForm = document.getElementById('cartForm');
            if (!cartForm) return;

            const timeoutMap = new Map();
            cartForm.querySelectorAll('.cart-qty-input').forEach(function (input) {
                input.addEventListener('input', function () {
                    const id = input.name;
                    if (timeoutMap.has(id)) {
                        clearTimeout(timeoutMap.get(id));
                    }
                    timeoutMap.set(id, setTimeout(function () {
                        cartForm.submit();
                    }, 450));
                });
            });
        });

        // Auto-hide success message after 3 seconds
        document.addEventListener('DOMContentLoaded', function () {
            const successMsg = document.querySelector('.message.success');
            if (successMsg) {
                setTimeout(function () {
                    successMsg.classList.add('fade-out');
                    setTimeout(function () {
                        successMsg.style.display = 'none';
                    }, 500);
                }, 3000);
            }
        });
    </script>
</body>
</html>
