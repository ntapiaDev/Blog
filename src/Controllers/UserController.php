<?php
namespace App\Controllers;

class UserController extends Controller
{

    /**
     * Profil de l'utilisateur
     *
     * @return void
     */
    public function index()
    {
        $this->twig->display('user/index.html.twig');
    }

    /**
     * Connexion de l'utilisateur
     *
     * @return void
     */
    public function login()
    {
        $this->twig->display('user/login.html.twig');
    }

    /**
     * Inscription de l'utilisateur
     *
     * @return void
     */
    public function register()
    {
        $this->twig->display('user/register.html.twig');
    }

    /**
     * Déconnecte l'utilisateur
     *
     * @return exit
     */
    public function logout() {
        unset($_SESSION['user']);
        header('Location: '. $_SERVER['HTTP_REFERER']);
        exit;
    }
}