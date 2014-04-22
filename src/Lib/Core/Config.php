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

namespace Shrew\Mazzy\Lib\Core;

/**
 * Gestion des options de configuration de l'application
 *
 * @todo    Envisager d'isoler cette librairie indépendante de *Core* afin 
 *          d'éviter des dépendances trop fortes envers ce paquet.
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Config
{
    
    /**
     * Tous les environnements
     */
    const ENV_ALL = 0b011;

    /**
     * Environnement de développement
     */
    const ENV_DEVELOPPMENT = 0b001;

    /**
     * Environnement de production
     */
    const ENV_PRODUCTION = 0b010;
    
    /**
     * Environnement de travail
     * @var integer 
     */
    private static $env;
    
    /**
     * Options de configuration des librairies
     * @var array
     */
    protected static $config;
    
    /**
     * Initialisation
     * 
     * @param string $env Environnement tel que retourné par Request
     */
    final public static function setEnvironnement($env)
    {
        self::$env = ($env === "developpment") 
                ? self::ENV_DEVELOPPMENT 
                : self::ENV_PRODUCTION;
    }

    /**
     * Configuration de l'application
     * 
     * @param string $lib Nom de la librairie concernee
     * @param array $config Options de configuration
     * @param integer $env Environnement(s) concerne(s)
     */
    final public static function set($lib, Array $config = array(), $env = self::ENV_ALL)
    {
        if (($env & self::ENV_DEVELOPPMENT) === self::ENV_DEVELOPPMENT) {
            if (isset(self::$config[self::ENV_DEVELOPPMENT][$lib])) {
                self::$config[self::ENV_DEVELOPPMENT][$lib] = array_merge(self::$config[self::ENV_DEVELOPPMENT][$lib], $config);
            } else {
                self::$config[self::ENV_DEVELOPPMENT][$lib] = $config;
            } 
        }
        if (($env & self::ENV_PRODUCTION) === self::ENV_PRODUCTION) {
            if (isset(self::$config[self::ENV_PRODUCTION][$lib])) {
                self::$config[self::ENV_PRODUCTION][$lib] = array_merge(self::$config[self::ENV_PRODUCTION][$lib], $config);
            } else {
                self::$config[self::ENV_PRODUCTION][$lib] = $config;
            } 
        }
    }
    
    final public static function exists($lib)
    {
        if (self::$env === null) {
            throw new AppException("Environnement d'exécution inconnu.", 500);
        }
        return array_key_exists($lib, self::$config[self::$env]);
    }

    /**
     * Récupération des options de configuration
     * 
     * @param string $lib Nom de la librairie dont on souhaite récupérer les options
     * @throws AppException Lorsqu'aucune option de configuration n'est trouvée
     */
    final public static function get($lib)
    {
        if (self::$env === null) {
            throw new AppException("Environnement d'exécution inconnu.", 500);
        }
        if (self::$config[self::$env][$lib] === null) {
            throw new AppException("Aucune configuration n'est disponible pour `$lib`", 500);
        }
        
        return self::$config[self::$env][$lib];
    }

    /**
     * Récupère l'environnement d'exécution de l'application
     * @return integer
     */
    final public static function getEnv()
    {
        return self::$env;
    }

    /**
     * Vérifie si l'environnement attendu correspond a celui du système
     * @param integer $env
     * @return boolean
     */
    final public static function isEnv($env)
    {
        return ($env === self::$env);
    }

    /**
     * Vérifie si l'application tourne en environnement de développement
     * @return boolean
     */
    final public static function isDeveloppment()
    {
        return self::isEnv(self::ENV_DEVELOPPMENT);
    }

    /**
     * Vérifie si l'application tourne en environnement de production
     * @return boolean
     */
    final public static function isProduction()
    {
        return self::isEnv(self::ENV_PRODUCTION);
    }
}
