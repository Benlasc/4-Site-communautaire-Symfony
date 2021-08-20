submit = document.querySelector('button[type=submit]');
form = document.querySelector('form[name="comment"]');

submit.addEventListener('click', async function (e) {
    e.preventDefault();
    // Si le textarea est vide, on ne fait rien
    if (document.querySelector('textarea').value == '') {
        return false;
    }
    let data = new FormData(form);

    try {
        // requête asynchrone
        let response = await fetch(form.getAttribute('action'), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: data
        });
        if (response.ok) {
            let responseData = await response.text();
            //insersion du commentaire
            // let div = stringToHTML(responseData);
            // document.body.insertAdjacentElement('beforeend', div);

        } else {
            console.error("Réponse du serveur : ", response.status);
        }
    } catch (error) {
        console.log(error);
    }
})
