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

use Shrew\Mazzy\Lib\Cache\CacheFile;
use Shrew\Mazzy\Lib\Report\Log;
use Shrew\Mazzy\Lib\Route\RouterInterface;


/**
 * Porte d'entrée de l'application.
 * 
 * Interface permettant l'initialisation, la configuration et 
 * l'exécution du programme.
 *
 * @todo    Corriger le système d'affichage des erreurs en s'inspirant de la
 *          manière de faire de la librairie Handler mais en évitant la
 *          dépendance à Template... Bref, toute une histoire !
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-21
 */
class App
{

    /**
     * Données de requêtes
     * @var Request 
     */
    protected $req;

    /**
     * Instance du router
     * @var \Shrew\Mazzy\Lib\Router\RouterInterface
     */
    protected $router;
    
    /**
     * Initialisation du système et des principales librairies
     */
    public function __construct()
    {
        try {
            // Récupération de la requête
            $this->req = Request::getInstance();

            // Initialisation de l'environnement d'exécution
            Config::setEnvironnement($this->req->getEnv());

            // Initialisation du gestionnaire de logs
            $config = Config::get("log");
            Log::setPath($config["directory"]);
            Log::setLeveLReports($config["minLevel"]);
            
            // Initialisation du cache
            $config = Config::get("cache");
            CacheFile::setPath($config["directory"]);

            // Localisation et encodage
            $this->initCharset();
            $locale = $this->detectLocalisation();
            $this->initLocalisation($locale);

        } catch (AppException $e) {
            Log::emergency($e->getMessage(), $e->getFile(), $e->getLine());
            $this->sendError($e);
        }
    }

    /**
     * Active la gestion des langues
     * @return string La locale détectée
     */
    final protected function detectLocalisation()
    {
        // Chargement de la configuration
        $config = Config::get("locale");

        // Locale par défaut
        $locale = $config["default"];

        // détection de la locale par sous-domaine
        $domains = explode(".", $this->req->getHostname());
        $length = count($domains);

        // Détection de la langue
        if ($length > 2) {
            $n = --$length;
            for ($i = 0; $i < $n; $i++) {
                $allowed = $config["translations"][$i];
                $lang = Locale::getPrimaryLanguage($allowed);
                if (in_array($lang, $domains)) {
                    $locale = $allowed;
                    break;
                }
            }
        }

        return $locale;
    }

    /**
     * Initialise et configure gettext
     *
     * @param string $locale Locale de langue à utiliser
     */
    final protected function initLocalisation($locale)
    {
        // Initialisation de php
        ini_set('intl.default_locale', $locale);
        \Locale::setDefault($locale);

        // Initialisation de gettext
        putenv("LANG=$locale");
        setlocale(LC_ALL, $locale);

        $domain = "alagos";
        bindtextdomain($domain, APP_ROOT . "/locales");
        bind_textdomain_codeset($domain, "UTF-8");

        textdomain($domain);
    }

    /**
     * Configure l'encodage par défaut
     *
     * @param string $charset
     */
    final protected function initCharset()
    {
        mb_internal_encoding("UTF-8");
        mb_language("uni");

        iconv_set_encoding("internal_encoding", "UTF-8");
        iconv_set_encoding("input_encoding", "UTF-8");
        iconv_set_encoding("output_encoding", "UTF-8");
    }

    /**
     * Attributions des routes à l'application
     * 
     * @param \Shrew\Mazzy\Lib\Route\RouterInterface $router
     * @return \Shrew\Mazzy\Lib\Core\App
     */
    final public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
        return $this;
    }

    /**
     * Exécute la requête
     */
    final public function run()
    {
        try {
            $this->router->find($this->req->getPath());
        } catch (\Exception $e) {
            $this->sendError($e);
        }
    }

    /**
     * Gestion des erreurs http
     *
     * @param \Exception $e Exception ayant nécessité l'envoi d'une réponse d'erreur
     * @param integer $code Code http a utiliser. Si nulle, alors tentera d'utiliser le code de l'exception
     */
    final protected function sendError(\Exception $e)
    {
        if ($e->getCode() < 500) {
            Log::notice($e->getMessage());
        } else {
            Log::error($e->getMessage());
        }
        $handler = new ErrorHandler();
        $handler->sendException($e);
    }

    /**
     * Post-traitements de l'application
     */
    public function __destruct()
    {
        Log::save(); // Sauvegarde des logs
    }

}
