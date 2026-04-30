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
            <h1>Stand Nutrition</h1>
            <div style="display: flex; gap: 10px; align-items: center;">
                <a class="btn-manipuler" href="crud-produit.php" style="margin-right: 15px;">⚙️ Manipuler Produit</a>
                <a href="stand.php">Retour stands</a>
                <a href="Semi Marathon de Sousse Corniche.php">Retour details</a>
            </div>
        </div>

        <div class="grid-products">

            <div class="products">

                <div class="card">
                    <img class="card-img" src="stand nutrition1.png" alt="">
                    <div class="card-body">
                        <h3>Smart nutrition - Energy cake - Saveur Chocolat</h3>
                        <p class="type-tag">Nutrition</p>
                        <p class="desc">C'est l'apport énergétique idéal pour le repas avant l'effort (3h avant d'attaquer la course).
                            Apport énergétique complet équilibré.
                            Effort intense (rapide et prolongé).
                            Très digeste.</p>
                        <hr class="card-separator">
                        <div class="card-footer">
                            <span class="price">10.00 TND</span>
                            <a class="btn btn-add" href="#">Ajouter au panier</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <img class="card-img" src="stand nutrition2.png" alt="">
                    <div class="card-body">
                        <h3>Smart nutrition - Energy cake - Saveur Multi Fruits</h3>
                        <p class="type-tag">Nutrition</p>
                        <p class="desc">C'est l'apport énergétique idéal pour le repas avant l'effort (3h avant d'attaquer la course).
                            Apport énergétique complet équilibré.
                            Effort intense (rapide et prolongé).
                            Très digeste.</p>
                        <hr class="card-separator">
                        <div class="card-footer">
                            <span class="price">15.00 TND</span>
                            <a class="btn btn-add" href="#">Ajouter au panier</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <img class="card-img" src="stand nutrition3.png" alt="">
                    <div class="card-body">
                        <h3>Smart nutrition - Energy cake - Saveur Multi Fruits</h3>
                        <p class="type-tag">Nutrition</p>
                        <p class="desc">C'est l'apport énergétique idéal pour le repas avant l'effort (3h avant d'attaquer la course).
                            Apport énergétique complet équilibré.
                            Effort intense (rapide et prolongé).
                            Très digeste.</p>
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

</body>

</html>
