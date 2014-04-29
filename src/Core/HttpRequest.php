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

use Shrew\Mazzy\Input\Input;


/**
 * Gestion securisée des informations de requête 
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class HttpRequest
{

    private static $instance;
    
    private $server;
    private $env;
    private $method;
    private $canonical;
    private $urlAbsolute;
    private $path;
    private $hostname;
    private $port;
    private $baseUrl;
    private $www;
    private $scheme;
    private $xhr;
    private $isSecure;
    private $modRewrite;
    private $guestIP;
    private $agent;

    /**
     * Chargeur singleton
     * 
     * @return \Shrew\Mazzy\Core\Request;
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    final public function __clone()
    {
        trigger_error("Le clonage n'est pas autorisé.", E_USER_ERROR);
    }

    private function __construct()
    {
        $this->server = Input::getServers();
    }
    
    /**
     * Définit le chemin absolu vers le contenu publique du projet
     * @param string $directory
     */
    public function setPublicDirectory($directory)
    {
        $this->www = $directory;
    }

    /**
     * Récupère l'environnement de travail de la requête 
     *
     * @return string (`developpment` ou `production`)
     */
    final public function getEnv()
    {
        if ($this->env === null) {
            $ip = $this->server->get("REMOTE_ADDR");
            $this->env = ($ip === "127.0.0.1" || $ip === "::1") ? "developpment" : "production";
        }
        return $this->env;
    }

    /**
     * @return boolean
     */
    final public function isProduction()
    {
        return ($this->getEnv() === "production");
    }

    /**
     * @return boolean
     */
    final public function isDeveloppment()
    {
        return ($this->getEnv() === "developpment");
    }

    /**
     * Récupération de la méthode de requête http 
     *
     * @return string
     */
    final public function getMethod()
    {
        if ($this->method === null) {
            $method = $this->server->get("REQUEST_METHOD");
            $this->method = strtoupper($method);
        }
        return $this->method;
    }

    /**
     * Retourne l'url absolue de la page actuelle sans les queries string
     *
     * @return string
     */
    final public function getCanonicalUrl()
    {
        if ($this->canonical === null) {
            $this->canonical = $this->getAbsoluteUrl() . $this->getPath();
        }
        return $this->canonical;
    }

    /**
     * Url absolue vers la racine du site.
     *
     * @return string
     */
    final public function getAbsoluteUrl()
    {
        if ($this->urlAbsolute === null) {
            $this->urlAbsolute = $this->getScheme() . "://" . $this->getHostname();

            $port = $this->getPort();
            if ($port !== 80 && $port !== 443) {
                $this->urlAbsolute .= ":$port";
            }
            $this->urlAbsolute .= $this->getRootUrl();

            if ($this->isRewrited() !== true) {
                $this->urlAbsolute .= "index.php";
            }
        }
        return $this->urlAbsolute;
    }

    /**
     * Retourne l'url de la page chargée
     *
     * @return string
     */
    final public function getPath()
    {
        if ($this->path === null) {

            if ($this->isRewrited() === true) {
                $uri = $this->server->get("REQUEST_URI", FILTER_SANITIZE_URL);
                $path = substr($uri, strpos($uri, "?"));
            } else {
                $path = $this->server->get("PATH_INFO", FILTER_SANITIZE_URL);
            }
            if ($path === null) {
                $path = "/";
            } else {
                // decriptage des urls
                $encoded = explode("/", $path);
                $decoded = array_map("rawurldecode", $encoded);
                $path = implode("/", $decoded);
            }
            $this->path = $path;
        }
        return $this->path;
    }

    /**
     * Récupère l'url de base du site
     *
     * @return string
     */
    final public function getRootUrl()
    {
        if ($this->baseUrl === null) {
            $needle = $this->server->get("DOCUMENT_ROOT");
            $baseUrl = substr($this->www, strlen($needle));
            $this->baseUrl = ($baseUrl === false) ? "/" : $baseUrl;
        }
        return $this->baseUrl;
    }

    /**
     * Détecte le port de connexion utilisé par la requête
     *
     * @return integer
     */
    final public function getPort()
    {
        if ($this->port === null) {
            $port = $this->server->get("SERVER_PORT", FILTER_SANITIZE_NUMBER_INT);
            if (empty($port)) {
                $port = 80;
            }
            $this->port = (int) $port;
        }
        return $this->port;
    }

    /**
     * Nom de domaine demande
     *
     * @return string
     */
    final public function getHostname()
    {
        if ($this->hostname === null) {
            $this->hostname = explode(":", $this->server->get("HTTP_HOST", FILTER_SANITIZE_URL))[0];
        }
        return $this->hostname;
    }

    /**
     * Récupère le schéma de la requête
     *
     * @return string
     */
    final public function getScheme()
    {
        if ($this->scheme === null) {
            $this->scheme = ($this->isSecure === true) ? "https" : "http";
        }
        return $this->scheme;
    }

    /**
     * La requete est-elle de type ajax ?
     *
     * @return boolean
     */
    final public function isXhr()
    {
        if ($this->xhr === null) {
            $xhr = strtolower($this->server->get("HTTP_X_REQUESTED_WITH"));
            $this->xhr = ($xhr === "xmlhttprequest");
        }
        return $this->xhr;
    }

    /**
     * Vérifie si la requête est sécurisée ou non
     *
     * [voir ici](https://tinyurl.com/3zakgtt)
     * 
     * @return boolean
     */
    final public function isSecure()
    {
        if ($this->isSecure === null) {
            $this->isSecure = ($this->server->get("HTTPS", FILTER_VALIDATE_BOOLEAN) === true || $this->server->get("SERVER_PORT") == 443) ? true : false;
        }
        return $this->isSecure;
    }

    /**
     * L'url rewriting est-il actif sur le site ?
     *
     * [voir ici](https://tinyurl.com/7ktdcuv)
     *
     * @return boolean
     */
    final public function isRewrited()
    {
        if ($this->modRewrite === null) {
             $this->modRewrite = filter_var(getenv("HTTP_MOD_REWRITE"), FILTER_VALIDATE_BOOLEAN);
        }
        return $this->modRewrite;
    }

    /**
     * Récupération de la locale privilégiée par le visiteur
     *
     * @return string (sous forme "fr_FR")
     */
    final public function getGuestLocale()
    {
        if ($this->locale === null) {
            $httpAccept = $this->server->get("HTTP_ACCEPT_LANGUAGE");
            $this->locale = Locale::acceptFromHttp($httpAccept);
        }
        return $this->locale;
    }

    /**
     * Récupération de la signature du navigateur utilisé par le client
     *
     * @return string
     */
    final public function getGuestAgent()
    {
        if ($this->agent === null) {
            $this->agent = $this->server->get("HTTP_USER_AGENT");
        }
        return $this->agent;
    }

    /**
     * Récupère l'adresse ip du client si elle est disponible
     *
     * [voir ici](https://tinyurl.com/q284x4g)
     * 
     * @return string
     */
    final public function getGuestIP()
    {
        if ($this->guestIP === null) {
            $this->guestIP = "0.0.0.0";
            foreach (array(
                "HTTP_CLIENT_IP",
                "HTTP_X_FORWARDED_FOR",
                "HTTP_X_FORWARDED",
                "HTTP_X_CLUSTER_CLIENT_IP",
                "HTTP_FORWARDED_FOR",
                "HTTP_FORWARDED",
                "REMOTE_ADDR") as $key) {
                        if (array_key_exists($key, $_SERVER) === true) {
                            foreach (explode(',', $_SERVER[$key]) as $ip) {
                                $ip = trim($ip); // just to be safe

                                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 |
                                                FILTER_FLAG_IPV6 |
                                                FILTER_FLAG_NO_PRIV_RANGE |
                                                FILTER_FLAG_NO_RES_RANGE) !== false) {
                                    $this->guestIP = $ip;
                                    return $ip;
                                }
                            }
                        }
            }
        }
        return $this->guestIP;
    }

}
