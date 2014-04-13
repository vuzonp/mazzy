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

use Shrew\Mazzy\Lib\Report\Log;
use \Shrew\Mazzy\Lib\RouterException;
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
 * @since   2014-04-13
 */
class App
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
     * Données de requêtes
     * @var Request 
     */
    protected $req;

    /**
     * Instance du router
     * @var Router
     */
    protected $route;

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

            // Localisation et encodage
            $this->initCharset();
            $locale = $this->detectLocalisation();
            $this->initLocalisation($locale);

            // Vieux code qui sert de pense-bête pour ajouter ces options
            // à la librairie de template...

            /*
            Template::setLocale($locale);
            TemplateTool::setAbsoluteUrl($this->req->getAbsoluteUrl());
            TemplateTool::setCanonicalUrl($this->req->getCanonicalUrl());

            if (isset($config["template"]["assets"])) {
                TemplateTool::setAssetsUrl($config["template"]["assets"]);
            } else {
                TemplateTool::setAssetsUrl("/assets");
            }
            */
            
        } catch (AppException $e) {
            Log::alert($e->getMessage(), $e->getFile(), $e->getLine());
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
        bind_textdomain_codeset($domain, APP_CHARSET);

        textdomain($domain);
    }

    /**
     * Configure l'encodage par défaut
     *
     * @param string $charset
     */
    final protected function initCharset()
    {
        mb_internal_encoding(APP_CHARSET);
        mb_language("uni");

        iconv_set_encoding("internal_encoding", APP_CHARSET);
        iconv_set_encoding("input_encoding", APP_CHARSET);
        iconv_set_encoding("output_encoding", APP_CHARSET);
    }

    /**
     * Exécute la requête en lançant le contrôleur approprié
     */
    final public function run()
    {
        try {
            return $this->route->find($this->req->getPath());
        }

        // Exception exigeant d'afficher un message d'erreur http spécifique
        // (erreurs 404, accès interdits, mauvaise méthode, etc.)
        catch (RouterException $e) {
            Log::debug($e->getMessage(), $e->getFile(), $e->getLine());
            $this->sendError($e);
        }

        // Récupération des exceptions orphelines
        catch (AppException $e) {
            Log::error($e->getMessage(), $e->getFile(), $e->getLine());
            $this->sendError($e);
        } catch (\PDOException $e) {
            Log::error($e->getMessage(), $e->getFile(), $e->getLine());
            $this->sendError($e, 500);
        } catch (\LogicException $e) {
            Log::alert($e->getMessage(), $e->getFile(), $e->getLine());
            $this->sendError($e);
        } catch (\Exception $e) {
            Log::emergency($e->getMessage(), $e->getFile(), $e->getLine());
            $this->sendError($e);
        }
    }

    /**
     * Gestion des erreurs http
     *
     * @param \Exception $e Exception ayant nécessité l'envoi d'une réponse d'erreur
     * @param integer $code Code http a utiliser. Si nulle, alors tentera d'utiliser le code de l'exception
     */
    final protected function sendError(\Exception $e, $code = null)
    {
        // Préparation du code http
        $code = (is_int($code)) ? $code : $e->getCode();

        // Préparation du message à adresser au client
        if ($this->getEnv() === self::ENV_DEVELOPPMENT) {
            $msg = $e->getMessage() . " [file: " . $e->getFile() . ":" . $e->getLine() . "]";
        } else {
            $msg = _("Oups, il y a un problème !");
        }

        // todo: corriger cette dépendance absurde !
        if (class_exists("ErrorHandler")) {
            $err = new ErrorHandler();
            $err->show($msg, $code);
        } else {
            $res = Response::getInstance();
            $res->setStatus($e->getCode());
            $res->setBody($e->getMessage());
            $res->send();
        }
    }

    /**
     * Attributions des routes à l'application
     * 
     * @param \Shrew\Mazzy\Lib\Route\RouterInterface $route
     */
    final public function setRoute(RouterInterface $route)
    {
        $this->route = $route;
    }

    /**
     * Post-traitements de l'application
     */
    public function __destruct()
    {
        Log::save(); // Sauvegarde des logs
    }

}
