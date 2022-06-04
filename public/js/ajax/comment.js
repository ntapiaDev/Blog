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
                    clone.querySelector('span:nth-child(3)').textContent = 'Heure';
                    clone.querySelector('span:nth-child(4)').textContent = response.comment;
                    document.querySelector('.comments-list').appendChild(clone);

                    document.querySelector('#comment').value = '';
                }

            } else {
                // Le serveur a renvoyé un status d'erreur
            }
        }
    }
}