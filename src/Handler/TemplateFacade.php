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

namespace Shrew\Mazzy\Handler;

use Shrew\Mazzy\Config\Config;
use Shrew\Mazzy\Core\HttpRequest;
use Shrew\Mazzy\Core\HttpResponse;
use Shrew\Mazzy\Template\Template;

/**
 * Description of TemplateFacade
 *
 * @author thomas
 */
class TemplateFacade
{
    protected $life;
    
    /**
     * @var \Shrew\Mazzy\Core\HttpResponse 
     */
    protected $response;
    
    /**
     * @param \Shrew\Mazzy\Config\Config $settings
     */
    protected $settings;
    
    /**
     * @var \Shrew\Mazzy\Template\Template 
     */
    protected $template;
    
    /**
     * @var array 
     */
    protected $queue;


    /**
     * @param \Shrew\Mazzy\Core\HttpRequest $request
     * @param \Shrew\Mazzy\Core\HttpResponse $response
     * @param \Shrew\Mazzy\Config\Config $settings
     */
    public function __construct(HttpRequest $request, HttpResponse $response, Config $settings)
    {
        $www = $request->getRootUrl();
        $assets = $settings->get("view.assets", null);
        
        if ($assets === null) {
            $assets = $www;
        } else if(strpos($assets, "://") === false) {
            $assets = $www . $assets;
        }
        
        Template::setGlobal("www", $www);
        Template::setGlobal("assets", $assets);
        Template::setGlobal("charset", $response->getCharset());
        $this->queue = array();
        
        $this->life = 0;
        $this->response = $response;
        $this->settings = $settings;
    }
    
    /**
     * Charge et active un template spécifique
     * 
     * @param string $template Nom du template à charger
     * @param string $theme Spécifie un thème spécifique
     * @param string $mime Type mime à utiliser
     * @param boolean $detect Si à vrai, alors cherchera le mimetype exacte dans
     *                        la liste disponible par défaut. Sinon la réponse
     *                        utilisera exactement la valeur de `$mime`.
     * 
     * @return \Shrew\Mazzy\Handler\TemplateFacade
     */
    public function load($template, $theme = null, $mime = "html", $detect = true)
    {
        $this->template = new Template($this->settings);
        $this->template->prepare($template, $theme);
        
        // Ajout des valeurs placées en file d'attente
        foreach($this->queue as $label => $value) {
            $this->template->set($label, $value);
        }
        
        $this->response->setType($mime, $detect);
        return $this;
    }
    
    /**
     * Place le résultat en cache
     * 
     * @param integer $life Durée de vie maximale du cache
     * @return \Shrew\Mazzy\Handler\OutputFacade
     */
    public function cache($life)
    {
        $this->life = $life;
        return $this;
    }
    
    /**
     * @param string $label
     * @param mixed $value
     */
    public function set($label, $value)
    {
        // Si le template n'est pas encore chargé, place les données en file
        // d'attente
        if ($this->template === null) {
            $this->queue[$label] = $value;
        } else {
            $this->template->set($label, $value);
        }
    }
    
    public function __set($label, $value)
    {
        $this->set($label, $value);
    }
    
    public function render($status = 200)
    {
        $this->response->setStatus($status);
        $this->response->setBody($this->template->generate());
    }
}
