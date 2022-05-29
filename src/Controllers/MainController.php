<?php
namespace App\Controllers;

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

        $this->twig->display('main/index.html.twig', [
            'user' => $user
        ]);
    }
}