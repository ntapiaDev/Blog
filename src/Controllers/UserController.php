<?php
namespace App\Controllers;

use App\Core\Form;
use App\Models\UserModel;

class UserController extends Controller
{

    /**
     * Profil de l'utilisateur
     *
     * @return void
     */
    public function index()
    {
        if(!isset($_SESSION['user'])) {
            header('Location: /user/login');
            exit;
        }

        $userModel = new UserModel;
        $user = $userModel->findOneById($_SESSION['user']['id']);

        $this->twig->display('user/index.html.twig', [
            'user' => $user
        ]);
    }

    /**
     * Connexion de l'utilisateur
     *
     * @return void
     */
    public function login()
    {
        if(isset($_SESSION['user'])) {
            header('Location: /user');
            exit;
        }

        if(!empty($_POST)) {
            if(isset($_POST['email']) && !empty($_POST['email']) && isset($_POST['password']) && !empty($_POST['password'])) {
                $userModel = new UserModel;
                $userArray = $userModel->findOneByEmail(strip_tags($_POST['email']));
                if(!$userArray) {
                    $_SESSION['erreur'] = 'L\'adresse email et/ou le mot de passe est incorrect.';
                    header('Location: /user/login');
                    exit;
                }

                $user = $userModel->hydrate($userArray);
                if(password_verify($_POST['password'], $user->getPassword())) {
                    $user->setSession();
                    header('Location: /user');
                    exit;
                } else {
                    $_SESSION['erreur'] = 'L\'adresse email et/ou le mot de passe est incorrect.';
                    header('Location: /user/login');
                    exit;
                }
            } else {
                $_SESSION['erreur'] = "Vous n'avez pas rempli tous les champs demandés.";
            }
        }

        $form = new Form;

        $form->debutForm()
            ->ajoutLabelFor('email', 'Votre email :')
            ->ajoutInput('email', 'email', ['id' => 'email', 'class' => '', 'placeholder' => 'Adresse email'])
            ->ajoutLabelFor('password', 'Votre mot de passe :')
            ->ajoutInput('password', 'password', ['id' => 'password', 'class' => '', 'placeholder' => 'Mot de passe'])
            ->ajoutBouton('Me connecter', ['class' => ''])
            ->finForm();

        $this->twig->display('user/login.html.twig', [
            'loginForm' => $form->create(),
            'message' => isset($_SESSION['erreur']) ? $_SESSION['erreur'] : ''
        ]);
        unset($_SESSION['erreur']);
    }

    /**
     * Inscription de l'utilisateur
     *
     * @return void
     */
    public function register()
    {
        if(isset($_SESSION['user'])) {
            header('Location: /user');
            exit;
        }

        if(!empty($_POST)) {
            if(Form::validate($_POST, ['email', 'password', 'firstname', 'lastname', 'cgu']) && $_FILES['image']['name'] === '' || Form::validate($_FILES, ['image'])) {
                $email = strip_tags($_POST['email']);
                $password = password_hash($_POST['password'], PASSWORD_ARGON2I);
                $firstname = strip_tags($_POST['firstname']);
                $lastname = strip_tags($_POST['lastname']);
                
                $user = new UserModel;
                $user->setEmail($email)
                    ->setPassword($password)
                    ->setRoles('ROLE_USER')
                    ->setFirstname($firstname)
                    ->setLastname($lastname);

                if(!empty($_POST['hook'])) {
                    $hook = strip_tags($_POST['hook']);
                    $user->setHook($hook);
                }

                if($_FILES['image']['name'] !== '') {
                    $upload_dir = ROOT . '/public/uploads/';
                    $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                    $file_name = basename(md5(rand()) . '.' . $ext);
                    $upload_file = $upload_dir . $file_name;
                    move_uploaded_file($_FILES["image"]["tmp_name"], $upload_file);
                    $user->setAvatar($file_name);
                }
    
                $user->create();
                header('Location: /user/login');
                exit;
            }
        } 
        $email = isset($_POST['email']) ? strip_tags($_POST['email']) : '';
        $firstname = isset($_POST['firstname']) ? strip_tags($_POST['firstname']) : '';
        $lastname = isset($_POST['lastname']) ? strip_tags($_POST['lastname']) : '';
        $hook = isset($_POST['hook']) ? strip_tags($_POST['hook']) : '';

        $form = new Form;

        $form->debutForm('post', '#', ['enctype' => 'multipart/form-data'])
            ->ajoutLabelFor('firstname', 'Votre prénom :')
            ->ajoutInput('text', 'firstname', ['id' => 'firstname', 'class' => '', 'placeholder' => 'Prénom', 'value' => $firstname])
            ->ajoutLabelFor('lastname', 'Votre nom :')
            ->ajoutInput('text', 'lastname', ['id' => 'lastname', 'class' => '', 'placeholder' => 'Nom', 'value' => $lastname])
            ->ajoutLabelFor('avatar', 'Votre avatar :')
            ->ajoutInput('file', 'image', ['id' => 'avatar', 'class' => '',  'accept' => "image/gif, image/jpeg, image/png"])
            ->ajoutLabelFor('hook', 'Votre accroche :')
            ->ajoutInput('text', 'hook', ['id' => 'hook', 'class' => '', 'placeholder' => 'Accroche', 'value' => $hook])
            ->ajoutLabelFor('email', 'Votre email :')
            ->ajoutInput('email', 'email', ['id' => 'email', 'class' => '', 'placeholder' => 'Adresse email', 'value' => $email])
            ->ajoutLabelFor('password', 'Votre mot de passe :')
            ->ajoutInput('password', 'password', ['id' => 'password', 'class' => '', 'placeholder' => 'Mot de passe'])
            ->ajoutInput('checkbox', 'cgu', ['id' => 'cgu', 'class' => 'checkbox-box'])
            ->ajoutLabelFor('cgu', 'J\'accepte les conditions générales d\'utilisation de ce site', ['class' => 'checkbox-label'])
            ->ajoutBouton('M\'inscrire', ['class' => ''])
            ->finForm();

        $this->twig->display('user/register.html.twig', [
            'registerForm' => $form->create(),
            'message' => isset($_SESSION['erreur']) ? $_SESSION['erreur'] : ''
        ]);
        unset($_SESSION['erreur']);
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