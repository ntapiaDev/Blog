<?php
namespace App\Controllers;

use App\Models\CommentModel;
use App\Models\PostModel;
use App\Models\UserModel;

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
        $currentUser = $_SESSION['user'];

        //Récupération des articles
        $postModel = new PostModel;
        $posts = $postModel->findAllWithDetails();

        $commentModel = new CommentModel;
        foreach($posts as $post) {
            $comments = COUNT($commentModel->findAllByPostId($post->id));
            $post->comments = $comments;
        }

        //Récupération des commentaires
        $commentModel = new CommentModel;
        $comments = $commentModel->findAllWithDetails();

        //Récupération des utilisateurs
        $userModel = new UserModel;
        $users = $userModel->findAll();

        foreach($users as $user) {
            $nbArticles = COUNT($userModel->getPosts($user->id));
            $nbComments = COUNT($userModel->getComments($user->id));
            $user->articles = $nbArticles;
            $user->comments = $nbComments;
        }

        $this->twig->display('admin/index.html.twig', [
            'user' => $currentUser,
            'posts' => $posts,
            'comments' => $comments,
            'users' => $users
        ]);
    }
}