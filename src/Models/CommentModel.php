<?php
namespace App\Models;

Class CommentModel extends Model
{
    protected $id;
    protected $comment;
    protected $created_at;
    protected $user;
    protected $post;

    public function __construct()
    {
        $class = str_replace(__NAMESPACE__.'\\', '', __CLASS__);
        $this->table = strtolower(str_replace('Model', '', $class));
    }

    public function findAllByPostId(int $id): array
    {
        $query = $this->request("SELECT c.*, DATE_FORMAT(c.created_at, '%d/%m/%Y à %Hh%i') as formated_created_at, u.firstname, u.lastname, u.avatar FROM $this->table c INNER JOIN user u ON c.user = u.id WHERE post = ? ORDER BY c.id", [$id]);
        return $query->fetchAll();
    }

    /**
     * Sélectionne le dernier commentaire d'un utilisateur
     *
     * @param integer $id de l'utilisateur
     * @return Comment
     */
    public function findLastComment(int $id)
    {
        $query = $this->request("SELECT * FROM $this->table WHERE user = ? ORDER BY id DESC LIMIT 1", [$id]);
        return $query->fetch();
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
     * Get the value of comment
     */ 
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set the value of comment
     *
     * @return  self
     */ 
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the value of created_at
     */ 
    public function getCreated_at()
    {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     *
     * @return  self
     */ 
    public function setCreated_at($created_at)
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * Get the value of user
     */ 
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set the value of user
     *
     * @return  self
     */ 
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of post
     */ 
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set the value of post
     *
     * @return  self
     */ 
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }
}