window.onload = () => {
    //DELETE article
    const buttons = document.querySelectorAll('.fa-trash-can');
    const abord = document.querySelector('.abord');
    const modale = document.querySelector('.modale');
    buttons.forEach(button => button.addEventListener('click', openModale));    
    abord.addEventListener('click', () => modale.classList.remove('visible'));
}

function openModale() {
    const modale = document.querySelector('.modale');
    const link = modale.querySelector('a');

    modale.classList.add('visible');
    console.log(this.dataset.slug);

    if(this.dataset.target === 'post') {
        link.href = `/post/delete/${this.dataset.slug}`;
    } else if(this.dataset.target === 'comment') {
        link.href = `/post/deleteComment/${this.dataset.id}`;
    }
}