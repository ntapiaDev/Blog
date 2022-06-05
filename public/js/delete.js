window.onload = () => {
    const btn = document.querySelector('.delete-post');
    const abord = document.querySelector('.abord');
    const modale = document.querySelector('.modale');
    btn.addEventListener('click', () => modale.classList.add('visible'));
    abord.addEventListener('click', () => modale.classList.remove('visible'));
}