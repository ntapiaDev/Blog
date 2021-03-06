<?php
namespace App\Controllers;

use App\Core\Form;
use App\Models\CommentModel;
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
        } else {
            header('Location: /user/show/' . $_SESSION['user']['id']);
        }

        // $userModel = new UserModel;
        // $user = $userModel->findOneById($_SESSION['user']['id']);

        // $this->twig->display('user/index.html.twig', [
        //     'user' => $user
        // ]);
    }

    /**
     * Affiche la page d'un utilisateur
     *
     * @param integer $id de l'utilisateur recherché, par défaut l'id de l'utilisateur connecté
     * @return void
     */
    public function show(int $id)
    {
        if(isset($_SESSION['user'])) {
            $currentUser = $_SESSION['user'];
        } else {
            $currentUser = [];
        }

        $userModel = new UserModel;
        $user = $userModel->find($id);

        if(!$user) {
            header('Location: /main/notfound');
            exit;
        }

        $posts = $userModel->getPostsWithDetails($id);

        $commentModel = new CommentModel;
        foreach($posts as $post) {
            $comments = COUNT($commentModel->findAllByPostId($post->id));
            $post->comments = $comments;
        }

        $comments = $userModel->getCommentsWithDetails($id);

        $this->twig->display('user/show.html.twig', [
            'userShown' => $user,
            'posts' => $posts,
            'comments' => $comments,
            'user' => $currentUser
        ]);
    }

    public function edit(int $id)
    {
        $userModel = new UserModel;
        $user = $userModel->find($id);

        if(!$user) {
            header('Location: /main/notfound');
            exit;
        }

        if(!isset($_SESSION['user']) || $user->id !== $_SESSION['user']['id'] && $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            header('Location: /main/forbidden');
            exit;
        }
        $currentUser = $_SESSION['user'];

        $email = isset($_POST['email']) ? strip_tags($_POST['email']) : $user->email;
        $firstname = isset($_POST['firstname']) ? strip_tags($_POST['firstname']) : $user->firstname;
        $lastname = isset($_POST['lastname']) ? strip_tags($_POST['lastname']) : $user->lastname;
        $hook = isset($_POST['hook']) ? strip_tags($_POST['hook']) : $user->hook;
        if(!empty($_POST)) {
            if(Form::validate($_POST, ['email', 'firstname', 'lastname']) && ($_FILES['image']['name'] === '' || Form::validate($_FILES, ['image']))) {                
                $editedUser = new UserModel;
                $editedUser->setId($user->id)
                    ->setEmail($email)
                    ->setFirstname($firstname)
                    ->setLastname($lastname);

                if(!empty($_POST['hook'])) {
                    $hook = strip_tags($_POST['hook']);
                    $editedUser->setHook($hook);
                }

                if($_FILES['image']['name'] !== '') {
                    $upload_dir = ROOT . '/public/uploads/';
                    $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                    $file_name = basename(md5(rand()) . '.' . $ext);
                    $upload_file = $upload_dir . $file_name;
                    move_uploaded_file($_FILES["image"]["tmp_name"], $upload_file);
                    $editedUser->setAvatar($file_name);
                }
    
                $editedUser->update();
                $_SESSION['user']['roles'] === 'ROLE_ADMIN' ? header('Location: /admin') : header('Location: /user');
                exit;
            }
        }

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
            ->ajoutBouton('Mettre à jour', ['class' => ''])
            ->finForm();

        $this->twig->display('user/edit.html.twig', [
            'user' => $currentUser,
            'editedUser' => $user,
            'editForm' => $form->create(),
            'message' => isset($_SESSION['erreur']) ? $_SESSION['erreur'] : ''
        ]);
        unset($_SESSION['erreur']);
    }

    public function delete($id)
    {
        $userModel = new UserModel;

        $user = $userModel->find($id);

        if(!$user) {
            header('Location: /main/notfound');
            exit;
        }

        if(!isset($_SESSION['user']) || $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
            header('Location: /main/forbidden');
            exit;
        }

        $userModel->delete($user->id);
        $_SERVER['HTTP_REFERER'] === 'http://blog/admin' ? header('Location: '. $_SERVER['HTTP_REFERER']) : header('Location: /');
        exit;
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
        header('Location: /user');
        // header('Location: '. $_SERVER['HTTP_REFERER']);
        exit;
    }
}