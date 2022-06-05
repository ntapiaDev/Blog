<?php
namespace App\Core;

use App\Controllers\MainController;

Class Main
{
    public function start()
    {
        session_start();

        // http://domaine/controller/method/params
        // http://domaine/index.php?p=controller/method/params

        $uri = $_SERVER['REQUEST_URI'];
        if(!empty($uri) && $uri != '/' && $uri[-1] === "/") {
            $uri = substr($uri, 0, -1);
            http_response_code(301);
            header('Location: '.$uri);
            exit; //Solves the problem?
        }

        //On gère les paramètres de l'URL
        $params = explode('/', $_GET['p']);

        if($params[0] != '') {
            //On a au moins un paramètre
            $controller = '\\App\\Controllers\\' . ucfirst(array_shift($params)) . 'Controller';
            
            //On instancie le controller
            $controller = new $controller();

            //S'il n'y a pas d'autres paramètres dans l'url, il charge la méthode par défaut index, sinon il charge la méthode appelée
            $action = (isset($params[0])) ? array_shift($params) : 'index';

            if(method_exists($controller, $action)) {
                //Si il reste des paramètres, on les passe à la méthode
                (isset($params[0])) ? call_user_func_array([$controller, $action], $params) : $controller->$action();

            } else {
                http_response_code(404);
                header('Location: /main/notfound');
                exit;
            }

        } else {
            //On a pas de paramètres, on instancie le controller par défaut
            $controller = new MainController;
            $controller->index();
        }
    }
}