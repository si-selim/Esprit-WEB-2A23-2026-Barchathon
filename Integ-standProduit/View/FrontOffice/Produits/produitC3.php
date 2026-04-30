<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue stand</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <img src="logo.png" alt="BarchaThon Logo">
                <span>BarchaThon</span>
            </div>
            <nav class="nav">
                <a href="../BackOffice/tab_stand.php">Accueil</a>
                <a href="stand.php">Catalogue</a>
                <a href="#">S’inscrire</a>
                <a href="#" class="btn-login">Se connecter</a>
            </nav>
        </div>
    </header>

    <div class="stand">

        <div class="top">
            <h1>Stand Outdoor</h1>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a class="btn-manipuler" href="crud-produit.php" style="margin-right: 15px;">⚙️ Manipuler Produit</a>
                <a href="stand.php">Retour stands</a>
                <a href="Stand Trail de Zaghouan.php">Retour details</a>
            </div>
        </div>

        <div class="grid-products">

            <div class="products">

                <div class="card">
                    <img class="card-img" src="stand outdoor.png" alt="">
                    <div class="card-body">
                        <h3>Sac à dos trail</h3>
                        <p class="type-tag">Equipement</p>
                        <p class="desc">Sac léger et ergonomique, conçu pour transporter eau et équipements essentiels lors des sorties
                            en trail.</p>
                        <hr class="card-separator">
                        <div class="card-footer">
                            <span class="price">10.00 TND</span>
                            <a class="btn btn-add" href="#">Ajouter au panier</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <img class="card-img" src="stand outdoor2.png" alt="">
                    <div class="card-body">
                        <h3>lampe frontale running</h3>
                        <p class="type-tag">Equipement</p>
                        <p class="desc">Lampe pratique pour éclairer les parcours en faible luminosité ou lors des sorties nocturnes.</p>
                        <hr class="card-separator">
                        <div class="card-footer">
                            <span class="price">15.00 TND</span>
                            <a class="btn btn-add" href="#">Ajouter au panier</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <img class="card-img" src="stand outdoor3.png" alt="">
                    <div class="card-body">
                        <h3>Lunettes de soleil sport</h3>
                        <p class="type-tag">Equipement</p>
                        <p class="desc">Protection UV et confort visuel pour activités en extérieur.</p>
                        <hr class="card-separator">
                        <div class="card-footer">
                            <span class="price">15.00 TND</span>
                            <a class="btn btn-add" href="#">Ajouter au panier</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <img class="card-img" src="stand outdoor4.png" alt="">
                    <div class="card-body">
                        <h3>Mini-boussole ou GPS portable</h3>
                        <p class="type-tag">Equipement</p>
                        <p class="desc">ultra-léger et compact, idéal pour rester orienté lors de vos sorties running ou trail.
                            Accessoire pratique pour suivre vos parcours et ne jamais vous perdre.</p>
                        <hr class="card-separator">
                        <div class="card-footer">
                            <span class="price">15.00 TND</span>
                            <a class="btn btn-add" href="#">Ajouter au panier</a>
                        </div>
                    </div>
                </div>

            </div>

            <!-- PANIER -->
            <aside class="side">
                <h3>Panier participant</h3>
                <p>Produit 1 - 00.00 TND</p>
                <p>Produit 2 - 00.00 TND</p>
                <p><strong>Total: 00.000 TND</strong></p>
            </aside>

        </div>

    </div>
    <!-- SIDEBAR -->


</body>

</html>
