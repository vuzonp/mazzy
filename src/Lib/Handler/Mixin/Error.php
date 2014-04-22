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
use Shrew\Mazzy\Lib\Core\Config;
use Shrew\Mazzy\Lib\Report\Log;
use Shrew\Mazzy\Lib\Template\TemplateException;

/**
 * Description of Error
 *
 * @author thomas
 */
trait Error
{
    use Html;
    
    public function sendException(\Exception $e)
    {
        // Récupération des données
        $code = $e->getCode();
        $name = sprintf(_("Erreur %s"), $code);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $trace = $e->getTraceAsString();
        
        $error = new Collection(compact("code", "name", "message", "file", "line", "trace", "debug"));
        
        if (Config::isDeveloppment() === false) {
            $error->message = _("Une erreur s'est produite");
            $error->file = null;
            $error->line = null;
            $error->trace = null;
        } 

        // Log de l'erreur
        if ($code < 500) {
            Log::notice($message, $file, $line);
        } else {
            Log::error($message, $file, $line);
        }

        // Retourne l'erreur en html ou bien au format texte
        try {
            $this->render("error", $error, $code, 3600);
        } catch (TemplateException $e) {
            $body = "{$error->message}\n\n{$error->trace}";
            $this->response->sendError($body, $code);
        }
    }
    
    public function sendError($message, $code = 500)
    {
        $error = new Collection();
        $error->name = sprintf(_("Erreur %s"), $code);
        $error->message = $message;
        $error->code = $code;
        
        // Retourne l'erreur en html ou bien au format texte
        try {
            $this->render("error", $error, $code, 3600);
        } catch (TemplateException $e) {
            $body = "{$error->message}\n\n{$error->trace}";
            $this->response->sendError($body, $code);
        }
    }
}
