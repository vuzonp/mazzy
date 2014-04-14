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

/**
 * *Database Abstraction Layer* étendant PDO
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-14
 */
class DataBaseHandler extends \PDO implements \Countable
{

    /**
     * @var integer 
     */
    private $counter;

    public function __construct($dsn, $username = null, $passwd = null, Array $options = null)
    {
        parent::__construct($dsn, $username, $passwd, $options);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->setAttribute(self::ATTR_STATEMENT_CLASS, array(__NAMESPACE__ . "\Statement"));
        $this->counter = 0;
    }

    /**
     * @return integer
     */
    final public function count()
    {
        return $this->counter;
    }

    final public function increment()
    {
        $this->counter++;
    }

    /**
     * Exécute une requête SQL, retourne un jeu de résultats en tant qu'objet 
     * 
     * @param string $statement Requête SQL à préparer et à exécuter
     */
    public function query($statement)
    {
        $this->increment();
        parent::query($statement);
    }

    /**
     * Exécute une requête SQL et retourne le nombre de lignes affectées 
     * 
     * @param string $statement Requête SQL à préparer et à exécuter
     */
    public function exec($statement)
    {
        $this->increment();
        parent::exec($statement);
    }

    /**
     * Prépare une requête à l'exécution et retourne un objet
     * 
     * @param string $statement Requête SQL à préparer et à exécuter
     * @param array $options Tableau contient une ou plusieurs paires clé=>valeur
     * @return \Shrew\Mazzy\Lib\DataBase\Statement
     */
    public function prepare($statement, $options = array())
    {
        $sth = parent::prepare($statement, $options);
        $sth->assignDataBaseHandler($this);
        return $sth;
    }

}
