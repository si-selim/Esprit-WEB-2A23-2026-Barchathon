document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form');

    const fields = {
        nom: document.getElementById('nom_marathon'),
        organisateur: document.getElementById('organisateur_marathon'),
        region: document.getElementById('region_marathon'),
        date: document.getElementById('date_marathon'),
        places: document.getElementById('nb_places_dispo'),
        prix: document.getElementById('prix_marathon'),
        image: document.getElementById('image_marathon')
    };

    function setFeedback(id, message, type) {
        const el = document.getElementById(id);
        if (!el) return;
        el.textContent = message;
        el.className = 'feedback ' + (type || '');
    }

    // Au moins une lettre obligatoire (lettres + chiffres OK, mais pas QUE des chiffres)
    const alphaNumRegex = /^(?=.*[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF])[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF0-9\s\-'\.]+$/;
    // Lettres uniquement (pas de chiffres du tout)
    const alphaOnlyRegex = /^[A-Za-z\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF\s\-'\.]+$/;

    function validateNom() {
        const v = fields.nom.value.trim();
        if (v.length === 0) { setFeedback('nomFeedback', '\u274C Le nom est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('nomFeedback', '\u274C Le nom doit contenir au moins 3 caract\u00e8res.', 'error'); return false; }
        if (!alphaNumRegex.test(v)) { setFeedback('nomFeedback', '\u274C Le nom doit contenir au moins une lettre (pas uniquement des chiffres).', 'error'); return false; }
        setFeedback('nomFeedback', '\u2705 Nom valide.', 'success'); return true;
    }

    function validateOrganisateur() {
        const v = fields.organisateur.value.trim();
        if (v.length === 0) { setFeedback('organisateurFeedback', "\u274C L'organisateur est obligatoire.", 'error'); return false; }
        if (v.length < 3) { setFeedback('organisateurFeedback', "\u274C L'organisateur doit contenir au moins 3 caract\u00e8res.", 'error'); return false; }
        if (!alphaOnlyRegex.test(v)) { setFeedback('organisateurFeedback', "\u274C L'organisateur doit contenir uniquement des lettres (pas de chiffres).", 'error'); return false; }
        setFeedback('organisateurFeedback', '\u2705 Organisateur valide.', 'success'); return true;
    }

    function validateRegion() {
        const v = fields.region.value.trim();
        if (v.length === 0) { setFeedback('regionFeedback', '\u274C La r\u00e9gion est obligatoire.', 'error'); return false; }
        if (v.length < 3) { setFeedback('regionFeedback', '\u274C La r\u00e9gion doit contenir au moins 3 caract\u00e8res.', 'error'); return false; }
        if (!alphaNumRegex.test(v)) { setFeedback('regionFeedback', '\u274C La r\u00e9gion doit contenir au moins une lettre (pas uniquement des chiffres).', 'error'); return false; }
        setFeedback('regionFeedback', '\u2705 R\u00e9gion valide.', 'success'); return true;
    }

    function validateDate() {
        const v = fields.date.value;
        if (!v) { setFeedback('dateFeedback', '\u274C La date est obligatoire.', 'error'); return false; }
        const selected = new Date(v + 'T00:00:00');
        const today = new Date(); today.setHours(0, 0, 0, 0);
        if (selected <= today) { setFeedback('dateFeedback', '\u274C La date doit \u00eatre dans le futur.', 'error'); return false; }
        setFeedback('dateFeedback', '\u2705 Date valide.', 'success'); return true;
    }

    function validatePlaces() {
        const raw = fields.places.value.trim();
        if (raw === '') { setFeedback('placesFeedback', '\u274C Le nombre de places est obligatoire.', 'error'); return false; }
        if (!/^\d+$/.test(raw)) { setFeedback('placesFeedback', '\u274C Veuillez saisir uniquement des chiffres entiers.', 'error'); return false; }
        if (parseInt(raw, 10) < 1) { setFeedback('placesFeedback', '\u274C Le nombre de places doit \u00eatre un entier positif (\u2265 1).', 'error'); return false; }
        setFeedback('placesFeedback', '\u2705 Nombre de places valide.', 'success'); return true;
    }

    function validatePrix() {
        const raw = fields.prix.value.trim();
        if (raw === '') { setFeedback('prixFeedback', '\u274C Le prix est obligatoire.', 'error'); return false; }
        if (!/^\d+(\.\d{1,2})?$/.test(raw)) { setFeedback('prixFeedback', '\u274C Veuillez saisir uniquement des chiffres (ex: 30 ou 30.50).', 'error'); return false; }
        if (parseFloat(raw) < 0) { setFeedback('prixFeedback', '\u274C Le prix doit \u00eatre un nombre positif ou z\u00e9ro.', 'error'); return false; }
        setFeedback('prixFeedback', '\u2705 Prix valide.', 'success'); return true;
    }

    function validateImage() {
        if (!fields.image.files || fields.image.files.length === 0) {
            setFeedback('imageFeedback', '\u274C Veuillez s\u00e9lectionner une photo pour le marathon.', 'error'); return false;
        }
        setFeedback('imageFeedback', '\u2705 Image s\u00e9lectionn\u00e9e : ' + fields.image.files[0].name, 'success'); return true;
    }

    // Bloquer les lettres dans les champs numeriques
    fields.places.addEventListener('keypress', function (e) {
        if (!/[\d]/.test(e.key)) e.preventDefault();
    });
    fields.prix.addEventListener('keypress', function (e) {
        if (!/[\d\.]/.test(e.key)) e.preventDefault();
        if (e.key === '.' && this.value.includes('.')) e.preventDefault();
    });

    fields.nom.addEventListener('input', validateNom);
    fields.nom.addEventListener('blur', validateNom);
    fields.organisateur.addEventListener('input', validateOrganisateur);
    fields.organisateur.addEventListener('blur', validateOrganisateur);
    fields.region.addEventListener('input', validateRegion);
    fields.region.addEventListener('blur', validateRegion);
    fields.date.addEventListener('change', validateDate);
    fields.places.addEventListener('input', validatePlaces);
    fields.places.addEventListener('blur', validatePlaces);
    fields.prix.addEventListener('input', validatePrix);
    fields.prix.addEventListener('blur', validatePrix);
    fields.image.addEventListener('change', validateImage);

    form.addEventListener('submit', function (e) {
        const valid = [validateNom(), validateOrganisateur(), validateRegion(), validateDate(), validatePlaces(), validatePrix(), validateImage()];
        if (valid.includes(false)) e.preventDefault();
    });
});
