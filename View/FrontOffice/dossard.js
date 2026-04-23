document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");
    if (!form) return;

    const nom = document.getElementById("nom_global");

    // ================= NOM =================
    function validateNom() {
        if (!nom) return true;

        let error = document.getElementById("error-nom_global");

        if (nom.value.trim() === "") {
            if (error) {
                error.style.color = "red";
                error.innerText = "Nom obligatoire";
            }
            return false;
        } else {
            if (error) {
                error.style.color = "green";
                error.innerText = "OK";
            }
            return true;
        }
    }

    // ================= COULEUR =================
    function validateCouleur() {
        let valid = true;

        document.querySelectorAll(".couleur").forEach(c => {

            let error = c.parentElement.querySelector(".error-couleur");

            if (c.value.trim() === "") {
                if (error) {
                    error.style.color = "red";
                    error.innerText = "Couleur obligatoire";
                }
                valid = false;
            } else {
                if (error) {
                    error.style.color = "green";
                    error.innerText = "OK";
                }
            }
        });

        return valid;
    }

    // ================= TAILLE =================
    function validateTaille() {
        let valid = true;

        document.querySelectorAll(".taille").forEach(t => {

            let error = t.parentElement.querySelector(".error-taille");

            if (t.value === "") {
                if (error) {
                    error.style.color = "red";
                    error.innerText = "Taille obligatoire";
                }
                valid = false;
            } else {
                if (error) {
                    error.style.color = "green";
                    error.innerText = "OK";
                }
            }
        });

        return valid;
    }

    // ================= EVENTS =================
    if (nom) nom.addEventListener("input", validateNom);

    document.querySelectorAll(".couleur").forEach(c => {
        c.addEventListener("input", validateCouleur);
    });

    document.querySelectorAll(".taille").forEach(t => {
        t.addEventListener("change", validateTaille);
    });

    // ================= SUBMIT =================
    form.addEventListener("submit", function (e) {

        let okNom = validateNom();
        let okCouleur = validateCouleur();
        let okTaille = validateTaille();

        if (!okNom || !okCouleur || !okTaille) {
            e.preventDefault();
            alert("❌ Vérifie tous les champs !");
        } else {
            alert("✅ Dossards enregistrés !");
        }
    });

});