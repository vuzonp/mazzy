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

namespace Shrew\Mazzy\Lib\Handler;

use Shrew\Mazzy\Lib\Core\Collection;
use Shrew\Mazzy\Lib\Core\Config;
use Shrew\Mazzy\Lib\Core\Response;
use Shrew\Mazzy\Lib\Cache\CacheFile as Cache;
use Shrew\Mazzy\Lib\Template\Template;


/**
 * Génère des réponses à partir des contrôleurs
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class OutputFacade
{
    /**
     * @var \Shrew\Mazzy\Lib\Template\Template 
     */
    private $tpl;
    
    /**
     * @var string 
     */
    private $name;
    
    /**
     * @var string 
     */
    private $label;
    
    /**
     * @var \Shrew\Mazzy\Lib\Core\Collection 
     */
    private $collection;
    
    /**
     * @var integer 
     */
    private $life;

    public function __construct()
    {
        $config = Config::get("view");
        Template::setDefaultTheme($config["defaultTheme"]);
        Template::setTheme($config["theme"]);
        Template::setGlobal("assets", $config["assets"]);
        
        $config = Config::get("cache");
        Cache::setPath($config["directory"]);
        
        $this->collection = new Collection();
        $this->life = 0;
    }
    
    /**
     * Charge et active un template spécifique
     * 
     * @param string $template Nom du template à charger
     * @param string $theme Spécifie un thème spécifique
     * @return \Shrew\Mazzy\Lib\Handler\OutputFacade
     */
    public function load($template, $theme = null)
    {
        if ($theme !== null) {
            Template::setTheme($theme);
        }
        $this->name = str_replace("/", ".", $template);
        $this->label = substr($template, strrpos($template, "/"));
        $this->tpl = new Template($template);
        
        return $this;
    }
    
    /**
     * Place le résultat en cache 
     * 
     * @param integer $life Durée de vie maximale du cache
     * @return \Shrew\Mazzy\Lib\Handler\OutputFacade
     */
    public function cache($life)
    {
        $this->life = intval($life);
        return $this;
    }
    
    /**
     * Ajoute une valeur à destination de la réponse
     * 
     * @param string $label
     * @param mixed $value
     */
    public function set($label, $value)
    {
        if (is_object($value)) {
            $this->tpl->set($label, $value);
        } elseif (is_array($value)) {
            $this->tpl->set($label, new Collection($value));
        } else {
            $this->collection->set($label, $value);
        }
    }
    
    /**
     * @param string $label
     * @param mixed $value
     */
    public function __set($label, $value)
    {
        $this->set($label, $value);
    }
    
    /**
     * Compilation de la vue et envoi de la réponse
     * 
     * Cette méthode utilise un système de cache lorsque le contenu n'a pas
     * évolué et si le template n'a pas été édité depuis la création du cache.
     * 
     * @param integer $status Status http de la réponse
     */
    public function render($status = 200)
    {
        $this->tpl->set($this->label, $this->collection);
        
        // Initialisation du cache
        $cache = new Cache($this->label, $this->life);
        $cache->generateUID(array($this->tpl, $this->tpl->getHash()));
        
        $response = Response::getInstance();
        $response->setStatus($status);
        
        if ($cache->exists() === true) {
            $response->sendFile((string) $cache->getFile());
        } else {
            $cache->purge();
            $output = $this->tpl->generate();
            $cache->save($output);
            $response->setBody($output);
        }
    }
    
}
