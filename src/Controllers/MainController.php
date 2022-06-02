<?php
namespace App\Controllers;

use App\Models\PostModel;

class MainController extends Controller
{
    /**
     * Affiche la page principale du blog
     *
     * @return void
     */
    public function index()
    {
        if(isset($_SESSION['user'])) {
            $user = $_SESSION['user'];
        } else {
            $user = [];
        }

        $postModel = new PostModel;

        $posts = $postModel->findAll();

        $this->twig->display('main/index.html.twig', [
            'user' => $user,
            'posts' => $posts
        ]);
    }

    public function forbidden()
    {
        $this->twig->display('main/forbidden.html.twig');
    }
}