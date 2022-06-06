window.onload = () => {
    //DELETE article
    const buttons = document.querySelectorAll('.fa-trash-can');
    const abord = document.querySelector('.abord');
    const modale = document.querySelector('.modale');
    buttons.forEach(button => button.addEventListener('click', openModale));    
    abord.addEventListener('click', () => modale.classList.remove('visible'));

    //FILTRE
    const selects = document.querySelectorAll('.admin-container li');
    selects.forEach(select => select.addEventListener('click', (e) => changeTab(select.dataset.tab, e)));
}

function openModale() {
    const modale = document.querySelector('.modale');
    const link = modale.querySelector('a');

    modale.classList.add('visible');

    if(this.dataset.target === 'post') {
        link.href = `/post/delete/${this.dataset.slug}`;
    } else if(this.dataset.target === 'comment') {
        link.href = `/post/deleteComment/${this.dataset.id}`;
    } else if(this.dataset.target === 'user') {
        link.href = `/user/delete/${this.dataset.id}`;
    }
}

function changeTab(tab, e) {
    const articles = document.querySelectorAll('.admin-container article');
    articles.forEach(article => Array.from(article.classList).includes(`${tab}-list`) ? 
        article.classList.add('visible') : Array.from(article.classList).includes(`visible`) ?
        article.classList.remove('visible') : '');
    const article = document.querySelector(`.${tab}-list`);
    const selects = document.querySelectorAll('.admin-container li');
    selects.forEach(select => Array.from(select.classList).includes('active') ? select.classList.remove('active') : '');
    e.target.classList.add('active');
}