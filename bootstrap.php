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

use Shrew\Mazzy\Lib\Core\App;
use Shrew\Mazzy\Lib\Core\Config;
use Shrew\Mazzy\Lib\DataBase\DB;
use Shrew\Mazzy\Lib\Report\Log;

if (defined("APP_WWW") === false) {
    exit();
}

/**
 *  Répertoire racine du projet
 */
define("APP_ROOT", __DIR__);
define("APP_CONFIG", APP_ROOT . "/config");

try {

//------------------------------------------------------------------------------
    
    // Autoloader PSR-4
    require APP_ROOT . "/vendor/autoload.php";

    // Configuration de l'application
    require APP_CONFIG . "/config.php";

    // Initialisation de l'application
    $app = new App();

    // Base(s) de données
    DB::attachMany(Config::get("database"));

    // Vers l'infini et au-delà !!
    $app->setRoute(include APP_CONFIG . "routes.php");
    $app->run();

//------------------------------------------------------------------------------
    
// Récupération des exceptions qui se seraient échappées :
} catch (Exception $ex) {
    
    Log::alert($e->getMessage(), $e->getFile(), $e->getLine());
    
    if (Config::isDeveloppment()) {
        trigger_error($e->getMessage(), E_USER_ERROR);
    } else {
        echo "« Houston, we've had a problem »";
    }
}
