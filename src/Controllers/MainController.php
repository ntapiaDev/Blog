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
        $this->twig->display('main/index.html.twig');
    }
}