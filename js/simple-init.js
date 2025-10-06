// Script simple pour injecter header et footer sans modules ES6
document.addEventListener("DOMContentLoaded", async function() {
    console.log("Début de l'initialisation");
    
    try {
        // Injecter le header
        const headerResponse = await fetch("/templates/header.html");
        if (headerResponse.ok) {
            const headerData = await headerResponse.text();
            const headerPlaceholder = document.getElementById("header-placeholder");
            if (headerPlaceholder) {
                headerPlaceholder.innerHTML = headerData;
                console.log("Header injecté avec succès");
            }
        } else {
            console.error("Erreur chargement header :", headerResponse.status);
        }

        // Injecter le footer
        const footerResponse = await fetch("/templates/footer.html");
        if (footerResponse.ok) {
            const footerData = await footerResponse.text();
            const footerPlaceholder = document.getElementById("footer-placeholder");
            if (footerPlaceholder) {
                footerPlaceholder.innerHTML = footerData;
                console.log("Footer injecté avec succès");
            }
        } else {
            console.error("Erreur chargement footer :", footerResponse.status);
        }

        // Injecter les modals
        const modalsResponse = await fetch("/templates/modals.html");
        if (modalsResponse.ok) {
            const modalsData = await modalsResponse.text();
            document.body.insertAdjacentHTML("beforeend", modalsData);
            console.log("Modals injectés avec succès");
        } else {
            console.error("Erreur chargement modals :", modalsResponse.status);
        }

        console.log("Initialisation terminée avec succès !");
    } catch (err) {
        console.error("Erreur lors de l'initialisation :", err);
    }
});
