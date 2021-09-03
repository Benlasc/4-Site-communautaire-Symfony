//Code JavaScript pour modifier dynamiquement le champs de formulaire "noms de figures" lorsque l'utilisateur choisit un groupe

function stringToHTML(str) {
    var parser = new DOMParser();
    return doc = parser.parseFromString(str, "text/html");
}

var groupe = document.getElementById("trick_groupe");

groupe.addEventListener("change", async (event) => {
    var form = groupe.closest("form");
    var data = new FormData();
    data.append(groupe.getAttribute("name"),groupe.value);
    // ex groupe.getAttribute("name") = trick[groupe]
    // ex groupe.value = 3

    try {
        // requête asynchrone
        let response = await fetch(form.getAttribute("action"), {
            method: form.getAttribute("method"),
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            body: data
        });
        // On ne met pas if response.ok car on va recevoir résponse 422 car on a laissé des champs incomplets et le token n'est pas envoyé
        if (response) {
            let responseData = await response.text();
            let html = stringToHTML(responseData);
            let newNames = html.getElementById("trick_name");
            newNames.classList.remove("is-invalid");
            document.getElementById("trick_name").replaceWith(html.getElementById("trick_name"));
        } else {
            console.log("Réponse du serveur : ", response.status);
        }
    } catch (error) {
        console.log(error);
    }
  });