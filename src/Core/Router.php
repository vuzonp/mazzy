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
 * Router de l'application
 * 
 * Le routage est compatible avec les techniques REST et permet d'utiliser, selon
 * les besoins, un système de fonctions de rappel ou bien le chargement de classes
 * contrôleurs. 
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Router implements \IteratorAggregate
{
    
    private $verb;
    private $url;
    private $buffer;
    
    private $paramSearch;
    private $paramReplace;
    private $namespace;
    private $found;

    /**
     * @param string $verb Méthode http de la requête
     * @param string $url URL de la requête
     */
    public function __construct($verb, $url)
    {
        $this->buffer = new \SplQueue();
        $this->found = false;
        $this->verb = $verb;
        $this->url = $url;
        
        // Préparation des motifs de base
        $this->paramSearch = array();
        $this->paramReplace = array();
        $this->paramSearch[0] = "*";
        $this->paramReplace[0] = ".*";
        $this->paramSearch[1] = "/";
        $this->paramReplace[1] = "\/";
    }
    
    /**
     * Permet de definir des motifs de parametres
     * 
     * @param string $param Motif du paramètre
     * @param string $regex Valeur regex du motif
     */
    public function param($param, $regex) 
    {        
        $this->paramSearch[] = ":" . ltrim($param, ":");
        $this->paramReplace[] = $regex;
    }

    /**
     * Attribue un espace de nom aux ressources
     * 
     * @param string $namespace Permet de définir un espace de nom 
     *                          pour les classes associées aux routes
     * @return \Shrew\Mazzy\Core\Router
     */
    public function setNamespace($namespace)
    {
        $this->namespace = trim($namespace, "\\") . "\\";
        return $this;
    }

    /**
     * Ajoute une nouvelle route / middleware
     * 
     * @param string $verb Méthode http concernée par la route
     * @param string $urlPattern Motif d'identification de l'url
     * @param \Closure|string $resource Une fonction de callback ou un nom de classe
     * @param null|string $action Si $resource est une classe, alors représente sa méthode
     * @return \Shrew\Mazzy\Core\Router
     */
    public function add($verb, $urlPattern, $resource, $action = null, $next = false) 
    {
        if ($this->found === false && $verb === $this->verb && ($params = $this->getParameters($urlPattern)) !== false) {
            $rsc = new \stdClass;
            $rsc->namespace = $this->namespace;
            $rsc->handler = $resource;
            $rsc->method = $action;
            $rsc->params = $params;
            $this->buffer->enqueue($rsc);
            
            $this->found = ($next !== true) ? true : false;
            
        }
        
        return $this;
    }
    
    final public function middleware($urlPattern, $resource, $action = null)
    {
        return $this->add($this->verb, $urlPattern, $resource, $action, true);
    }
    
    /**
     * Ajoute une nouvelle route pour l'ensemble des méthodes http
     * 
     * @param string $urlPattern Motif d'identification de l'url
     * @param \Closure|string $resource Une fonction de callback ou un nom de classe
     * @param null|string $action Si $resource est une classe, alors représente sa méthode
     * @return \Shrew\Mazzy\Core\Router
     */
    final public function all($urlPattern, $resource, $action = null, $next = false)
    {
        return $this->add($this->verb, $urlPattern, $resource, $action, $next);
    }
    
    /**
     * Ajoute une nouvelle route pour les méthodes de type POST
     * 
     * @param string $urlPattern Motif d'identification de l'url
     * @param \Closure|string $resource Une fonction de callback ou un nom de classe
     * @param null|string $action Si $resource est une classe, alors représente sa méthode
     * @return \Shrew\Mazzy\Core\Router
     */
    final public function post($urlPattern, $resource, $action = null, $next = false)
    {
        return $this->add("POST", $urlPattern, $resource, $action, $next);
    }
    
    /**
     * Ajoute une nouvelle route pour les méthodes de type GET
     * 
     * @param string $urlPattern Motif d'identification de l'url
     * @param \Closure|string $resource Une fonction de callback ou un nom de classe
     * @param null|string $action Si $resource est une classe, alors représente sa méthode
     * @return \Shrew\Mazzy\Core\Router
     */
    final public function get($urlPattern, $resource, $action = null, $next = false)
    {
        return $this->add("GET", $urlPattern, $resource, $action, $next);
    }
    
    /**
     * Ajoute une nouvelle route pour les méthodes de type PUT
     * 
     * @param string $urlPattern Motif d'identification de l'url
     * @param \Closure|string $resource Une fonction de callback ou un nom de classe
     * @param null|string $action Si $resource est une classe, alors représente sa méthode
     * @return \Shrew\Mazzy\Core\Router
     */
    final public function put($urlPattern, $resource, $action = null, $next = false)
    {
        return $this->add("PUT", $urlPattern, $resource, $action, $next);
    }
    
    /**
     * Ajoute une nouvelle route pour les méthodes de type DELETE
     * 
     * @param string $urlPattern Motif d'identification de l'url
     * @param \Closure|string $resource Une fonction de callback ou un nom de classe
     * @param null|string $action Si $resource est une classe, alors représente sa méthode
     * @return \Shrew\Mazzy\Core\Router
     */
    final public function delete($urlPattern, $resource, $action = null, $next = false)
    {
        return $this->add("DELETE", $urlPattern, $resource, $action, $next);
    }
    
    /**
     * Récupère les parametres de la ressource si celle-ci correspond a l'url
     * 
     * @param string $urlPattern
     * @return array|boolean
     */
    protected function getParameters($urlPattern)
    {
        // La route et l'url correspondent en tous points
        if ($urlPattern === $this->url || $urlPattern === "*") {
            return array();
        }
        
        // Transforme la route en motif regex
        $matches = array();
        $pattern = str_replace($this->paramSearch, $this->paramReplace, $urlPattern);
        $pattern = "!^" . $pattern . "$!i";
        
        if (preg_match_all($pattern, $this->uri, $matches) > 0) {
                return array_column(array_slice($matches, 1), 0);
        }
        
        return false;
    }
    
    /**
     * Vérifie si au moins une route a pu être validée
     * 
     * @return boolean
     */
    final public function hasResults()
    {
        return ($this->buffer->count() > 0);
    }
    
   
    public function getIterator()
    {
        return $this->buffer;
    }
}
