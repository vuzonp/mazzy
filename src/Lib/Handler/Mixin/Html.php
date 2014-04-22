<?php

/*
 * The MIT License
 *
 * Copyright 2014 thomas.
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
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Shrew\Mazzy\Lib\Handler\Mixin;

use Shrew\Mazzy\Lib\Core\Collection;
use Shrew\Mazzy\Lib\Cache\CacheFile;
use Shrew\Mazzy\Lib\Core\Config;
use Shrew\Mazzy\Lib\Template\Template;

/**
 * Description of Template
 *
 * @author thomas
 */
trait Html
{
    /**
     * Charge une instance de template et prépare la réponse
     * 
     * @param string $template Fichier de template à utiliser
     * @param integer $status Code http de la réponse
     * @return \Shrew\Mazzy\Lib\Template\Template
     */
    protected function loadTemplate($template, $status = 200)
    {
        if (Template::hasDefaultTheme() === false) {
            $config = Config::get("view");
            Template::setDefaultTheme($config["defaultTheme"]);
            Template::setGlobal("assets", $config["assets"]);
        }
        
        $tpl =  new Template($template);
        $this->response->setType($tpl->getType());
        $this->response->setStatus($status);
        return $tpl;
    }
    
    /**
     * Prépare les données pour être exploitables par le moteur de template
     * 
     * @param string $rawLabel Nom des variables de template sans nettoyage
     * @param array|\Shrew\Mazzy\Lib\Core\Collection $data Données d'output
     * @return \SplFixedArray dont la première clé correspond au label 
     *                        des données et la seconde aux données elles-même
     * @throws HandlerException Lorsque les données ne sont pas compatibles
     */
    protected function prepareTplData($rawLabel, $data)
    {
        $tplData = new \SplFixedArray(2);
        
        // Vérifie le bon format des données d'output
        if (is_array($data)) {
            $tplData[1] = new Collection($data);
        } elseif ($data instanceof Collection) {
            $tplData[1] = $data;
        } else {
            throw new HandlerException("Les variables de templates doivent de type Collection", 500);
        }
        
        // Retourne les données préparées pour être exploitées par le template sous forme de tableau
        $tplData[0] = substr($rawLabel, strrpos($rawLabel, "/"));
        return $tplData; 
    }
    
    /**
     * Retourne une vue html au client
     * 
     * @param string $template Nom du template à utiliser
     * @param array|\Shrew\Mazzy\Lib\Core\Collection $data Données du template
     * @param integer $status Status http de la réponse
     * @param integer $life Durée de vie du cache en secondes
     */
    protected function render($template, $data = null, $status = 200, $life = 0)
    {       
        $tpl = $this->loadTemplate($template, $status);
        
        // Initialisation du cache
        $name = str_replace("/", ".", $template) . "-";
        $cache = new CacheFile($name, $life);
        $cache->generateUID(array($data, $tpl->getHash()));
        
        // Retourne le cache s'il existe
        if ($cache->exists())
        {
            $this->response->sendFile((string) $cache->getFile());
            
        // Génère le template si le cache est périmé ou inexistant
        } else {
            $cache->purge();
            $tplData = $this->prepareTplData($template, $data);
            $tpl->set($tplData[0], $tplData[1]);
            $output = $tpl->generate();
            $cache->save($output);
            $this->response->setBody($output);
        }
    }
}
