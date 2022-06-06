<?php
namespace App\Controllers;

use App\Models\CommentModel;
use App\Models\PostModel;

class AdminController extends Controller
{
    /**
     * Page d'administration du site
     *
     * @return void
     */
    public function index()
    {
        if(!isset($_SESSION['user']) || $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            header('Location: /main/forbidden');
            exit;
        }
        $user = $_SESSION['user'];

        $postModel = new PostModel;
        $posts = $postModel->findAllWithDetails();

        $commentModel = new CommentModel;
        foreach($posts as $post) {
            $comments = COUNT($commentModel->findAllByPostId($post->id));
            $post->comments = $comments;
        }

        $this->twig->display('admin/index.html.twig', [
            'user' => $user,
            'posts' => $posts
        ]);
    }
}