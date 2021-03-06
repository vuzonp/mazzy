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

/**
 * Gestionnaire de réponses http (Singleton).
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class HttpResponse
{
    private static $instance;
    
    /**
     * Liste des types mimes disponibles par défaut
     * @var array
     */
    private static $listTypes = array(
        "text" => "text/plain; charset=%s",
        "csv" => "text/csv; charset=%s",
        "html" => "text/html; charset=%s",
        "xml" => "text/xml; charset=%s",
        "json" => "application/json; charset=%s",
        "gif" => "image/gif",
        "jpg" => "image/jpeg",
        "png" => "image/png"
    );

    /**
     * Status http utilisables
     * @var array
     */
    private static $listStatus = array(
        200 => "OK",
        201 => "Created",
        202 => "Accepted",
        //203 => "Non-Authoritative Information",
        204 => "No Content",
        205 => "Reset Content",
        206 => "Partial Content",

        300 => "Multiple Choices",
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        304 => "Not Modified",
        //305 => "Use Proxy",
        307 => "Temporary Redirect",

        400 => "Bad Request",
        401 => "Unauthorized",
        403 => "Forbidden",
        404 => "Not Found",
        405 => "Method Not Allowed",
        406 => "Not Acceptable",
        //407 => "Proxy Authentication Required",
        //408 => "Request Timeout",
        409 => "Conflict",
        410 => "Gone",
        411 => "Length Required",
        //412 => "Precondition Failed",
        413 => "Request Entity Too Large",
        414 => "Request-URI Too Long",
        415 => "Unsupported Media Type",
        //416 => "Requested Range Not Satisfiable",
        417 => "Expectation Failed",
        418 => "I'm a teapot",

        500 => "Internal Server Error",
        501 => "Not Implemented",
        503 => "Service Unavailable",
    );

    /**
     * Réponse déjà envoyée au client ?
     * @var boolean
     */
    private $isSent = false;

    /**
     * @var string 
     */
    private $charset;
    
    /**
     * Entêtes http
     * @var array
     */
    private $headers = array();

    /**
     * @integer
     */
    private $status;

    /**
     * @var string
     */
    private $body;
    
    /**
     * Chargeur singleton
     * 
     * @return \Shrew\Mazzy\Core\Response;
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
    
    /**
     * Initialise la réponse par les valeurs par défaut
     *
     * En production le status par défaut de la réponse est 204, mais en 
     * développement, cela peut gêner fortement le débugage. Donc une détection
     * de l'environnement d'exécution est utilisée pour adapter la réponse par
     * défaut selon le contexte.
     */
    private function __construct() {
        
        $this->charset = mb_internal_encoding();
        
        if (HttpRequest::getInstance()->isDeveloppment() === true) {
            $this->setStatus(200);
            $this->setType("html"); // pour xdebug
            $this->setBody("");
        } else {
            $this->setStatus(204);
            $this->setBody("");
        }
    }
    
    /**
     * Récupère l'encodage utilisé pour la réponse
     */
    public function getCharset()
    {
        return $this->charset;
    }

    /**
     * Ajoute des entêtes http
     *
     * @param string $identifier
     * @param string $value
     * @return \Shrew\Mazzy\Core\Response
     */
    public function setHeader($identifier, $value)
    {
        $this->headers[$identifier] = $value;
        return $this;
    }

    /**
     * Change le corps de la réponse http
     *
     * @param string $str Texte à utiliser en corps de réponse
     * @return \Shrew\Mazzy\Core\Response
     */
    public function setBody($str)
    {
        // Le status est changé automatiquement au besoin
        if (!empty($str) && $this->status === 204) {
            $this->setStatus(200);
        }
        $this->body = (string) $str;
        return $this;
    }

    /**
     * Modifie le status de la réponse
     *
     * @param integer $status
     * @return \Shrew\Mazzy\Core\Response
     */
    public function setStatus($status)
    {
        if (isset(self::$listStatus[$status])) {
            $this->status = $status;
        }
        return $this;
    }

    /**
     * Modifie le mimetype de la réponse
     * 
     * @param string $mime Type mime à utiliser
     * @param boolean $detect Si à vrai, alors cherchera le mimetype exacte dans
     *                        la liste disponible par défaut. Sinon la réponse
     *                        utilisera exactement la valeur de `$mime`.
     * @return \Shrew\Mazzy\Core\Response
     * @throws \InvalidArgumentException
     */
    public function setType($mime, $detect = true)
    {
        if ($detect === true) {
            if (isset(self::$listTypes[$mime])) {
                $mime = sprintf(self::$listTypes[$mime], $this->charset);
            } else {
                throw new \InvalidArgumentException("Le type mime _{$mime}_ n'est "
                        . "pas pris en charge par l'application");
            }
        }
        $this->setHeader("Content-Type", $mime);

        return $this;
    }

    /**
     * Redirection vers une autre ressource
     *
     * @param string $url Url absolue vers laquelle faire pointer la redirection
     * @param integer $status Status http à utiliser.
     */
    public function location($url, $status = 307)
    {
        $this->setStatus($status);
        header("Location: $url", $this->status);
        exit();
    }

    /**
     * Redirection vers une autre ressource du site
     * @param string $url Url relative vers laquelle faire pointer la redirection
     * @param integer $status Status http à utiliser.
     */
    public function redirect($url, $status = 307)
    {
        $url = HttpRequest::getInstance()->getAbsoluteUrl() . $url;
        $this->location($url, $status);
    }

    /**
     * Réinitialisation de la réponse
     * 
     * @return \Shrew\Mazzy\Core\Response
     */
    public function reset()
    {
        $this->headers = array();
        $this->setBody("");
        $this->setStatus(204);
        
        return $this;
    }

    /**
     * Envoi les entêtes au client
     */
    public function sendHeaders()
    {
        $file = __FILE__;
        $line = __LINE__;
        if (headers_sent($file, $line)) {
            trigger_error("Les entêtes ont déjà été envoyées $file:$line", E_USER_ERROR);
        }

        $status = sprintf("HTTP/1.1 %d %s", $this->status, self::$listStatus[$this->status]);
        header($status);

        header("X-Powered-By: Unknown"); // securité, s'il vous plaît !!!
        foreach ($this->headers as $k => $v) {
            $k = str_replace(" ", "-", ucwords(str_replace("-", " ", $k)));
            header("$k: $v");
        }
    }

    /**
     * Envoi les entêtes sans le corps au client
     */
    public function out($status = null)
    {
        if ($status !== null) {
            $this->setStatus($status);
        }

        $this->isSent = true;
        $this->sendHeaders();
        exit;
    }

    /**
     * Envoi la réponse complète au client
     */
    public function send($body = null, $status = null)
    {
        if ($body !== null) {
            $this->setBody($body);
        }
        if ($status !== null) {
            $this->setStatus($status);
        }
        
        $this->isSent = true;
        $this->sendHeaders();
        echo $this->body;
        exit;
    }
    
    /**
     * Retourne un message d'erreur http
     * 
     * @param \Shrew\Mazzy\Core\OutputInterface|string $message
     * @param integer $status Status http à utiliser.
     */
    public function sendError($message, $status = 500)
    {
        $this->reset()->setStatus($status)->setType("text")->setBody($message)->send();
    }

    /**
     * Retourne le contenu d'un fichier au client. 
     *
     * @param string $filename Fichier à envoyer
     * @param boolean $download Force le téléchargement du fichier
     */
    public function sendFile($filename, $download = false)
    {
        if (!is_readable($filename)) {
            throw new HttpException("Le fichier `$filename` "
            . "n'est pas ouvert en lecture");
        }

        if ($download === true) {
            $this->setHeader("Content-disposition", "attachment;filename=$filename");
        }

        // Prépare les entêtes http
        $this->isSent = true;

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $this->setType(finfo_file($finfo, $filename), false);
        finfo_close($finfo);

        $this->sendHeaders();

        // Envoi du fichier
        ob_clean();
        flush();
        readfile($filename);
    }

    /**
     * Rend l'application injoignable 
     *
     * @param integer $life
     */
    public function setUnvailable($life = 1800)
    {
        $min = round($life / 60);
        header("Status: 503 Service Temporarily Unavailable", false, 503);
        header("Retry-After: $life");
        printf(_("Le serveur subit une maintenance, revenez dans : %s minutes"), $min);
        exit();
    }

    /**
     * Envoi automatique de la réponse si celle-ci n'a pas été envoyée
     */
    public function __destruct()
    {
        if (!$this->isSent === true) {
            $this->send();
        }
    }

}
