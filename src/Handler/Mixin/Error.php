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

namespace Shrew\Mazzy\Handler\Mixin;

use Shrew\Mazzy\Template\TemplateException;

/**
 * Description of Error
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
trait Error
{   
    /**
     * 
     * @param \Exception $e
     */
    final public function sendException(\Exception $e)
    {
        $this->response->reset();
        
        // Si une précédente exception est trouvée, c'est celle-ci qui est 
        // affichée en mode développement
        if ($this->request->isDeveloppment() === true && $e->getPrevious()) {
            while($e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }
        }
        
        // Récupération des données
        $code = $e->getCode();
        if ($code < 400) {
            $code = 500;
        }
        $name = sprintf(_("Erreur %s"), $code);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();
        
        $public = array();
        $public["code"] = $code;
        $public["name"] = $name;

        if ($this->request->isProduction() === true) {
            $public["message"] = _("Une erreur s'est produite");
            $public["file"] = null;
            $public["line"] = null;
            $public["trace"] = null;
        } else {
            $public["message"] = $message;
            $public["file"] = $file;
            $public["line"] = $line;
            $public["trace"] = $trace;
        }

        // Retourne l'erreur en html ou bien au format texte
        try {
            $this->view->load("error");
            $this->view->set("error", $public);
            $this->view->render($code);
        } catch (TemplateException $e) {
            $body = "{$public["message"]}\n\n{$public["trace"]}";
            $this->response->sendError($body, $code);
        }
    }    
    
    /**
     * Génère une erreur à retourner au client 
     *
     * @param string $message
     * @param integer $code
     */
    final protected function sendError($message, $code = 500)
    {      
        // Retourne l'erreur en html ou bien au format texte
        try {
            $this->view->load("error");
            
            $this->view->name = sprintf(_("Erreur %s"), $code);
            $this->view->code = $code;
            $this->view->message = $message;
            
            $this->view->render($code);
            
        } catch (TemplateException $e) {
            $this->response->sendError($message, $code);
        }
    }
}
