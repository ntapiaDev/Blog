<?php
namespace App\Controllers;

use App\Core\Form;
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
        $postModel = new PostModel;

        $post = $postModel->findOneBySlug($slug);

        $userModel = new UserModel;

        $user = $userModel->findOneById($post->user);

        $this->twig->display('post/show.html.twig', [
            'post' => $post,
            'user' => $user,
            'currentUser' => $_SESSION['user']['id']
        ]);
    }

    public function new()
    {
        if(!isset($_SESSION['user']) || $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            header('Location: /main/forbidden');
            exit;
        }

        $title = isset($_POST['title']) ? strip_tags($_POST['title']) : '';
        $hook = isset($_POST['hook']) ? strip_tags($_POST['hook']) : '';
        $content = isset($_POST['content']) ? strip_tags($_POST['content']) : '';
        if(!empty($_POST)) {
            if(Form::validate($_POST, ['title', 'hook', 'category', 'content'])) {
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
            ->ajoutSelect('category', ['1' => 'Forêt', '2' => 'Montagne', '3' => 'Lac', '4' => 'Océan'], ['id' => 'category', 'class' => ''])
            ->ajoutLabelFor('image', 'Illustration :')
            ->ajoutInput('file', 'image', ['id' => 'image', 'class' => '',  'accept' => "image/jpeg, image/png"])
            ->ajoutLabelFor('content', 'Contenu :')
            ->ajoutTextarea('content', $content, ['id' => 'content', 'class' => ''])
            ->ajoutBouton('Créer un post', ['class' => ''])
            ->finForm();

        $this->twig->display('post/new.html.twig', [
            'newForm' => $form->create(),
            'message' => isset($_SESSION['erreur']) ? $_SESSION['erreur'] : ''
        ]);
        unset($_SESSION['erreur']);
    }

    public function edit($slug)
    {   
        $postModel = new PostModel;

        $post = $postModel->findOneBySlug($slug);

        if($post->user !== $_SESSION['user']['id']) {
            header('Location: /main/forbidden');
            exit;
        }

        $title = isset($_POST['title']) ? strip_tags($_POST['title']) : $post->title;
        $hook = isset($_POST['hook']) ? strip_tags($_POST['hook']) : $post->hook;
        $content = isset($_POST['content']) ? strip_tags($_POST['content']) : $post->content;
        $category = isset($_POST['category']) ? $_POST['category'] : $post->category;
        if(!empty($_POST)) {
            if(Form::validate($_POST, ['title', 'hook', 'category', 'content'])) {

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
            'editForm' => $form->create()
        ]);
    }
}