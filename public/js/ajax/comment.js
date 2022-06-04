window.onload = () => {
    const btn = document.querySelector('.comment-btn');
    btn.addEventListener('click', comment);
}

function comment(e) {

    e.preventDefault();

    // let comment = document.querySelector('#comment').value;
    const slug = document.querySelector('#slug').value;

    const form = document.querySelector('#comment-form');
    let data = new FormData(form);

    let xhr = new XMLHttpRequest;
    xhr.open('POST', `/post/show/${slug}`, true);
    xhr.setRequestHeader('X-Requested-With', 'xmlhttprequest');
    xhr.send(data);

    xhr.onreadystatechange = () => {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {

                let response = JSON.parse(xhr.responseText)

                console.log(response);

                if(response.code === '200') {
                    const template = document.querySelector('.comments-template');
                    let clone = document.importNode(template.content, true);
                    clone.querySelector('img').src = `/uploads/${response.avatar}`;
                    clone.querySelector('span:nth-child(2)').textContent = response.user;
                    now = new Date();
                    clone.querySelector('span:nth-child(3)').textContent = now.toLocaleDateString("fr") + ` à ${now.getHours()}h${now.getMinutes()}`;
                    clone.querySelector('span:nth-child(4)').textContent = response.comment;
                    document.querySelector('.comments-list').appendChild(clone);
                    // +1 au nombre de commentaires
                    document.querySelector('.comments h3 span').textContent = parseInt(document.querySelector('.comments h3 span').textContent) + 1;
                    // Reset de l'input commentaire
                    document.querySelector('#comment').value = '';
                }

            } else {
                // Le serveur a renvoyé un status d'erreur
            }
        }
    }
}