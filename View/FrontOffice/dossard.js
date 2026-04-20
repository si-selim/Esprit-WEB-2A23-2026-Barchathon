document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("form");

    let nom = document.getElementById("nom_global");
    let couleurs = document.querySelectorAll(".couleur");
    let tailles = document.querySelectorAll(".taille");

    // ================= NOM =================
    function validateNom() {
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

        couleurs.forEach(c => {
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

        tailles.forEach(t => {
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
    nom.addEventListener("input", validateNom);

    couleurs.forEach(c => {
        c.addEventListener("input", validateCouleur);
    });

    tailles.forEach(t => {
        t.addEventListener("change", validateTaille);
    });

    // ================= SUBMIT =================
    form.addEventListener("submit", function (e) {

        let v1 = validateNom();
        let v2 = validateCouleur();
        let v3 = validateTaille();

        if (!v1 || !v2 || !v3) {
            e.preventDefault(); // bloque seulement si erreur
            alert("Vérifie tous les champs !");
        } else {
            alert("Dossards enregistrés !");
        }
    });

});