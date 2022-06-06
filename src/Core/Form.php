<?php
namespace App\Core;

use App\Models\UserModel;

Class Form
{
    private $formCode = '';

    /**
     * Génère le formulaire HTML
     *
     * @return void
     */
    public function create()
    {
        return $this->formCode;
    }

    /**
     * Valide si tous les champs proposés sont remplis
     *
     * @param array $form Tableau du formulaire ($_POST ou $_GET)
     * @param array $champs Tableau listant les champs obligatoires
     * @return bool
     */
    public static function validate(array $form, array $champs)
    {
        foreach($champs as $champ) {
            if(!isset($form[$champ]) || empty($form[$champ])) {
                if($champ !== 'cgu') {
                    $_SESSION['erreur'] = "Vous n'avez pas rempli tous les champs demandés.";
                } else {
                    $_SESSION['erreur'] = "Vous devez accepter les conditions d'utilisation du site.";
                }
                return false;
            }
            if($champ === 'email') {
                if(!filter_var($form[$champ], FILTER_VALIDATE_EMAIL)) {
                    $_SESSION['erreur'] = "Votre adresse email n'est pas valide.";
                    return false;
                } else {
                    $userModel = new UserModel;
                    $userArray = $userModel->findOneByEmail(strip_tags($_POST['email']));
            
                    if($userArray) {
                        if($_SESSION['user']['email'] !== $userArray->email && $_SESSION['user']['roles'] !== 'ROLE_ADMIN') {
                            $_SESSION['erreur'] = 'Cette adresse email est déjà utilisée.';
                            return false;
                        }
                    }
                }
            }
            if($champ === 'password') {
                if(strlen($form[$champ]) < 8) {
                    $_SESSION['erreur'] = "Votre mot de passe est trop court (8 caractères minimum).";
                    return false;
                }
            }
            if($champ === 'firstname') {
                if(!preg_match ("/^[a-zA-z]*$/", $form[$champ])) {
                    $_SESSION['erreur'] = "Votre prénom n'est pas valide.";
                    return false;
                }
            }
            if($champ === 'lastname') {
                if(!preg_match ("/^[a-zA-z]*$/", $form[$champ])) {
                    $_SESSION['erreur'] = "Votre nom n'est pas valide.";
                    return false;
                }
            }
            if($champ === 'image') {
                $ext = pathinfo($form["image"]["name"], PATHINFO_EXTENSION);
                if(!$ext) {
                    $_SESSION['erreur'] = 'Merci d\'ajouter une image à votre article.';
                    return false;
                }
                $authorized_ext = ['jpg', 'jpeg', 'jfif', 'pjpeg', 'pjp', 'png'];
                if(!in_array($ext, $authorized_ext)) {
                    $_SESSION['erreur'] = 'Votre fichier n\'est pas une image.';
                    return false;
                }
                if($_FILES['image']['size'] >= 2000000) {
                    $_SESSION['erreur'] = 'Votre fichier est trop gros (2 Mo maximum).';
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Ajoute les attributs envoyés à la balise
     *
     * @param array $attributs Tableau d'attributs ex: ['class' => 'form-control', 'required' => true]
     * @return string Chaine de caractères générée
     */
    private function ajoutAttributs(array $attributs): string
    {
        $str = '';
        $courts = ['checked', 'disabled', 'readonly', 'multiple', 'required', 'autofocus', 'novalidate', 'formnovalidate'];

        foreach($attributs as $attribut => $valeur) {
            if(in_array($attribut, $courts) && $valeur == true) {
                $str .= " $attribut";
            } else {
                $str .= " $attribut=\"$valeur\"";
            }
        }

        return $str;
    }

    /**
     * Balise d'ouverture du formulaire
     *
     * @param string $methode Méthode du formulaire (post ou get)
     * @param string $action Action du formulaire
     * @param array $attributs Attributs
     * @return Form
     */
    public function debutForm(string $methode = 'post', string $action = '#', array $attributs = []): self
    {
        $this->formCode .= "<form action='$action' method='$methode'";
        $this->formCode .= $attributs ? $this->ajoutAttributs($attributs).'>' : '>';

        return $this;
    }

    /**
     * Balise de fermeture du formulaire
     *
     * @return Form
     */
    public function finForm(): self
    {
        $this->formCode .= '</form>';

        return $this;
    }

    public function ajoutLabelFor(string $for, string $texte, array $attributs = []): self
    {
        $this->formCode .= "<label for='$for'";
        $this->formCode .= $attributs ? $this->ajoutAttributs($attributs) : '';
        $this->formCode .= ">$texte</label>";

        return $this;
    }

    public function ajoutInput(string $type, string $nom, array $attributs = []): self
    {
        $this->formCode .= "<input type='$type' name='$nom'";
        $this->formCode .= $attributs ? $this->ajoutAttributs($attributs).'>' : '>';

        return $this;
    }

    public function ajoutTextarea(string $nom, string $valeur = '', array $attributs = []): self
    {
        $this->formCode .= "<textarea name='$nom'";
        $this->formCode .= $attributs ? $this->ajoutAttributs($attributs) : '';
        $this->formCode .= ">$valeur</textarea>";

        return $this;
    }

    public function ajoutSelect(string $nom, array $options, array $attributs = []): self
    {
        $this->formCode .= "<select name='$nom'";
        $this->formCode .= $attributs ? $this->ajoutAttributs($attributs).'>' : '>';

        foreach($options as $valeur => $texte) {
            $this->formCode .= "<option value=\"$valeur>$texte</option>";
        }

        $this->formCode .= '</select>';

        return $this;
    }

    public function ajoutBouton(string $texte, array $attributs = []): self
    {
        $this->formCode .= '<button ';
        $this->formCode .= $attributs ? $this->ajoutAttributs($attributs).'>' : '>';
        $this->formCode .= "$texte</button>";

        return $this;
    }
}