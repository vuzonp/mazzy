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
 * Gestion securisée des informations de requête 
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-13
 * @todo Standardiser les noms de méthodes *guest* vs *user*
 */
class Request
{
    
    use Mixin\Singleton;


    private $env;
    private $method;
    private $canonical;
    private $urlAbsolute;
    private $path;
    private $hostname;
    private $port;
    private $baseUrl;
    private $scheme;
    private $xhr;
    private $isSecure;
    private $modRewrite;
    private $langs;
    private $userIP;
    private $agent;

    /**
     * Récupère l'environnement de travail de la requête 
     *
     * @return string (`developpment` ou `production`)
     */
    public function getEnv()
    {
        if ($this->env === null) {
            $this->env = ($this->get("REMOTE_ADDR") === "127.0.0.1") ? "developpment" : "production";
        }
        return $this->env;
    }

    /**
     * Récupération de la méthode de requête http 
     *
     * @return string
     */
    public function getMethod()
    {
        if ($this->method === null) {
            $method = $this->get("REQUEST_METHOD");
            $this->method = strtoupper($method);
        }
        return $this->method;
    }

    /**
     * Retourne l'url absolue de la page actuelle sans les queries string
     *
     * @return string
     */
    public function getCanonicalUrl()
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
    public function getAbsoluteUrl()
    {
        if ($this->urlAbsolute === null) {
            $this->urlAbsolute = $this->getScheme() . "://" . $this->getHostname();

            $port = $this->getPort();
            if ($port !== 80 && $port !== 443) {
                $this->urlAbsolute .= ":$port";
            }
            $this->urlAbsolute .= $this->getRootUrl();

            if ($this->isRewrited() !== true) {
                $this->urlAbsolute .= "/index.php";
            }
        }
        return $this->urlAbsolute;
    }

    /**
     * Retourne l'url de la page chargée
     *
     * @return string
     */
    public function getPath()
    {
        if ($this->path === null) {
            $path = $this->get("PATH_INFO", FILTER_SANITIZE_URL);
            if ($path === null) {
                $path = "/";
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
    public function getRootUrl()
    {
        if ($this->baseUrl === null) {
            $needle = $this->get("DOCUMENT_ROOT");
            $baseUrl = substr(APP_WWW, strlen($needle));
            $this->baseUrl = ($baseUrl === false) ? "" : $baseUrl;
        }
        return $this->baseUrl;
    }

    /**
     * Détecte le port de connexion utilisé par la requête
     *
     * @return integer
     */
    public function getPort()
    {
        if ($this->port === null) {
            $port = $this->get("SERVER_PORT", FILTER_SANITIZE_NUMBER_INT);
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
    public function getHostname()
    {
        if ($this->hostname === null) {
            $this->hostname = explode(":", $this->get("HTTP_HOST", FILTER_SANITIZE_URL))[0];
        }
        return $this->hostname;
    }

    /**
     * Récupère le schéma de la requête
     *
     * @return string
     */
    public function getScheme()
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
    public function isXhr()
    {
        if ($this->xhr === null) {
            $xhr = strtolower($this->get("HTTP_X_REQUESTED_WITH"));
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
    public function isSecure()
    {
        if ($this->isSecure === null) {
            $this->isSecure = ($this->get("HTTPS", FILTER_VALIDATE_BOOLEAN) === true || $this->get("SERVER_PORT") == 443) ? true : false;
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
    public function isRewrited()
    {
        if ($this->modRewrite === null) {
            $uri = $this->get("REQUEST_URI");
            if ($uri !== null) {
                return (strpos("index.php", $uri) !== false) ? true : false;
            } elseif (function_exists("apache_get_modules")) {
                $modules = apache_get_modules();
                $this->modRewrite = in_array("mod_rewrite", $modules);
            } else {
                $this->modRewrite = filter_var(getenv("HTTP_MOD_REWRITE"), FILTER_VALIDATE_BOOLEAN);
            }
        }
        return $this->modRewrite;
    }

    /**
     * Récupération de la locale privilégiée par le visiteur
     *
     * @return string (sous forme "fr_FR")
     */
    public function getUserLocale()
    {
        if ($this->locale === null) {
            $httpAccept = $this->get("HTTP_ACCEPT_LANGUAGE");
            $this->locale = Locale::acceptFromHttp($httpAccept);
        }
        return $this->locale;
    }

    /*
    public function getGuestLangs()
    {
        if ($this->langs === null) {
            $langs = array("es", "en");
            $locales = explode(",", $this->get("HTTP_ACCEPT_LANGUAGE"));
            foreach ($locales as $locale) {
                $langs[] = strtolower(substr($locale, 0, 2));
            }
            $this->langs = array_unique($langs);
        }
        return $this->langs;
    }
    */

    /**
     * Récupération de la signature du navigateur utilisé par le client
     *
     * @return string
     */
    public function getUserAgent()
    {
        if ($this->agent === null) {
            $this->agent = $this->get("HTTP_USER_AGENT");
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
    public function getUserIP()
    {
        if ($this->userIP === null) {
            $this->userIP = "0.0.0.0";
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
                            $this->userIP = $ip;
                            return $ip;
                        }
                    }
                }
            }
        }
        return $this->userIP;
    }

    /**
     * Récupère de manière sécurisée une variable de requête
     *
     * Par défaut les valeurs sont nettoyées de tout code PHP et HTML
     * 
     * @param string $identifier Label correspondant dans $_SERVER
     * @param integer $filter filtre de nettoyage de type filter_var
     * @return string
     */
    public function get($identifier, $filter = FILTER_DEFAULT, $options = null)
    {
        if ($filter === FILTER_UNSAFE_RAW && $options === null) {
            $options = FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH;
        }
        return filter_input(INPUT_SERVER, $identifier, $filter, $options);
    }

}
