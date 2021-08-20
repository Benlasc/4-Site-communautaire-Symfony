
//Hauteur de l'image home
var image = document.querySelector('.image-home');
var hauteur = document.querySelector('nav').offsetHeight;
image.style.height = image.offsetHeight - hauteur + "px";

window.addEventListener('resize', function (event) {
    image.style.height = (window.innerWidth < 992 ? "50vh" : "100vh");
    hauteur = document.querySelector('nav').offsetHeight
    image.style.height = image.offsetHeight - hauteur + "px";
});

// // Taille de la police des titres des figures

// titres = document.getElementsByClassName('card-title');
// for (const titre of titres) {
//     titre.style.fontSize = titre.offsetWidth * 0.1 + 'px';
// }

// window.addEventListener('resize', function (event) {
//     titres = document.getElementsByClassName('card-title');
//     for (const titre of titres) {
//         titre.style.fontSize = titre.offsetWidth * 0.1 + 'px';
//     }
// });

// test ASYNCHRONE

function stringToHTML(str) {
    var parser = new DOMParser();
    var doc = parser.parseFromString(str, 'text/html');
    return doc.body.firstElementChild;
}

liens = document.getElementsByClassName('liens-ajax');
for (const lien of liens) {
    lien.addEventListener('click', async function (e) {
        e.preventDefault();
        try {
            // Si la div box existe on la supprime
            let box = document.querySelector('.box');
            if (box) {
                box.remove();
            }

            // requête asynchrone
            let response = await fetch(this.getAttribute('href'), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (response.ok) {
                let responseData = await response.text();
                let div = stringToHTML(responseData);
                document.body.insertAdjacentElement('beforeend', div);
                setTimeout(myFunction, 200);
                function myFunction() {
                    div.classList.add('box2');
                }

                // Lien pour supprimer la div box
                cross = document.querySelector('.close a');

                cross.addEventListener('click', (e) => {
                    e.preventDefault();

                    //On réinitialise l'affichage des commentaires
                    first = 0;
                    commentsList = document.getElementsByClassName('commentaire');

                    //On supprime la box
                    document.querySelector('.box').remove();
                })

                // Affichage des cinq premiers commentaires
                showList();
            } else {
                console.error("Réponse du serveur : ", response.status);
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
        let response = await fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
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
            console.error("Réponse du serveur : ", response.status);
        }
    } catch (error) {
        console.log(error);
    }
}