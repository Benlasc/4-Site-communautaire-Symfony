// Code JavaScript pour ajouter et retirer de nouvelles images ou vidéos dans le formulaire permettant de modifier une figure
// +balise WYSIWYG pour la description

var ready = (callback) => {
    if (document.readyState !== "loading") { callback(); }
    else { document.addEventListener("DOMContentLoaded", callback); }
};

// Ajout de nouvelles vidéos
ready(() => {
    // On récupère la balise <div> en question qui contient l'attribut « data-prototype » qui nous intéresse.
    var $container = document.getElementById("trick_videos");

    // On ajoute un lien pour ajouter une nouvelle catégorie
    var $lienAjout = document.createElement("a");
    $lienAjout.classList.add("btn", "btn-info");

    $lienAjout.id = "ajout_video";
    $lienAjout.textContent = "Ajouter une vidéo";
    $lienAjout.href = "#";
    $container.insertAdjacentElement("beforeend", $lienAjout);

    // La fonction qui ajoute un formulaire Video
    function ajouterVideo($container) {

        // Dans le contenu de l'attribut « data-prototype », on remplace :
        // - le texte "__name__label__" qu'il contient par le label du champ
        // - le texte "__name__" qu'il contient par le numéro du champ
        var $prototype = stringToHTML($container.getAttribute("data-prototype").replace(/__name__label__/g, "Vidéo").replace(/__name__/g, index));

        // On ajoute au prototype un lien pour pouvoir supprimer la catégorie
        ajouterLienSuppression($prototype);

        // On ajoute le prototype modifié à la fin de la balise <div>
        $container.insertAdjacentElement("beforeend", $prototype);

        // Enfin, on incrémente le compteur pour que le prochain ajout se fasse avec un autre numéro
        index++;
    }

    // La fonction qui ajoute un lien de suppression d'une catégorie
    function ajouterLienSuppression($prototype) {
        // Création du lien
        var $lienSuppression = document.createElement("a");
        $lienSuppression.classList.add("btn", "btn-danger");
        $lienSuppression.href = "#";
        $lienSuppression.textContent = "Supprimer";
        $prototype.insertAdjacentElement("beforeend", $lienSuppression);

        // Ajout du listener sur le clic du lien

        $lienSuppression.addEventListener("click", (e) => {
            $prototype.remove();
            e.preventDefault(); // évite qu'un # apparaisse dans l'URL
            return false;
        });
    }

    // On ajoute un nouveau champ à chaque clic sur le lien d'ajout.

    $lienAjout.addEventListener("click", (e) => {
        ajouterVideo($container);
        // évite qu'un # apparaisse dans l'URL
        e.preventDefault();
        return false;
    });

    // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
    // var index = $container.getElementsByTagName("input").length;
    // Rem : j'ai mis document.get... à la place de $container.get...car les autres input sont en dehors de $container 
    var index = document.getElementsByTagName("input").length;

    function stringToHTML(str) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(str, "text/html");
        return doc.body.firstElementChild;
    }

    // Suppression des vidéos sur la page d'éddition
    liens = document.getElementsByClassName("delete-video");

    for (let lien of liens) {
        lien.addEventListener("click", function (e) {
            e.preventDefault();
            if (confirm("Voulez-vous supprimer cette vidéo ?")) {
                // Clic sur OK
                this.parentNode.parentNode.remove();
            }
        });
    }
});

// Ajout de nouvelles images
ready(() => {
    // On récupère la balise <div> en question qui contient l'attribut « data-prototype » qui nous intéresse.
    var $container = document.getElementById("trick_images");

    // La fonction qui ajoute un formulaire Image
    function ajouterImage($container) {

        // Dans le contenu de l'attribut « data-prototype », on remplace :
        // - le texte "__name__label__" qu'il contient par le label du champ
        // - le texte "__name__" qu'il contient par le numéro du champ
        var $prototype = stringToHTML($container.getAttribute("data-prototype").replace(/__name__label__/g, "Image").replace(/__name__/g, $container.dataset.index));

        // On ajoute au prototype un lien pour pouvoir supprimer la catégorie
        ajouterLienSuppression($prototype);

        // On ajoute le prototype modifié à la fin de la balise <div>
        $container.insertAdjacentElement("beforeend", $prototype);

        // Enfin, on incrémente le compteur pour que le prochain ajout se fasse avec un autre numéro
        $container.dataset.index++;
    }

    // On ajoute un lien pour ajouter une nouvelle catégorie
    var $lienAjout = document.createElement("a");
    $lienAjout.classList.add("btn", "btn-info");
    $lienAjout.id = "ajout_image";
    $lienAjout.textContent = "Ajouter une image";
    $lienAjout.href = "#";
    $container.insertAdjacentElement("beforeend", $lienAjout);

    // On ajoute un nouveau champ à chaque clic sur le lien d'ajout.

    $lienAjout.addEventListener("click", (e) => {
        ajouterImage($container);
        // évite qu'un # apparaisse dans l'URL
        e.preventDefault();
        return false;
    });

    function stringToHTML(str) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(str, "text/html");
        return doc.body.firstElementChild;
    }

    // La fonction qui ajoute un lien de suppression d'une catégorie
    function ajouterLienSuppression($prototype) {
        // Création du lien
        var $lienSuppression = document.createElement("a");
        $lienSuppression.classList.add("btn", "btn-danger");
        $lienSuppression.href = "#";
        $lienSuppression.textContent = "Supprimer";
        $prototype.insertAdjacentElement("beforeend", $lienSuppression);

        // Ajout du listener sur le clic du lien

        $lienSuppression.addEventListener("click", (e) => {
            $prototype.remove();
            e.preventDefault(); // évite qu'un # apparaisse dans l'URL
            return false;
        });
    }

    // Suppression des images sur la page d'éddition
    liens = document.getElementsByClassName("delete-image");

    for (lien of liens) {
        lien.addEventListener("click", function (e) {
            e.preventDefault();
            if (confirm("Voulez-vous supprimer cette image ?")) {
                // Clic sur OK
                this.parentNode.parentNode.remove();
            }
        })
    }

    // Balise WYSIWYG
    ClassicEditor
        .create(document.querySelector(".ckeditor"), {
            removePlugins: ["CKFinderUploadAdapter", "CKFinder", "EasyImage", "Image", "ImageCaption", "ImageStyle", "ImageToolbar", "ImageUpload", "MediaEmbed"]
        })
        .then((editor) => {
            editorTextarea = editor;
        })
        .catch((error) => {
            console.log(error);
        });
});