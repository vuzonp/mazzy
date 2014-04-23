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
 * Gestionnaire de connexions aux bases de données
 * 
 * _Les connexions ne sont réalisées que lors du premier appel à 
 * une base de données et non lors de l'ajout d'une nouvelle configuration._
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class DB
{

    /**
     * @var array 
     */
    private static $config;

    /**
     * @var array 
     */
    private static $dbhs;

    /**
     * Permet d'attacher plusieurs bases en une seule fois
     * 
     * @param array $configs Tableau multidimensionnel composé des configurations
     */
    final public static function attachMany(Array $configs)
    {
        $labels = array_keys($configs);
        $configs = array_values($configs);
        $length = sizeof($configs);

        for ($i = 0; $i < $length; $i++) {
            self::attach($labels[$i], $configs[$i]);
        }
    }

    /**
     * Permet d'ajouter une base de données
     * 
     * @param string $label Identifiant de la connexion
     * @param array $config
     * @throws \Shrew\Mazzy\Lib\DataBase\DatabaseException
     */
    final public static function attach($label, Array $config)
    {
        if (!isset($config['dsn'])) {
            throw new DatabaseException("La base de donnée `$label` ne contient"
            . " aucune information de connexion [dsn]", 500);
        }
        self::$config[$label] = $config;
    }

    /**
     * Permet de retirer une base de données de la liste des disponibilités
     * 
     * @param string $label Identifiant de la connexion
     */
    final public static function detach($label)
    {
        self::$config[$label] = null;
        unset(self::$config[$label]);

        if (isset(self::$dbhs[$label])) {
            self::$dbhs[$label] = null;
            unset(self::$dbhs[$label]);
        }
    }

    /**
     * Effectue la connexion avec la base de données
     * 
     * @param array $config Configuration d'accès à la base
     * @return \Shrew\Mazzy\Lib\DataBase\DataBaseHandler
     * @throws \Shrew\Mazzy\Lib\DataBase\DatabaseException Erreur de connexion à la base
     */
    private static function loadDataBaseHandler(Array $config)
    {
        try {
            return new DataBaseHandler(
                    $config["dsn"], $config["user"], $config["password"], $config["options"]
            );
        } catch (\PDOException $e) {
            throw new DatabaseException("Connexion à la base impossible", 503, $e);
        }
    }

    /**
     * Récupère une connexion à une base de donnée
     * 
     * @param string $label Identifiant de la connexion
     * @return \Shrew\Mazzy\Lib\DataBase\DataBaseHandler Instance de connexion à la base
     * @throws \Shrew\Mazzy\Lib\DataBase\DatabaseException Lorsqu'aucune connexion ne correspond à `$label`
     */
    final public static function get($label)
    {
        if (!isset(self::$dbhs[$label])) {
            if (!isset(self::$config[$label])) {
                throw new DatabaseException("La base de données `$label` n'est pas rattachée à l'application", 500);
            }
            self::$dbhs[$label] = self::loadDataBaseHandler(self::$config[$label]);
        }
        return self::$dbhs[$label];
    }

    /**
     * Retourne le nombre de requêtes effectuées par une connexion
     * 
     * Cette méthode est surtout destinée au débugage de l'application (benchmark)
     * 
     * @param string $label Identifiant de la connexion
     * @return integer
     */
    final public static function countRequests($label)
    {
        return (isset(self::$dbhs[$label])) ? count(self::$dbhs[$label]) : 0;
    }

}
