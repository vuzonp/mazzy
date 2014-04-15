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

namespace Shrew\Mazzy\Lib\Route;

/**
 * Le router permet de charger un contrôleur à partir de l'url
 * appelée par le client. 
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-13
 */
class Router implements RouterInterface
{

    /**
     * Liste des espaces de noms utilisés par les contrôleurs
     * @var \SplStack 
     */
    private $namespaces;

    /**
     * Liste des routes disponibles
     * @var array 
     */
    private $routes;

    /**
     * Définition des motifs de remplacement utilisés dans les routes
     * @var array 
     */
    private $aliases;

    public function __construct()
    {
        $this->namespaces = new \SplStack();
        $this->routes = array();
        $this->aliases["needles"][] = "/";
        $this->aliases["regex"][] = "\/";
    }

    /**
     * Permet de rendre accessible aux routes un espace de nom.
     *
     * @param string $namespace Espace de nom sans `\\` final
     */
    final public function pushNamespace($namespace)
    {
        $this->namespaces->push("$namespace\\");
    }

    /**
     * Ajoute une route
     *
     * @param string $pattern motif d'url
     * @param string $class Nom de la classe cible
     * @param string $method Nom de la méthode de classe cible
     */
    final public function set($pattern, $class, $method)
    {
        $this->routes[$pattern] = array(ucfirst($class), $method);
    }

    /**
     * Permet d'ajouter des alias de motifs regex pour les routes
     *
     * Par exemple, permet d'utiliser `:num` au lieu de `[0-9]+`
     *
     * @param string $needle
     * @param string $regex
     */
    final public function setAliases($needle, $regex)
    {
        $this->aliases["needles"][] = $needle;
        $this->aliases["regex"][] = "($regex)";
    }

    /**
     * Effectue une recherche sur les routes disponibles à partir de l'url
     * passée en argument. Lorsqu'une route est trouvée, la méthode exécute son
     * contrôleur.
     *
     * @param string $path Url sur laquelle effectuer la recherche
     * @return boolean Retourne true si la route est trouvée
     * @throws RouterException Lorsqu'aucune route n'est trouvée
     */
    public function find($path)
    {
        if ($path !== "/") {
            $path = rtrim($path, "/");
        }

        // On commence par vérifier si l'uri existe de façon identique parmi
        // les motifs de routes.
        if (isset($this->routes[$path])) {
            $this->launch($this->routes[$path][0], $this->routes[$path][1]);
            return true;
        }
        // Sinon on effectue une recherche en utilisant des regex
        else {
            $matches = array();
            foreach ($this->routes as $route => $handler) {
                // On transforme le motif en regex
                $pattern = str_replace(
                        $this->aliases['needles'], $this->aliases["regex"], $route
                );

                $pattern = "!^" . $pattern . "$!i";

                // On teste la regex
                if (preg_match_all($pattern, $path, $matches) > 0) {
                    // Récupération des paramètres
                    $params = array_column(array_slice($matches, 1), 0);

                    // On retourne la route trouvée
                    $this->launch($handler[0], $handler[1], $params);
                    return true;
                }
            }
            // Aucune route correspondante
            throw new RouterException("Page `$path` introuvable", 404);
        }
    }

    /**
     * Dispatcher
     *
     * @param string $className Contrôleur à exécuter
     * @param string $methodName Méthode à appeler
     * @param array $params Arguments à transmettre à la méthode du contrôleur
     * @return mixed Fait suivre la valeur retournée par le contrôleur s'il y en a une
     */
    private function launch($className, $methodName = null, Array $params = array())
    {
        // Cherche l'espace de nom de la classe
        $found = false;
        foreach ($this->namespaces as $namespace) {
            $c = $namespace . $className;
            if (class_exists($c)) {
                $found = true;
                $className = $c;
                break;
            }
        }
        // La classe n'existe pas
        if ($found === false) {
            throw new \LogicException("La classe contrôleur `$className` n'existe pas", 500);
        }
        else {
            $controller = new $className();

            if (!method_exists($controller, $methodName)) {
                throw new \LogicException("La méthode `$className::$methodName` "
                . "n'est pas disponible", 500);
            }
        }

        return call_user_func_array(array($controller, $methodName), $params);
    }

}
