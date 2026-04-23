// ================= CALCUL PRIX GLOBAL =================
function calculerPrix() {

    let nb = document.getElementById("nb_personnes");
    let circuit = document.getElementById("circuit");
    let champ = document.getElementById("prix_total");

    if (!nb || !circuit || !champ) return;

    let nombre = parseInt(nb.value);

    if (isNaN(nombre) || nombre <= 0) {
        champ.value = "0 TND";
        return;
    }

    let circuitValue = circuit.value;

    let prixUnitaire = 0;

    switch (circuitValue) {
        case "1":
            prixUnitaire = 20;
            break;
        case "2":
            prixUnitaire = 40;
            break;
        case "3":
            prixUnitaire = 60;
            break;
        default:
            champ.value = "0 TND";
            return;
    }

    let total = prixUnitaire * nombre;

    // 🔥 réduction
    if (nombre >= 5) total *= 0.8;
    else if (nombre >= 3) total *= 0.9;

    champ.value = total.toFixed(2) + " TND";

    console.log("TOTAL =", total);
}
document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    if (!form) return;

    const nb = document.getElementById("nb_personnes");
    const circuit = document.getElementById("circuit");
    const mode = document.getElementById("mode_paiement");
    const date = document.getElementById("date_paiement");
    const search = document.getElementById("search_id");
    

    // ================= CALCUL PRIX =================
    // ================= CALCUL PRIX INTELLIGENT =================


    // ================= VALIDATION =================
    function validateNb() {
        const error = document.getElementById("error-nb_personnes");

        if (nb.value.trim() === "" || Number(nb.value) <= 0) {
            error.innerText = "Nombre invalide";
            error.style.color = "red";
            return false;
        }

        error.innerText = "OK";
        error.style.color = "green";
        return true;
    }

    function validateCircuit() {
        const error = document.getElementById("error-circuit");

        if (circuit.value === "") {
            error.innerText = "Choisir circuit";
            error.style.color = "red";
            return false;
        }

        error.innerText = "OK";
        error.style.color = "green";
        return true;
    }

    function validateMode() {
        const error = document.getElementById("error-mode_paiement");

        if (mode.value === "") {
            error.innerText = "Choisir mode";
            error.style.color = "red";
            return false;
        }

        error.innerText = "OK";
        error.style.color = "green";
        return true;
    }

    function validateDate() {
        const error = document.getElementById("error-date_paiement");

        if (date.value === "") {
            error.innerText = "Date obligatoire";
            error.style.color = "red";
            return false;
        }

        error.innerText = "OK";
        error.style.color = "green";
        return true;
    }

    // ================= EVENTS INPUT =================
    nb.addEventListener("input", function () {
        validateNb();
        calculerPrix();
    });

    circuit.addEventListener("change", function () {
        validateCircuit();
        calculerPrix();
    });

    mode.addEventListener("change", validateMode);
    date.addEventListener("change", validateDate);

    // ================= SUBMIT =================
    form.addEventListener("submit", function (e) {

        let ok =
            validateNb() &&
            validateCircuit() &&
            validateMode() &&
            validateDate();

        if (!ok) {
            e.preventDefault();
            alert("❌ Vérifie les champs !");
        } else {
            alert("✅ Formulaire valide !");
        }
    });

    // ================= SEARCH DYNAMIC =================
    if (search) {
    search.addEventListener("input", function () {

        let filter = this.value.trim();
        let rows = document.querySelectorAll("#table-body tr");

        rows.forEach(row => {

            let idValue = row.getAttribute("data-id");

            if (filter === "" || idValue.startsWith(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }

        });
    });
}

});

// ================= FILL FORM (UPDATE) =================
window.fillForm = function(id, nbVal, modeVal, dateVal, parcours) {

    document.getElementById("id_inscription").value = id;
    document.getElementById("nb_personnes").value = nbVal;
    document.getElementById("mode_paiement").value = modeVal;
    document.getElementById("date_paiement").value = dateVal;

    // 🔥 IMPORTANT : utiliser 1 / 2 / 3
    document.getElementById("circuit").value = parcours;

    calculerPrix();
};
// ================= DATE AUTO =================
function setTodayDate() {
    const dateInput = document.getElementById("date_paiement");

    let today = new Date();
    let formatted = today.toISOString().split('T')[0]; // format YYYY-MM-DD

    dateInput.value = formatted;
}

document.addEventListener("DOMContentLoaded", function () {
    setTodayDate();
    calculerPrix();
});