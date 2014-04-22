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

use Shrew\Mazzy\Lib\Core\Config;
use Shrew\Mazzy\Lib\Report\Log;


/**
 * Fichier de configuration de l"application
 *
 * La configuration est gérée par la classe App et permet de spécifier
 * l'environnement ciblé (prodution / développement / les deux).
 *
 * L'environnement d'éxécution est détecté par l'application elle-même à chaque
 * requête.
 */


//------------------------------------------------------------------------------
// Bases de données
//------------------------------------------------------------------------------

Config::set("database", array(
    
    // Base de donnée principale
    "main" => array(
        "dsn" => "mysql:host=localhost;port=3600;dbname=alagos",
        "user" => "JohnDoe",
        "password" => "XXXXXXX",
        "options" => array(PDO::ATTR_PERSISTENT => false)
    )
    
    // Base de données complémentaires...
/* "secondary" => array(
        "dsn" => "mysql:host=localhost;port=3600;dbname=alagos",
        "user" => "JohnDoe",
        "password" => "XXXXXXX",
        "options" => array(PDO::ATTR_PERSISTENT => false)
    )
/**/
));


//------------------------------------------------------------------------------
// Sessions
//------------------------------------------------------------------------------

Config::set("session", array(
    "name" => "mazzy_sess",
    "life" => 86400, // 86400 = 24h00
    "domain" => null,
    "path" => "/",
));


//------------------------------------------------------------------------------
// Log
//------------------------------------------------------------------------------

Config::set("log", array(
    "directory" => "/tmp",
    "minLevel" => Log::DEBUG,
));

// Environnement de production :
Config::set("log", array(
    "directory" => APP_ROOT . "/var/log",
    "minLevel" => Log::WARNING,
), Config::ENV_PRODUCTION);


//------------------------------------------------------------------------------
// Cache
//------------------------------------------------------------------------------

Config::set("cache", array(
    "directory" => "/tmp",
));

// Environnement de production :
Config::set("cache", array(
    "directory" => APP_ROOT . "/var/tmp",
), Config::ENV_PRODUCTION);


//------------------------------------------------------------------------------
// Localisation
//
// (Les locales doivent être installées au niveau du serveur pour pouvoir les
// utiliser correctement)
//------------------------------------------------------------------------------

Config::set("locale", array(
    "default" => "fr_FR", // Locale par défaut du site web
    "translations" => array("en_US") // Locales secondaires
));


//------------------------------------------------------------------------------
// Rendu (mVc)
//------------------------------------------------------------------------------

Config::set("view", array(
    
    // Thème par défaut de l'application
    "defaultTheme" => "default",
    
    // Chemin à utilisé pour les contenus statiques du site
    "assets" => "/assets/"
    // "assets" => "http://cdn.monsite.com/"
));

