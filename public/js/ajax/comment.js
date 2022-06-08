window.onload = () => {
    if(!document.querySelector('.comment-btn')) {
        return;
    }
    const btn = document.querySelector('.comment-btn');
    btn.addEventListener('click', comment);

    //DELETE comment
    const deleteBtns = document.querySelectorAll('.delete-btn');
    deleteBtns.forEach(deleteBtn => deleteBtn.addEventListener('click', deleteComment));

    //DELETE article
    const button = document.querySelector('.delete-post');
    const abord = document.querySelector('.abord');
    const modale = document.querySelector('.modale');
    button.addEventListener('click', () => modale.classList.add('visible'));
    abord.addEventListener('click', () => modale.classList.remove('visible'));
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
                    clone.querySelector('a:nth-child(2)').href = `/user/show/${response.user_id}`;
                    clone.querySelector('a:nth-child(2) span').textContent = response.user;
                    now = new Date();
                    clone.querySelector('span:nth-child(3)').textContent = now.toLocaleDateString("fr") + ` à ${now.getHours()}h${now.getMinutes()}`;
                    clone.querySelector('span:nth-child(4)').textContent = response.comment;
                    if(clone.querySelector('span:nth-child(5)')) {
                        clone.querySelector('span:nth-child(5)').dataset.commentId = response.id;
                        clone.querySelector('span:nth-child(5)').addEventListener('click', deleteComment);
                    }
                    document.querySelector('.comments-list').appendChild(clone);
                    // +1 au nombre de commentaires
                    document.querySelector('.comments h3 span').textContent = parseInt(document.querySelector('.comments h3 span').textContent) + 1;
                    if(response.myPost) {
                        document.querySelector('.author-info span').textContent = parseInt(document.querySelector('.author-info span').textContent) + 1;
                    }
                    // Reset de l'input commentaire
                    document.querySelector('#comment').value = '';
                }

            } else {
                // Le serveur a renvoyé un status d'erreur
            }
        }
    }
}

function deleteComment(e) {

    console.log('test');

    const slug = document.querySelector('#slug').value;

    console.log(this.dataset);
    let data = new FormData();
    data.append('commentId', this.dataset.commentId);

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
                    this.parentNode.remove();
                    // -1 au nombre de commentaires
                    document.querySelector('.comments h3 span').textContent = parseInt(document.querySelector('.comments h3 span').textContent) - 1;
                    if(response.myPost) {
                        document.querySelector('.author-info span').textContent = parseInt(document.querySelector('.author-info span').textContent) - 1;
                    }
                }
            } else {
                // Le serveur a renvoyé un status d'erreur
            }
        }
    }
}