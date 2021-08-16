    var image = document.querySelector('.image-home');
    var hauteur = document.querySelector('nav').offsetHeight;
    image.style.height = image.offsetHeight - hauteur + "px";

    window.addEventListener('resize', function (event) {
        image.style.height = (window.innerWidth < 400 ? "50vh" : "100vh");
        // image.style.height = "100vh";
        hauteur = document.querySelector('nav').offsetHeight
        image.style.height = image.offsetHeight - hauteur + "px";
    });

    // test ASYNCHRONE

    function stringToHTML(str) {
        var parser = new DOMParser();
        var doc = parser.parseFromString(str, 'text/html');
        return doc.body.firstElementChild;
    }

    liens = document.getElementsByClassName('test-envoi');
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
                        'X-Requested-With': 'xmlhttprequest'
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
                        document.querySelector('.box').remove();
                    })
                } else {
                    console.error("Réponse du serveur : ", response.status);
                }
            } catch (error) {
                console.log(error);
            }
        })
    }