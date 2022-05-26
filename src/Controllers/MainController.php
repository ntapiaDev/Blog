<?php
namespace App\Controllers;

class MainController extends Controller
{
    public function index()
    {
        $this->twig->display('main/index.html.twig');
    }
}