
// Code JavaScript pour la hauteur dynamique de l'image home, le filtre et les requêtes Ajax pour afficher la page show d'une figure

//Hauteur de l'image home
var image = document.querySelector(".image-home");
var hauteur = document.querySelector("nav").offsetHeight;
image.style.height = image.offsetHeight - hauteur + "px";

window.addEventListener("resize", function (event) {
    image.style.height = (window.innerWidth < 992 ? "50vh" : "100vh");
    hauteur = document.querySelector("nav").offsetHeight
    image.style.height = image.offsetHeight - hauteur + "px";
});

//filtre pour chercher des figures
let input = document.getElementById("trickFilter");
input.focus();
input.addEventListener("keyup", (event) => {
    let val = input.value;
    if (val =="") {
        document.querySelectorAll(".col").forEach(bloc => bloc.style.display = "block");
        document.querySelectorAll(".card-title > a span").forEach((span) => span.classList.remove("highlighted"));
        return true;
    }
    let regex = "(.*)";
    for (const i in val) {
        regex += `(${val[i]})(.*)`;            
        }
    document.querySelectorAll(".col").forEach((bloc) => bloc.style.display = "block")
    let textsFiltered = document.querySelectorAll(".card-title > a");
    for (const lien of textsFiltered) {
        let resultats = lien.innerText.match(new RegExp(regex,"i"));
        if (resultats) {
            let string = "";
            for (const i in resultats) {
                if (i > 0) {
                    if (i % 2 === 0) {
                        string += '<span class="highlighted">' + resultats[i] + '</span>';
                    } else {
                        string += resultats[i];
                    }
                }
            }
            lien.innerHTML = string;
        } else {
            lien.parentElement.parentElement.parentElement.parentElement.style.display = "none";
        }
    }
});

function stringToHTML(str) {
    var parser = new DOMParser();
    var doc = parser.parseFromString(str, "text/html");
    return doc.body.firstElementChild;
}

function myFunction(div) {
    div.classList.add("box2");
}

// Requête Ajax pour afficher une fenêtre show trick
let liens = document.getElementsByClassName("liens-ajax");
for (const lien of liens) {
    lien.addEventListener("click", async function (e) {
        e.preventDefault();
        try {
            // Si la div box existe on la supprime
            let box = document.querySelector('.box');
            if (box) {
                box.remove();
            }

            // requête asynchrone
            let response = await fetch(this.getAttribute("href"), {
                method: "GET",
                headers: {
                    "X-Requested-With": "XMLHttpRequest"
                }
            });
            if (response.ok) {
                let responseData = await response.text();
                let div = stringToHTML(responseData);
                document.body.insertAdjacentElement("beforeend", div);
                
                setTimeout(myFunction.bind(null,div), 200);

                // Lien pour supprimer la div box
                let cross = document.querySelector(".close a");

                cross.addEventListener("click", (e) => {
                    e.preventDefault();

                    //On réinitialise l'affichage des commentaires
                    first = 0;
                    commentsList = document.getElementsByClassName("commentaire");

                    //On supprime la box
                    document.querySelector('.box').remove();
                });

                // Affichage des cinq premiers commentaires
                showList();
            } else {
                console.log("Réponse du serveur : ", response.status);
            }
        } catch (error) {
            console.log(error);
        }
    })
}

// Commentaires de la fenêtre ajax show
async function addComment(e) {
    // Si le textarea est vide, on ne fait rien
    if (document.querySelector('textarea').value == '') {
        return false;
    }
    e.preventDefault();
    document.querySelector('button[type=submit]').blur();
    form = document.querySelector('form[name="comment"]');
    let data = new FormData(form);

    try {
        // Requête asynchrone
        let response = await fetch(form.getAttribute("action"), {
            method: 'POST',
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            },
            body: data
        });
        if (response.ok) {
            let responseData = await response.text();
            // Insersion du commentaire
            let div = stringToHTML(responseData);
            if (div.nodeName == 'DIV') {
                document.querySelector('.laisser_commentaire').insertAdjacentElement('afterend', div);
                div.style.display = 'flex';
            } else {
                document.location.href = "/login";
            }
        } else {
            console.log("Réponse du serveur : ", response.status);
        }
    } catch (error) {
        console.log(error);
    }
}