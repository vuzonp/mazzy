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

namespace Shrew\Mazzy\Storage;

use Shrew\Mazzy\Core\Config;
use Shrew\Mazzy\Core\Request;


/**
 * Gestionnaire de sessions de l'application
 * permettant de manipuler les sessions en *poo*
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Session implements \ArrayAccess, \Iterator
{
    private static $instance;

    private static $name = "sess_app";
    private static $lifetime = 0;
    private static $domain = null;
    private static $path = "/";

    /**
     * @return Shrew\Mazzy\Storage\Session
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
        $this->initialize();
    }

    /**
     * Charge et initialise la session
     * 
     * @throws AppException Les entêtes http ont déjà été envoyées
     */
    final protected function initialize()
    {
        $config = Config::get("session");
        
        // On récupère la requête
        $req = Request::getInstance();

        // On commence par vérifier si les entêtes http ont été envoyées
        $file = __FILE__; $line = __LINE__;
        if (headers_sent($file, $line)) {
            throw new AppException("La réponse a été lancée ailleurs : `$file:$line`", 500);
        }

        // Doit-on utiliser le mode https ?
        $secure = ($req->isSecure()) ? true : false;

        // Configuration de la session
        session_name($config["name"]);
        session_set_cookie_params($config["life"], $config["path"], $config["domain"], $secure, true);

        // Cache Http desactive pour la cause
        // http://fr2.php.net/manual/fr/function.session-cache-limiter.php#46827
        session_cache_limiter("nocache");
        session_cache_expire($config["life"] / 60);

        // Démarrage de la session
        session_start();

        // Si la requête n'est pas de type ajax on regénère l'id
        // bug : http://blog.teamtreehouse.com/how-to-create-bulletproof-sessions
        if ($req->isXhr() === false) {
            session_regenerate_id(true);
        }

        // La session n'existe pas ou ne correspond pas à l'uid du client...
        // ... on l'initialise.
        $uid = crc32($req->getUserIp() . $req->getUserAgent());
        if ((empty($_SESSION)) || (empty($_SESSION["_uid"])) || ($_SESSION["_uid"] !== $uid)) {
            $_SESSION = array();
            $_SESSION["_uid"] = $uid;
        }
    }

    /**
     * Retourne une rangée de la session
     *
     * @param string $key Clé de la rangée
     * @return mixed
     */
    public function get($key, $filter = FILTER_DEFAULT, $options = null)
    {
        return filter_input(INPUT_SESSION, $key, $filter, $options);
    }

    /**
     * Ajoute ou édite une entrée à la session
     *
     * @param string $key Clé d'identification de la rangée
     * @param mixed $value Valeur de la rangée
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Supprime une rangée dans la session
     *
     * @param string $key Clé de la rangée
     */
    public function delete($key)
    {
        $_SESSION[$key] = null;
        unset($_SESSION[$key]);
    }

    /**
     * Destruction de la session
     */
    public function destroy()
    {
        $_SESSION = array();
        unset($_SESSION);
        session_destroy();

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                "",
                0, 
                $params["path"],
                $params["domain"], 
                $params["secure"], 
                $params["httponly"]
            );
        }
    }
      
    // Iterators
    //--------------------------------------------------------------------------
    
    function rewind()
    {
        return reset($_SESSION);
    }

    function current()
    {
        return current($_SESSION);
    }

    function key()
    {
        return key($_SESSION);
    }

    function next()
    {
        return next($_SESSION);
    }

    function valid()
    {
        return key($_SESSION) !== null;
    }

    public function offsetExists($key)
    {
        return isset($_SESSION[$key]);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value)
    {
        if (is_null($key)) {
            $_SESSION[] = $value;
        } else {
            $_SESSION[$key] = $value;
        }
    }

    public function offsetUnset($key)
    {
        $this->del($key);
    }
}
