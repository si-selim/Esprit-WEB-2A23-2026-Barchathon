document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("formInscription");

    let dateInput = document.getElementById("date_paiement");
    if (dateInput) {
        let today = new Date().toISOString().split("T")[0];
        dateInput.value = today;
    }

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

    function validateField(field) {

        let value = field.value.trim();
        let error = document.getElementById("error-" + field.id);

        if (!error) return;

        switch (field.id) {

            case "nb_personnes":
                if (value === "" || isNaN(value) || Number(value) <= 0) {
                    error.style.color = "red";
                    error.innerText = " Nombre invalide";
                } else {
                    error.style.color = "green";
                    error.innerText = " Correct";
                }
                break;

            case "circuit":
                if (value === "") {
                    error.style.color = "red";
                    error.innerText = " Choisir circuit";
                } else {
                    error.style.color = "green";
                    error.innerText = " Correct";
                }
                break;

            case "mode_paiement":
                if (value === "") {
                    error.style.color = "red";
                    error.innerText = " Choisir mode";
                } else {
                    error.style.color = "green";
                    error.innerText = " Correct";
                }
                break;

            case "date_paiement":
                if (value === "") {
                    error.style.color = "red";
                    error.innerText = " Date obligatoire";
                } else {
                    error.style.color = "green";
                    error.innerText = " Correct";
                }
                break;
        }
    }

    let nbInput = document.getElementById("nb_personnes");
    let circuitInput = document.getElementById("circuit");
    let modeInput = document.getElementById("mode_paiement");

    if (nbInput) {
        nbInput.addEventListener("input", function () {
            calculerPrix();
            validateField(this);
        });
    }

    if (circuitInput) {
        circuitInput.addEventListener("change", function () {
            calculerPrix();
            validateField(this);
        });
    }

    if (modeInput) {
        modeInput.addEventListener("change", function () {
            validateField(this);
        });
    }

    if (dateInput) {
        dateInput.addEventListener("change", function () {
            validateField(this);
        });
    }

    if (!form) return;

    form.addEventListener("submit", function (e) {

        e.preventDefault();

        let nb = nbInput.value.trim();
        let circuit = circuitInput.value;
        let mode = modeInput.value;
        let date = dateInput.value;

        let valid = true;

        // validations
        if (nb === "" || isNaN(nb) || Number(nb) <= 0) valid = false;
        if (circuit === "") valid = false;
        if (mode === "") valid = false;
        if (date === "") valid = false;

        if (!valid) {
            alert(" Vérifiez les champs !");
            return;
        }

        calculerPrix();

        let id = document.getElementById("id_inscription").value;

        if (id === "") {
            alert(" Inscription ajoutée !");
        } else {
            alert(" Inscription modifiée !");
        }

        form.submit();
    });

});

window.fillForm = function(id, nb, mode, date, parcours) {

    document.getElementById("id_inscription").value = id;
    document.getElementById("nb_personnes").value = nb;
    document.getElementById("mode_paiement").value = mode;

    document.getElementById("date_paiement").value = date;

    let circuit = "";
    if (parcours == 1) circuit = "10km";
    else if (parcours == 2) circuit = "21km";
    else circuit = "42km";

    document.getElementById("circuit").value = circuit;

    let event = new Event("input");
    document.getElementById("nb_personnes").dispatchEvent(event);
};

window.rechercher = function() {

    let id = document.getElementById("search_id").value;

    if (id === "") {
        alert(" Entrez un ID");
        return;
    }

    window.location.href = window.location.pathname + "?search_id=" + id;
};