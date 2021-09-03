// Code JavaScript pour activer le bouton "Afficher plus de commentaires" sur la page d'une figure

let commentsList = document.getElementsByClassName('commentaire');
let numberOfComments = 5;
first = 0
showList();


function showList() {
    for (let i = first; i < first + numberOfComments; i++) {
        if (i < commentsList.length) {
            document.getElementsByClassName('commentaire')[i].style.display = "flex";
        }
    }
}

function nextComments() {
    if (first + numberOfComments <= commentsList.length) {
        first += numberOfComments;
        showList();
    } else {
        document.querySelector('.more-comments').remove();
    }
}