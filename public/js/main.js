const fixedNav = document.querySelector('.fixed-nav');
const sectionDestinations = document.querySelector('#destinations');

if(sectionDestinations.getBoundingClientRect().y < 100) {
    fixedNav.style.display = "flex";
    fixedNav.style.opacity = (100 - sectionDestinations.getBoundingClientRect().y) / 100;
}
document.addEventListener('scroll', () => {
    if(sectionDestinations.getBoundingClientRect().y < 100) {
        fixedNav.style.display = "flex";
        fixedNav.style.opacity = (100 - sectionDestinations.getBoundingClientRect().y) / 100;
    } else if(sectionDestinations.getBoundingClientRect().y > 100 && fixedNav.style.display !== "none") {
        fixedNav.style.display = "none";
    }
})

//FILTER

btns = document.querySelectorAll('#destinations ul li');

window.onload = () => {
    for(let i = 0; i < 5; i++) {
        btns[i].addEventListener('click', filter);
    }
}

function filter() {

    let data = new FormData();
    data.append('category', this.dataset.category);

    let xhr = new XMLHttpRequest;
    xhr.open('POST', '/main', true);
    xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest');
    xhr.send(data);

    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {

                let response = JSON.parse(xhr.responseText)

                console.log(response.posts);

                const posts = document.querySelectorAll('.grid article');
                posts.forEach(post => post.style.display = response.posts.includes(post.dataset.id) ? 'block' : 'none');
            }

        } else {
            // Le serveur a renvoyé un status d'erreur
        }
    }
}
