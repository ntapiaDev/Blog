<?php
namespace App\Models;

use App\Core\Db;

Class Model extends Db
{
    protected $table;

    private $db;

// ********** READ **********

    public function findAll()
    {
        $query = $this->request('SELECT * FROM ' . $this->table);
        return $query->fetchAll();
    }

    public function findBy(array $criteres)
    {
        $champs = [];
        $valeurs = [];

        foreach($criteres as $champ => $valeur)
        {
            //SELECT * FROM annonces WHERE actif = ? AND signale = 0
            //bindValue(1, valeur)
            $champs[] = "$champ = ?";
            $valeurs[] = $valeur;
        }
        //On transforme le tableau "champs" en une chaine de caractères
        $liste_champs = implode(' AND ', $champs); // 'actif = ? AND signale = 0'

        return $this->request('SELECT * FROM ' . $this->table . ' WHERE ' . $liste_champs, $valeurs)->fetchAll();
    }

    public function find(int $id)
    {
        return $this->request("SELECT * FROM $this->table WHERE id = $id")->fetch();
    }

// ********** CREATE **********

    public function create()
    {
        $champs = [];
        $inter = []; //Liste des points d'interrogation
        $valeurs = [];

        foreach($this as $champ => $valeur)
        {
            //INSERT INTO annonces (titre, description, actif) VALUES (?, ?, ?)
            if($valeur !== NULL && $champ != 'db' && $champ != 'table') {
                $champs[] = $champ;
                $inter[] = "?";
                $valeurs[] = $valeur;
            }
        }
        $liste_champs = implode(', ', $champs);
        $liste_inter = implode(', ', $inter);

        return $this->request('INSERT INTO ' . $this->table . ' (' . $liste_champs .') VALUES (' . $liste_inter . ')', $valeurs);
    }

// ********** UPDATE **********

    public function update()
    {
        $champs = [];
        $valeurs = [];

        foreach($this as $champ => $valeur)
        {
            //UPDATE annonces SET titre = ?, description = ?, actif = ? WHERE id = ?
            if($valeur !== NULL && $champ != 'db' && $champ != 'table') {
                $champs[] = "$champ = ?";
                $valeurs[] = $valeur;
            }
        }
        $valeurs[] = $this->id;
        $liste_champs = implode(', ', $champs);

        return $this->request('UPDATE ' . $this->table . ' SET ' . $liste_champs .' WHERE id = ?', $valeurs);
    }

// ********** DELETE **********

    public function delete(int $id)
    {
        return $this->request("DELETE FROM $this->table WHERE id = ?", [$id]);
    }

    public function request(string $sql, array $attributs = null)
    {
        $this->db = Db::getInstance();

        if($attributs !== NULL) {
            //Requète préparée
            $query = $this->db->prepare($sql);
            $query->execute($attributs);
            return $query; 
        } else {
            //Requète simple
            return $this->db->query($sql);
        }
    }

    public function hydrate($donnees)
    {
        foreach($donnees as $key => $value) {
            $setter = 'set'.ucfirst($key);

            if(method_exists($this, $setter)){
                $this->$setter($value);
            }
        }
        return $this;
    }
}