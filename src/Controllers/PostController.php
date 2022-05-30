<?php
namespace App\Controllers;

use App\Core\Form;

class PostController extends Controller
{
    /**
     * Affiche la page principale du blog
     *
     * @return void
     */
    public function show()
    {
        $this->twig->display('post/show.html.twig');
    }

    public function new()
    {
        if(!isset($_SESSION['user']) || $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            //Redirection page 403
            header('Location: /');
            exit;
        }

        $form = new Form;

        $form->debutForm('post', '#', ['enctype' => 'multipart/formdata'])
            ->ajoutLabelFor('title', 'Titre :')
            ->ajoutInput('text', 'title', ['id' => 'title', 'class' => '', 'placeholder' => 'Titre'])
            ->ajoutLabelFor('hook', 'Chapô :')
            ->ajoutInput('text', 'hook', ['id' => 'hook', 'class' => '', 'placeholder' => 'Chapô'])
            ->ajoutLabelFor('category', 'Catégorie :')
            ->ajoutSelect('category', ['forest' => 'Forêt', 'mountain' => 'Montagne', 'lake' => 'Lac', 'ocean' => 'Océan'], ['id' => 'category', 'class' => ''])
            ->ajoutLabelFor('image', 'Illustration :')
            ->ajoutInput('file', 'image', ['id' => 'image', 'class' => '',  'accept' => "image/jpeg, image/png"])
            ->ajoutLabelFor('content', 'Contenu :')
            ->ajoutTextarea('content', '', ['id' => 'content', 'class' => ''])
            ->ajoutBouton('Créer un post', ['class' => ''])
            ->finForm();

        $this->twig->display('post/new.html.twig', [
            'NewForm' => $form->create(),
            'message' => isset($_SESSION['erreur']) ? $_SESSION['erreur'] : ''
        ]);
        unset($_SESSION['erreur']);
    }
}