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

namespace Shrew\Mazzy\Core;

use Shrew\Mazzy\Config\Config;
use Shrew\Mazzy\DataBase\DB;
use Shrew\Mazzy\Handler\ErrorHandler;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LogLevel;


/**
 * Classe principale
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class App implements LoggerAwareInterface
{

    use \Psr\Log\LoggerAwareTrait;

    /**
     * @var \Shrew\Mazzy\Core\HttpRequest 
     */
    private $request;
    
    /**
     * @var \Shrew\Mazzy\Core\HttpResponse
     */
    private $response;
    
    /**
     * @var \Shrew\Mazzy\Config\Config
     */
    private $settings;
    
    /**
     * @var \Shrew\Mazzy\Core\Router 
     */
    public $route;

   /**
    * @param array $settings
    */
    public function __construct(array $settings = array())
    {
        $this->request = HttpRequest::getInstance();
        $this->settings = new Config($settings);
        $this->route = new Router($this->request->getMethod(), $this->request->getPath());
    }
    
    /**
     * Détecte la locale à utiliser
     * 
     * @return string La locale détectée
     */
    private function detectLocalisation()
    {
        // Locale par défaut
        $locale = $this->settings->get("locale.default", "fr_FR");
        $alloweds = $this->settings->get("locale.allowed", null);

        // Si le multilinguisme n'est pas activé on utilise la locale par défaut
        if ($alloweds === null || is_array($alloweds) === false) {
            return $locale;
        }

        // Scanne les sous-domaines pour détecter une langue
        $domains = explode(".", $this->request->getHostname());

        if (sizeof($domains) > 2) {
            foreach ($alloweds as $allowed) {
                $lang = \Locale::getPrimaryLanguage($allowed);
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
     * @param string $locale Locale active
     * @param string $charset Charset du site
     */
    private function initLocalisation($locale, $charset)
    {
        // Initialisation de php
        ini_set('intl.default_locale', $locale);
        \Locale::setDefault($locale);

        // Initialisation de gettext
        putenv("LANG=$locale");
        setlocale(LC_ALL, $locale);

        $domain = "mazzy";
        bindtextdomain($domain, $this->settings->get("locale.directory", __DIR__ . "/../../locales") . "/locales");
        bind_textdomain_codeset($domain, $charset);

        textdomain($domain);
    }

    /**
     * Configure l'encodage par défaut
     * 
     * @param string $charset Charset du site
     */
    private function initCharset($charset)
    {
        mb_internal_encoding($charset);
        mb_language("uni");

        iconv_set_encoding("internal_encoding", $charset);
        iconv_set_encoding("input_encoding", $charset);
        iconv_set_encoding("output_encoding", $charset);
    }

    /**
     * Ajoute une option de configuration
     * 
     * @param string $label
     * @param mixed $value
     * @param string $env
     */
    final public function set($label, $value, $env = "all")
    {
        if ($env === "all" || $env === $this->env) {
            $this->settings->set($label, $value);
        }
    }

    /**
     * Attache une base de données à l'application
     * 
     * @param string $label
     * @param array $config
     */
    final public function setDatabase($label, array $config)
    {
        DB::attach($label, $config);
    }

    /**
     * Prépare l'application à traiter les requêtes
     */
    private function setup()
    {
        error_reporting($this->settings->get("error.reporting", 0));
        
        $charset = $this->settings->get("locale.charset", "UTF-8");
        $this->initCharset($charset);
        $this->initLocalisation($this->detectLocalisation($this->settings), $charset);
        
        // Définit le répertoire public de l'application 
        $this->request->setPublicDirectory(
            $this->settings->get("view.public", realpath(__DIR__ . "/../../"))
        );
        
        $this->response = HttpResponse::getInstance();
    }
    
    /**
     * Exécute la requête
     * 
     * @throws \Exception Lorsque la ressource rencontre un problème
     */
    public function run()
    {
        if ($this->logger !== null) {
            $this->logger->info("Execution de la requete...");
        }
        
        try {
            $this->setup();

            // Aucune route n'a été trouvée
            if ($this->route->hasResults() === false) {
                throw new \Exception("La ressource demandée n'existe pas", 404);
            }

            // Traitement de la file d'attente retenue par le router
            foreach($this->route as $rsc) {
                if ($rsc->method === null && $rsc->handler instanceof \Closure) {
                    $this->callClosure($rsc);
                } else {
                    $this->callHandler($rsc);
                }
            }
            
        } catch (\Exception $e) {
            
            // Logger
            if ($this->logger !== null) {
                $level = ($e->getCode() < 500) ? LogLevel::WARNING : LogLevel::ERROR;
                $this->logger->log(
                        $level, 
                        sprintf("[Exception] %s (code : %s)", 
                        $e->getMessage(), 
                        $e->getCode())
                );
            }
            
            // Gestionnaire d'exception de l'application
            $handler = new ErrorHandler($this->request, $this->response, $this->settings);
            $handler->sendException($e);
        }
    }

    /**
     * Exécute une ressource de type callback
     * 
     * @param \stdClass $rsc
     * @return mixed
     */
    private function callClosure(\stdClass $rsc)
    {
        if ($this->logger !== null) {
            $this->logger->info("[Handler] Fonction de rappel anonyme");
        }
        
        $params = array_merge(array($this->request, $this->response, $this->settings), $rsc->params);
        return call_user_func_array($rsc->handler, $params);
    }

    /**
     * Exécute une ressource placée dans une classe contrôleur
     * 
     * @param \stdClass $rsc
     * @return mixed
     * @throws \Exception
     */
    private function callHandler(\stdClass $rsc)
    {
        $class = $rsc->namespace . $rsc->handler;
        
        if ($this->logger !== null) {
            $this->logger->info("[Handler] Classe controleur : `$class::{$rsc->method}`");
        }
        
        if (is_subclass_of($class, "Shrew\Mazzy\Handler\HandlerAbstract") === false) {
            throw new \Exception("La classe `$class` n'est pas un contrôleur valide", 500);
        }
        
        $handler = new $class($this->request, $this->response, $this->settings);
        $handler->setLogger($this->logger);

        if (!method_exists($handler, $rsc->method)) {
            throw new \Exception("La methode `$class::{$rsc->method}` n'existe pas", 500);
        }

        return call_user_func_array(array($handler, $rsc->method), $rsc->params);
    }
    
    public function __destruct()
    {
        if ($this->request->isDeveloppment()) {
            $this->logger->debug("[Model] Nombre de requetes : " . DB::countRequests("main"));
            $this->logger->debug("Options de configuration : " . print_r($this->settings->getAll(), true));
        }
    }
}
