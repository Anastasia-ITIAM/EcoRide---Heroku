// Script simple pour injecter header et footer sans modules ES6
document.addEventListener("DOMContentLoaded", function() {
    console.log("Début de l'initialisation");
    
    try {
        // Template header inline
        const headerTemplate = `<header class="py-3 mb-4 border-bottom">
    <nav class="navbar navbar-expand-md">
        <div class="container">

            <!-- Logo -->
            <a href="/index.html" class="logo-container text-decoration-none">
                <img src="../assets/logo.svg" alt="Logo d'EcoRide" width="40" height="40">
                <span class="brand-name">EcoRide</span>
            </a>

            <!-- Bouton menu burger -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#ecoNavbar" aria-controls="ecoNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Contenu collapsible -->
            <div class="collapse navbar-collapse mt-3 mt-md-0" id="ecoNavbar">
                <!-- Navigation -->
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-center">
                    <li class="nav-item"><a href="/index.html" class="nav-link px-3">Accueil</a></li>
                    <li class="nav-item"><a href="/pages/searchTrip.html" class="nav-link px-3">Covoiturages</a></li>
                    <li class="nav-item"><a href="/pages/contact.html" class="nav-link px-3">Contacts</a></li>
                </ul>

                <!-- Connexion / Inscription -->
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-center gap-2 text-center text-md-start">
                    <div id="user-greeting"></div>
                    <div id="auth-buttons">
                        <a href="/pages/signUp.html" class="btn custom-btn">S'inscrire</a>
                        <a href="/pages/signIn.html" class="btn custom-btn">Connexion</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</header>
<a href="javascript:history.back()" class="back-link fixed">
    ← Retour
</a>`;

        // Template footer inline
        const footerTemplate = `<footer class="py-3 mt-auto text-center border-top">
    <a href="mailto:contact@ecoride.fr" class="footer-email nav-link px-2">
        contact@ecoride.fr
    </a>

    <hr class="footer-separator">

    <a href="#" class="nav-link px-1 text-muted" data-bs-toggle="modal" data-bs-target="#mentionsModal">
        Mentions légales
    </a>

    <p class="text-muted mb-0">© 2025 EcoRide</p>
</footer>`;

        // Template modals inline
        const modalsTemplate = `<div class="modal fade" id="mentionsModal" tabindex="-1" aria-labelledby="mentionsLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="mentionsLabel">Mentions légales</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>
        <div class="modal-body text-start">
            <div class="modal-body text-start">
        <p><strong>Préambule</strong><br>
                Ce site internet est une réalisation à but pédagogique et ne constitue pas un site officiel.
                Il n'est affilié à aucune entreprise ou organisation réelle.
                Conçu dans le cadre d'un projet de formation, il ne doit en aucun cas être utilisé à des fins commerciales.</p>
        <p><strong>Éditeur du site</strong><br>
        Nom de l'entreprise : EcoRide<br>
        Siège social : 1 Avenue de l'Écologie, Paris, France<br>
        Téléphone : +33 1 00 00 00 00<br>
        Email : contact@ecoride.com<br>
        Numéro d'enregistrement : 123 456 789<br>
        Directeur de la publication : José Studi</p>

        <p><strong>Hébergeur du site</strong><br>
        Le site est hébergé par WebHostPro.<br>
        Adresse : 25 Boulevard Saint-Michel, 75005 Paris, France.<br>
        Téléphone : +33 1 45 67 89 10.<br>
        Contact email : <a href="mailto:support@webhostpro.fr">support@webhostpro.fr</a>.
        </p>


        <p><strong>Conditions générales d'utilisation (CGU)</strong><br>
        En accédant à ce site, vous acceptez de respecter les présentes conditions d'utilisation. Tous les contenus présents sur ce site, y compris les textes, images, logos, sont la propriété intellectuelle d'EcoRide et sont protégés par le droit d'auteur.<br>
        EcoRide décline toute responsabilité en cas de dommages résultant de l'utilisation de ce site, y compris les liens vers des sites externes.</p>

        <p><strong>Protection des données personnelles</strong><br>
        Conformément à la loi Informatique et Libertés du 6 janvier 1978 modifiée et au RGPD, vous disposez d'un droit d'accès, de rectification, d'effacement, de limitation, d'opposition et de portabilité des données vous concernant.<br>
        Pour exercer ces droits : <a href="mailto:contact@ecoride.fr">contact@ecoride.fr</a></p>

        <p><strong>Cookies</strong><br>
        Ce site utilise des cookies pour améliorer votre expérience. Le cookie utilisé est un cookie de session, expirant à la fermeture de votre navigateur.</p>

        <p><strong>Politique de confidentialité</strong><br>
        Vos données sont utilisées uniquement pour le fonctionnement des services proposés. Aucune donnée n'est partagée avec des tiers, sauf obligation légale.</p>

        <p><strong>Droit applicable</strong><br>
        Ce site est régi par le droit français. En cas de litige, le tribunal compétent est le Tribunal de Commerce de Paris.</p>
        </div>
        </div>
        <div class="modal-footer">
    <button type="button" class="btn custom-btn" data-bs-dismiss="modal">Fermer</button>
        </div>
        </div>
    </div>
    </div>
</div>`;

        // Injecter le header
        const headerPlaceholder = document.getElementById("header-placeholder");
        if (headerPlaceholder) {
            headerPlaceholder.innerHTML = headerTemplate;
            console.log("Header injecté avec succès");
        }

        // Injecter le footer
        const footerPlaceholder = document.getElementById("footer-placeholder");
        if (footerPlaceholder) {
            footerPlaceholder.innerHTML = footerTemplate;
            console.log("Footer injecté avec succès");
        }

        // Injecter les modals
        document.body.insertAdjacentHTML("beforeend", modalsTemplate);
        console.log("Modals injectés avec succès");

        console.log("Initialisation terminée avec succès !");
    } catch (err) {
        console.error("Erreur lors de l'initialisation :", err);
    }
});