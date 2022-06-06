<?php
namespace App\Models;

Class UserModel extends Model
{
    protected $id;
    protected $email;
    protected $password;
    protected $roles;
    protected $firstname;
    protected $lastname;
    protected $avatar;
    protected $hook;

    public function __construct()
    {
        $class = str_replace(__NAMESPACE__.'\\', '', __CLASS__);
        $this->table = strtolower(str_replace('Model', '', $class));
    }

    /**
     * Récupère un user à partir de son email (surcharge des méthodes du Model.php)
     *
     * @param string $email
     * @return mixed
     */
    public function findOneByEmail(string $email)
    {
        return $this->request("SELECT * FROM $this->table WHERE email = ?", [$email])->fetch();
    }

    /**
     * Récupère le nom d'un user à partir de son id
     *
     * @param integer $id
     * @return mixed
     */
    public function findOneById(int $id)
    {
        return $this->request("SELECT * FROM $this->table WHERE id = ?", [$id])->fetch();
    }

    /**
     * Récupère la liste des articles d'un utilisateur
     *
     * @param integer $id
     * @return array
     */
    public function getPosts(int $id): array
    {
        return $this->request("SELECT * FROM post WHERE user = ?", [$id])->fetchAll();
    }

    public function getPostsWithDetails(int $id): array
    {
        return $this->request("SELECT p.*, DATE_FORMAT(p.created_at, '%d/%m/%Y à %Hh%i') as formated_created_at, c.type as category_name FROM post p INNER JOIN category c ON p.category = c.id WHERE p.user = ?", [$id])->fetchAll();
    }

    /**
     * Récupère la liste des commentaires d'un utilisateur
     *
     * @param integer $id
     * @return array
     */
    public function getComments(int $id): array
    {
        return $this->request("SELECT * FROM comment WHERE user = ?", [$id])->fetchAll();
    }

    public function getCommentsWithDetails(int $id): array
    {
        return $this->request("SELECT c.*, DATE_FORMAT(c.created_at, '%d/%m/%Y à %Hh%i') as formated_created_at, p.title, p.slug, p.image FROM comment c INNER JOIN post p ON c.post = p.id WHERE c.user = ?", [$id])->fetchAll();
    }

    /**
     * Crée la session utilisateur à la connexion
     *
     * @return void
     */
    public function setSession()
    {
        $_SESSION['user'] = [
            'id' => $this->id,
            'email' => $this->email,
            'roles' => $this->getRoles()
        ];
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of password
     */ 
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */ 
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get the value of roles
     */ 
    public function getRoles(): string
    {
        return $this->roles;
    }

    /**
     * Set the value of roles
     *
     * @return  self
     */ 
    public function setRoles($roles)
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get the value of firstname
     */ 
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @return  self
     */ 
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */ 
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @return  self
     */ 
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of avatar
     */ 
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * Set the value of avatar
     *
     * @return  self
     */ 
    public function setAvatar($avatar)
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * Get the value of hook
     */ 
    public function getHook()
    {
        return $this->hook;
    }

    /**
     * Set the value of hook
     *
     * @return  self
     */ 
    public function setHook($hook)
    {
        $this->hook = $hook;

        return $this;
    }
}