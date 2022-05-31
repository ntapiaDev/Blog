<?php
namespace App\Controllers;

use App\Core\Form;
use App\Models\PostModel;

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

        $this->twig->display('post/show.html.twig', [
            'post' => $post
        ]);
    }

    public function new()
    {
        if(!isset($_SESSION['user']) || $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            //Redirection page 403
            header('Location: /');
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
                    ->setSlug(strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title))));

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
            'NewForm' => $form->create(),
            'message' => isset($_SESSION['erreur']) ? $_SESSION['erreur'] : ''
        ]);
        unset($_SESSION['erreur']);
    }
}