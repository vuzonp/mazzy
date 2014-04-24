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
use Shrew\Mazzy\Lib\Report\Bench;
use Shrew\Mazzy\Lib\Report\Log;


/**
 * Représente une requête préparée et, une fois exécutée, 
 * le jeu de résultats associé. 
 * 
 * Cette surcharge de \PDOStatement propose des méthodes pour récupérer
 * les données sous forme de collections. Un compteur de requête a de plus
 * été ajouté au système original.
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Statement extends \PDOStatement
{

    /**
     * Instance de connexion a la base de donnees
     * @var \Shrew\Mazzy\Lib\DataBase\DataBaseHandler
     */
    private $dbh;

    /**
     * Assigne une l'instance de l'objet ayant servi à appeler la classe Statement
     * 
     * @param \Shrew\Mazzy\Lib\DataBase\DataBaseHandler $dbh
     */
    final public function assignDataBaseHandler(DataBaseHandler $dbh)
    {
        $this->dbh = $dbh;
    }

    /**
     * Exécute une requête préparée 
     * @param type $input_parameters tableau de valeurs avec autant d'éléments
     *                               qu'il y a de paramètres à associer dans la 
     *                               requête SQL qui sera exécutée. Toutes les 
     *                               valeurs sont traitées comme des constantes 
     *                               PDO::PARAM_STR. 
     * @return boolean
     */
    public function execute($input_parameters = null)
    {
        $bench = new Bench();
        $result = parent::execute($input_parameters);
        $time = $bench->getTimer();
        
        Log::debug("Requête SQL : {$this->queryString} (tps: $time sec)");
        $this->dbh->increment();
        
        return $result;
    }

    /**
     * Récupère la prochaine ligne et la retourne en tant que collection
     * 
     * @return \Shrew\Framework\Lib\Collection
     */
    public function fetchCollection()
    {
        $row = parent::fetch();
        return new Collection($row);
    }

    /**
     * Retourne une collection contenant toutes les lignes 
     * du jeu d'enregistrements 
     * 
     * @return \Shrew\Mazzy\Lib\Core\Collection
     */
    public function fetchAllCollection()
    {
        $rows = parent::fetchAll();
        return new Collection($rows);
    }

}
