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

        //FILTRE CATEGORY AJAX
        if(isset($_POST['category'])) {
            if($_POST['category'] !== '0') {
                $posts = $postModel->findIdByCategory($_POST['category']);
                foreach($posts as $post) {
                    $displayPosts[] = $post->id;
                }
            } else {
                $posts = $postModel->findAll();
                foreach($posts as $post) {
                    $displayPosts[] = $post->id;
                }
            }
            
            echo json_encode([
                'code' => 200,
                'posts' => $displayPosts
            ]);
            exit;
        }

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

    public function notfound()
    {
        $this->twig->display('main/notfound.html.twig');
    }
}