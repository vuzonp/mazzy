<?php

/**
 * The MIT License (MIT)
 *
 * Copyright (c) 2014 Thomas Girard
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in 
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Shrew\Mazzy\Lib\DataBase;

use Shrew\Mazzy\Lib\Core\Collection;


/**
 * _DataMapper_ de base destiné à servir de parent aux modèles (Mvc)
 * 
 * Permet des actions de type CRUD sur les donnees des bases relationnelles
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
abstract class Mapper implements MapperInterface
{

    /**
     * Instance de connexion a la base de donnees
     * @var \Shrew\Mazzy\Lib\DataBase\DataBaseHandler
     */
    protected $dbh;
    
    /**
     * Table SQL de destination
     * @var string
     */
    protected $table;
    
    /**
     * Cle primaire de la table SQL
     * @var string
     */
    protected $primary = "id";

    final public function __construct()
    {
        $this->primary = "id";
        $this->initialize();

        if ($this->table === null) {
            $class = get_called_class();
            $this->table = lcfirst(substr($class, strrpos($class, "\\") + 1)) . "s";
        }
    }

    /**
     * Initialisation de la classe modèle
     */
    protected function initialize()
    {
        
    }
    
    /**
     * Formatte un tableau en chaine SQL `ORDER BY xxx ASC`
     * 
     * @param array $orderBy Directives de tri des donnees
     * @return string
     */
    final protected function orderByFormat(Array $orderBy)
    {
        $sort = "";
        if (!empty($orderBy)) {
            $sort = " ORDER BY";
            foreach ($orderBy as $by) {
                $sort .= implode(" $by[0] $by[1],");
            }
            $sort = rtrim($sort, ",");
        }
        return $sort;
    }

    /**
     * Insertion d'une nouvelle ligne dans la table
     * 
     * @param \Shrew\Mazzy\Lib\Core\Collection $collection Donnees a inserer
     * @return boolean
     */
    public function insert(Collection $collection)
    {
        $c = $collection->toArray();
        $cols = implode(", ", array_keys($c));
        $params = implode(", ", array_fill(0, sizeof($c), "?"));
        $values = array_values($c);

        $statement = "INSERT INTO $this->table ($cols) VALUES ($params)";
        $sth = $this->dbh->prepare($statement);
        return $sth->execute($values);
    }

    /**
     * Recupere des lignes dans la table par recherche egalitaire et les ordonne
     * 
     * @param string $prop Propriete sur laquelle cibler la recherche
     * @param string $value Valeur a rechercher
     * @param integer $type Filtre PDO des requetes preparees
     * @param integer $limit Nombre maximal de resultats a retouner
     * @param array $orderBy Directives de tri des donnees
     * @return \Shrew\Mazzy\Lib\Core\Collection
     */
    public function findOrder($prop, $value, $type = \PDO::PARAM_STR, $limit = 0, Array $orderBy = array())
    {
        $sort = $this->orderByFormat($orderBy);
        $statement = "SELECT * FROM $this->table WHERE $prop=:value LIMIT :limit $sort";
        $sth = $this->dbh->prepare($statement);
        $sth->bindParam(":value", $value, $type);
        $sth->bindParam(":limit", $limit, \PDO::PARAM_INT);
        $sth->execute();
        return $sth->fetchCollection();
    }

    /**
     * Recupere des lignes dans la table par recherche egalitaire
     * 
     * @param string $prop Propriete sur laquelle cibler la recherche
     * @param string $value Valeur a rechercher
     * @param integer $type Filtre PDO des requetes preparees
     * @param integer $limit Nombre maximal de resultats a retouner
     * @return \Shrew\Mazzy\Lib\Core\Collection
     */
    public function findMany($prop, $value, $type = null, $limit = null)
    {
        return $this->findOrder($prop, $value, $type, $limit);
    }

    /**
     * Recupere une ligne dans la table par recherche egalitaire
     * 
     * @param string $prop Propriete sur laquelle cibler la recherche
     * @param string $value Valeur a rechercher
     * @param integer $type Filtre PDO des requetes preparees
     * @return \Shrew\Mazzy\Lib\Core\Collection
     * @todo Passer directement par findOrder()
     */
    public function findOne($prop, $value, $type = null)
    {
        return $this->findMany($prop, $value, $type, 1);
    }

    /**
     * Recupere une ligne dans la table par recherche sur cle primaire
     * 
     * @param integer $id valeur de la cle primaire
     * @return \Shrew\Mazzy\Lib\Core\Collection
     * @todo Passer directement par findOrder()
     */
    public function findByPrimary($id)
    {
        return $this->findOne($this->primary, $id, \PDO::PARAM_INT);
    }

    /**
     * Met a jour une ligne de la table
     * 
     * @param \Shrew\Mazzy\Lib\Core\Collection $collection
     * @return boolean
     */
    public function update(Collection $collection)
    {
        $c = $collection->toArray();
        $set = implode("=?, ", array_keys($c)) . "=?";
        $values = array_values($c);

        $statement = "UPDATE $this->table SET $set WHERE $this->primary=:id";
        return $this->dbh->prepare($statement)->execute($values);
    }

    /**
     * Supprime une ligne dans la table par recherche sur cle primaire
     * 
     * @param integer $id valeur de la cle primaire
     * @return boolean
     */
    public function delete($id)
    {
        $statement = "DELETE FROM $this->table WHERE {$this->primary}=:id";
        $sth = $this->dbh->prepare($statement);
        $sth->bindParam(":value", $id, \PDO::PARAM_INT);
        return $sth->execute();
    }

}
