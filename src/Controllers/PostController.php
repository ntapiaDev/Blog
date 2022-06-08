<?php
namespace App\Controllers;

use App\Core\Form;
use App\Models\CommentModel;
use App\Models\PostModel;
use App\Models\UserModel;

class PostController extends Controller
{
    /**
     * Affiche la page principale du blog
     *
     * @return void
     */
    public function show($slug)
    {   
        if(isset($_SESSION['user'])) {
            $loggedUser = $_SESSION['user'];
        } else {
            $loggedUser = [];
        }

        $postModel = new PostModel;

        $post = $postModel->findOneBySlug($slug);

        if(!$post) {
            header('Location: /main/notfound');
            exit;
        }

        $userModel = new UserModel;

        $user = $userModel->findOneById($post->user);
        $userPosts = $userModel->getPosts($post->user);
        $userComments = $userModel->getComments($post->user);

        $commentModel = new CommentModel;

        $comments = $commentModel->findAllByPostId($post->id);

        //DELETE comment
        if((isset($_POST['commentId']))) {

            if(!isset($_SESSION['user']['id'])) {
                echo json_encode([
                    'code' => '401',
                    'message' => 'Merci de vous connecter'
                ]);
                exit;
            }

            if($_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
                echo json_encode([
                    'code' => '403',
                    'message' => 'Action interdite'
                ]);
                exit;
            }

            $commentModel = new CommentModel;
            $comment = $commentModel->delete($_POST['commentId']);

            $isMyPost = $post->user == $_SESSION['user']['id'];

            echo json_encode([
                'code' => '200',
                'message' => 'Commentaire supprimé',
                'myPost' => $isMyPost
            ]);
            exit;
        }

        //Traitement AJAX du formulaire :
        if((isset($_POST['comment']))) {

            if(!isset($_SESSION['user']['id'])) {
                echo json_encode([
                    'code' => '401',
                    'message' => 'Merci de vous connecter'
                ]);
                exit;
            }

            if(empty($_POST['comment'])) {
                echo json_encode([
                    'code' => '400',
                    'message' => 'Merci d\'écrire un commentaire'
                ]);
                exit;
            }

            if(!empty($_POST['id']) && $_POST['id'] === $post->id) {

                $comment = new CommentModel;
                $comment->setComment(strip_tags($_POST['comment']))
                    ->setUser($_SESSION['user']['id'])
                    ->setPost($_POST['id']);
                $comment->create();

                $CommentModel = new CommentModel;
                $newComment = $CommentModel->findLastComment($_SESSION['user']['id']);

                $currentUser = $userModel->findOneById($_SESSION['user']['id']);

                $isMyPost = $post->user == $_SESSION['user']['id'];

                echo json_encode([
                    'code' => '200',
                    'message' => 'Commentaire envoyé',
                    'user_id' => $currentUser->id,
                    'avatar' => $currentUser->avatar,
                    'user' => $currentUser->firstname . ' ' . $currentUser->lastname,
                    'comment' => strip_tags($_POST['comment']),
                    'id' => $newComment->id,
                    'myPost' => $isMyPost
                ]);
                exit;
                
            } else {
                echo json_encode([
                    'code' => '400',
                    'message' => 'Erreur, merci de réessayer'
                ]);
                exit;
            }
        }

        //Formulaire de commentaires :
        $form = new Form;
        $form->debutForm('post', '', ['id' => 'comment-form'])
            ->ajoutInput('text', 'comment', ['id' => 'comment', 'class' => '', 'placeholder' => 'Ajouter un commentaire'])
            ->ajoutInput('text', 'slug', ['id' => 'slug', 'class' => '', 'value' => $post->slug, 'hidden' => true])
            ->ajoutInput('int', 'id', ['id' => 'id', 'class' => '', 'value' => $post->id, 'hidden' => true])
            ->ajoutBouton('Envoyer', ['class' => 'comment-btn'])
            ->finForm();

        $this->twig->display('post/show.html.twig', [
            'post' => $post,
            'author' => $user,
            'userPosts' => count($userPosts),
            'userComments' => count($userComments),
            'comments' => $comments,
            'currentUser' => isset($_SESSION['user']) ? $_SESSION['user']['id'] : '',
            'userRole' => isset($_SESSION['user']) ? $_SESSION['user']['roles'] : '',
            'user' => $loggedUser,
            'commentForm' => $form->create()
        ]);
    }

    public function new()
    {
        if(!isset($_SESSION['user']) || $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            header('Location: /main/forbidden');
            exit;
        }
        $user = $_SESSION['user'];

        $title = isset($_POST['title']) ? strip_tags($_POST['title']) : '';
        $hook = isset($_POST['hook']) ? strip_tags($_POST['hook']) : '';
        // $content = isset($_POST['content']) ? strip_tags($_POST['content']) : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        if(!empty($_POST)) {
            if(Form::validate($_POST, ['title', 'hook', 'category', 'content']) && Form::validate($_FILES, ['image'])) {
                // $title = strip_tags($_POST['title']);
                // $hook = strip_tags($_POST['hook']);
                // $content = strip_tags($_POST['content']);
                $category = $_POST['category'];

                $upload_dir = ROOT . '/public/uploads/';
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $file_name = basename(md5(rand()) . '.' . $ext);
                $upload_file = $upload_dir . $file_name;
                move_uploaded_file($_FILES["image"]["tmp_name"], $upload_file);
                
                $post = new PostModel;
                $post->setTitle($title)
                    ->setHook($hook)
                    ->setCategory($category)
                    ->setImage($file_name)
                    ->setContent($content)
                    ->setUser($_SESSION['user']['id'])
                    ->setSlug($title);

                // var_dump($post);
                $post->create();
                header('Location: /#destinations');
                exit;
            }
        }

        $form = new Form;

        $form->debutForm('post', '#', ['enctype' => 'multipart/form-data'])
            ->ajoutLabelFor('title', 'Titre :')
            ->ajoutInput('text', 'title', ['id' => 'title', 'class' => '', 'placeholder' => 'Titre', 'value' => $title])
            ->ajoutLabelFor('hook', 'Chapô :')
            ->ajoutInput('text', 'hook', ['id' => 'hook', 'class' => '', 'placeholder' => 'Chapô', 'value' => $hook])
            ->ajoutLabelFor('category', 'Catégorie :')
            ->ajoutSelect('category', ['1"' => 'Forêt', '2"' => 'Montagne', '3"' => 'Lac', '4"' => 'Océan'], ['id' => 'category', 'class' => ''])
            ->ajoutLabelFor('image', 'Illustration :')
            ->ajoutInput('file', 'image', ['id' => 'image', 'class' => '',  'accept' => "image/jpeg, image/png"])
            ->ajoutLabelFor('content', 'Contenu :')
            ->ajoutTextarea('content', $content, ['id' => 'content', 'class' => ''])
            ->ajoutBouton('Créer un post', ['class' => ''])
            ->finForm();

        $this->twig->display('post/new.html.twig', [
            'newForm' => $form->create(),
            'message' => isset($_SESSION['erreur']) ? $_SESSION['erreur'] : '',
            'user' => $user
        ]);
        unset($_SESSION['erreur']);
    }

    public function edit($slug)
    {   
        
        $postModel = new PostModel;

        $post = $postModel->findOneBySlug($slug);

        if(!isset($_SESSION['user']) || $post->user !== $_SESSION['user']['id'] && $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            header('Location: /main/forbidden');
            exit;
        }
        $user = $_SESSION['user'];

        $title = isset($_POST['title']) ? strip_tags($_POST['title']) : $post->title;
        $hook = isset($_POST['hook']) ? strip_tags($_POST['hook']) : $post->hook;
        // $content = isset($_POST['content']) ? strip_tags($_POST['content']) : $post->content;
        $content = isset($_POST['content']) ? $_POST['content'] : $post->content;
        $category = isset($_POST['category']) ? $_POST['category'] : $post->category;
        if(!empty($_POST)) {
            if(Form::validate($_POST, ['title', 'hook', 'category', 'content']) && ($_FILES['image']['name'] === '' || Form::validate($_FILES, ['image']))) {

                $newPost = new PostModel;
                $newPost->setId($post->id)
                    ->setTitle($title)
                    ->setHook($hook)
                    ->setCategory($category)
                    ->setContent($content)
                    ->setUser($_SESSION['user']['id'])
                    ->setSlug($title);

                if($_FILES['image']['name'] !== '') {
                    $upload_dir = ROOT . '/public/uploads/';
                    $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                    $file_name = basename(md5(rand()) . '.' . $ext);
                    $upload_file = $upload_dir . $file_name;
                    move_uploaded_file($_FILES["image"]["tmp_name"], $upload_file);
                    $newPost->setImage($file_name);
                }

                $newPost->update();
                $slug = $newPost->getSlug();

                header('Location: /post/show/' . $slug);
                exit;
            }
        }

        $form = new Form;

        $select1 = $category === '1' ? '1" selected' : '1"';
        $select2 = $category === '2' ? '2" selected' : '2"';
        $select3 = $category === '3' ? '3" selected' : '3"';
        $select4 = $category === '4' ? '4" selected' : '4"';
        $form->debutForm('post', '#', ['enctype' => 'multipart/form-data'])
            ->ajoutLabelFor('title', 'Titre :')
            ->ajoutInput('text', 'title', ['id' => 'title', 'class' => '', 'placeholder' => 'Titre', 'value' => $title])
            ->ajoutLabelFor('hook', 'Chapô :')
            ->ajoutInput('text', 'hook', ['id' => 'hook', 'class' => '', 'placeholder' => 'Chapô', 'value' => $hook])
            ->ajoutLabelFor('category', 'Catégorie :')
            ->ajoutSelect('category', [$select1 => 'Forêt', $select2 => 'Montagne', $select3 => 'Lac', $select4 => 'Océan'], ['id' => 'category', 'class' => ''])
            ->ajoutLabelFor('image', 'Illustration :')
            ->ajoutInput('file', 'image', ['id' => 'image', 'class' => '',  'accept' => "image/jpeg, image/png"])
            ->ajoutLabelFor('content', 'Contenu :')
            ->ajoutTextarea('content', $content, ['id' => 'content', 'class' => ''])
            ->ajoutBouton('Éditer le post', ['class' => ''])
            ->finForm();
        
        $this->twig->display('post/edit.html.twig', [
            'image' => $post->image,
            'editForm' => $form->create(),
            'message' => isset($_SESSION['erreur']) ? $_SESSION['erreur'] : '',
            'user' => $user
        ]);
        unset($_SESSION['erreur']);
    }

    public function delete($slug)
    {
        $postModel = new PostModel;

        $post = $postModel->findOneBySlug($slug);

        if(!$post) {
            header('Location: /main/notfound');
            exit;
        }

        if(!isset($_SESSION['user']) || $post->user !== $_SESSION['user']['id'] && $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            header('Location: /main/forbidden');
            exit;
        }

        $postModel->delete($post->id);
        $_SERVER['HTTP_REFERER'] === 'http://blog/admin' ? header('Location: '. $_SERVER['HTTP_REFERER']) : header('Location: /#destinations');
        exit;
    }

    public function deleteComment($id)
    {
        $commentModel = new CommentModel;

        $comment = $commentModel->find($id);

        if(!$comment) {
            header('Location: /main/notfound');
            exit;
        }

        if(!isset($_SESSION['user']) || $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            header('Location: /main/forbidden');
            exit;
        }

        $commentModel->delete($comment->id);
        header('Location: '. $_SERVER['HTTP_REFERER']);
        exit;
    }
}