<?php
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../../Controller/StandController.php';
require_once __DIR__ . '/../../../Controller/produitcontroller.php';

// 1. Récupérer la position actuelle via GET
$lat = isset($_GET['lat']) ? (float)$_GET['lat'] : null;
$lon = isset($_GET['lon']) ? (float)$_GET['lon'] : null;

// Conditions : Si la position n'est pas disponible → afficher un message d'erreur.
if ($lat === null || $lon === null) {
    http_response_code(400);
    echo json_encode(["error" => "Position de l'utilisateur non disponible. Veuillez fournir lat et lon."]);
    exit;
}

$sCtrl = new StandController();
$pCtrl = new ProduitController();

// 2. Lire la liste des stands
$allStands = $sCtrl->listStands();
$results = [];

foreach ($allStands as $stand) {
    // 1. Géocodage
    $coords = geocodeInternal($stand['position']);
    if ($coords === null) continue;

    $lat2 = $coords[0];
    $lon2 = $coords[1];

    // 2. Calcul distance
    $dist = haversineInternal($lat, $lon, $lat2, $lon2);

    // 3. ANALYSE INTELLIGENTE (Produits et Stocks)
    $products = $pCtrl->afficherProduitsParStand($stand['ID_stand']);
    $totalProds = count($products);
    $inStockCount = 0;
    foreach ($products as $p) {
        if ($p['en_out_stock'] == 1 || stripos($p['en_out_stock'], 'dispo') !== false) {
            $inStockCount++;
        }
    }

    // 4. CALCUL DU SCORE DE PERTINENCE (Intelligence)
    // Distance (Poids fort) + Diversité (Nombre produits) + Disponibilité (En stock)
    $score = (10 / ($dist + 0.1)) * 5; // Base distance
    $score += ($inStockCount * 2); // Bonus stock
    $score += ($totalProds * 0.5); // Bonus diversité

    // Label intelligent
    $label = "À proximité";
    if ($dist < 1) $label = "📍 Très proche";
    elseif ($inStockCount > 5) $label = "🔥 Grand choix en stock";
    elseif ($totalProds > 0 && $inStockCount == $totalProds) $label = "✅ Tout est disponible";

    $results[] = [
        'id_stand' => $stand['ID_stand'],
        'nom_stand' => $stand['nom_stand'],
        'distance_km' => round($dist, 2),
        'description' => $stand['description'],
        'position' => $stand['position'],
        'total_produits' => $totalProds,
        'en_stock' => $inStockCount,
        'score' => $score,
        'label' => $label
    ];
}

// 5. TRI INTELLIGENT par Score décroissant
usort($results, function($a, $b) {
    return $b['score'] <=> $a['score'];
});

// 6. Format de sortie JSON
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);


// Fonctions utilitaires expertes
function geocodeInternal($address) {
    if (empty($address)) return null;
    $address = trim($address);

    // Vérifier si le format est déjà numérique "lat,lon"
    if (preg_match('/^[-+]?([0-9]*\.[0-9]+|[0-9]+),\s*[-+]?([0-9]*\.[0-9]+|[0-9]+)$/', $address, $matches)) {
        $parts = explode(',', $address);
        return [(float)$parts[0], (float)$parts[1]];
    }

    // Dictionnaire étendu pour couvrir plus de stands
    $localCache = [
        'ariana' => [36.8625, 10.1956], 
        'menzah' => [36.8465, 10.1706],
        'nasser' => [36.8580, 10.1600], 
        'medina' => [36.7992, 10.1706],
        'zaghouan' => [36.4022, 10.1425], 
        'lac' => [36.8333, 10.2333], 
        'marsa' => [36.8778, 10.3222],
        'tunis' => [36.8065, 10.1815],
        'bardo' => [36.8093, 10.1311],
        'carthage' => [36.8578, 10.3231],
        'goulette' => [36.8181, 10.3050],
        'raoued' => [36.9081, 10.1753],
        'mourouj' => [36.7231, 10.2117],
        'ben arous' => [36.7531, 10.2189],
        'sousse' => [35.8256, 10.6369],
        'monastir' => [35.7780, 10.8262],
        'sfax' => [34.7400, 10.7600],
        'bizerte' => [37.2744, 9.8739],
        'hammamet' => [36.4000, 10.6167],
        'nabeul' => [36.4561, 10.7376],
        'manouba' => [36.8078, 10.0867]
    ];

    $addrClean = strtolower($address);
    foreach ($localCache as $key => $coords) { 
        // Recherche floue : si le nom de la ville est contenu dans l'adresse du stand
        if (strpos($addrClean, $key) !== false) return $coords; 
    }
    
    // Si l'adresse contient un mot comme "parcours" ou "stand", on peut donner une position par défaut à Tunis
    if (strpos($addrClean, 'parcours') !== false || strpos($addrClean, 'stand') !== false) {
        return [36.8065, 10.1815];
    }

    return null; // Si vraiment aucune correspondance, on ignore pour ne pas fausser les distances
}

function haversineInternal($lat1, $lon1, $lat2, $lon2) {
    $earthRadius = 6371; // km
    $dLat = deg2rad($lat2 - $lat1); 
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}
