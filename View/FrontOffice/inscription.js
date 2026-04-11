document.addEventListener("DOMContentLoaded", function () {

    // ===== DATE AUTOMATIQUE =====
    let dateInput = document.getElementById("date_paiement");
    if (dateInput) {
        let today = new Date().toISOString().split("T")[0];
        dateInput.value = today;
    }

    // ===== FONCTION CALCUL PRIX =====
    function calculerPrix() {
        let nb = document.getElementById("nb_personnes").value;
        let circuit = document.getElementById("circuit").value;

        let prixUnitaire = 0;

        if (circuit === "10km") prixUnitaire = 20;
        else if (circuit === "21km") prixUnitaire = 40;
        else if (circuit === "42km") prixUnitaire = 60;

        let total = Number(nb) * prixUnitaire;

        let champPrix = document.getElementById("prix_total");

        if (champPrix) {
            champPrix.value = (total || 0) + " TND";
        }
    }

    // ===== EVENEMENTS AUTOMATIQUES =====
    let nbInput = document.getElementById("nb_personnes");
    let circuitInput = document.getElementById("circuit");

    if (nbInput) nbInput.addEventListener("input", calculerPrix);
    if (circuitInput) circuitInput.addEventListener("change", calculerPrix);

    // ===== FORMULAIRE =====
    const form = document.getElementById("formInscription");
    if (!form) return;

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        let nb = document.getElementById("nb_personnes").value.trim();
        let circuit = document.getElementById("circuit").value;
        let mode = document.getElementById("mode_paiement").value;
        let date = document.getElementById("date_paiement").value;

        let erreurs = [];

        // ===== CONTROLES =====
        if (nb === "" || isNaN(nb) || Number(nb) <= 0) {
            erreurs.push("Nombre de personnes invalide");
        }

        if (circuit === "") {
            erreurs.push("Veuillez choisir un circuit");
        }

        if (mode === "") {
            erreurs.push("Veuillez choisir un mode de paiement");
        }

        if (date === "") {
            erreurs.push("Veuillez choisir une date de paiement");
        }

        // ===== STOP SI ERREURS =====
        if (erreurs.length > 0) {
            alert("Erreurs :\n\n" + erreurs.join("\n"));
            return;
        }

        // ===== CALCUL FINAL =====
        calculerPrix();

        form.submit();

    });
    
});
window.fillForm = function(id, nb, mode, date, parcours) {

    console.log("CLICK OK", id);

    document.getElementById("id_inscription").value = id;
    document.getElementById("nb_personnes").value = nb;
    document.getElementById("mode_paiement").value = mode;
    let dateFormat = date.split(" ")[0];
document.getElementById("date_paiement").value = dateFormat;

    let circuit = "";
    if (parcours == 1) circuit = "10km";
    else if (parcours == 2) circuit = "21km";
    else circuit = "42km";

    document.getElementById("circuit").value = circuit;
}
window.rechercher = function() {
    let id = document.getElementById("search_id").value;

    if (id === "") {
        alert("Veuillez entrer un ID");
        return;
    }

    // 👉 RESTE SUR inscription.php
    window.location.href = window.location.pathname + "?search_id=" + id;
};