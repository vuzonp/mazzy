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

use Shrew\Mazzy\Lib\Core\Request;
use Shrew\Mazzy\Lib\Core\Response;
use Shrew\Mazzy\Lib\Report\Log;
use Shrew\Mazzy\Lib\Storage\Session;
use Shrew\Mazzy\Lib\Template\Template;
use Shrew\Mazzy\Lib\Template\TemplateException;


/**
 * Classe parente des contrôleurs
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-14
 */
abstract class HandlerAbstract
{

    /**
     * Charge une nouvelle instance de template
     * 
     * @param string $name Nom du fichier de template (sans extension)
     * @param string $theme Nom du thème à utiliser si besoin
     * @return \Shrew\Mazzy\Lib\Template\Template
     */
    protected function loadTemplate($name, $theme = null)
    {
        if ($theme !== null) {
            Template::setTheme($theme);
        }
        return new Template($name);
    }

    /**
     * Charge la session
     * 
     * @return \Shrew\Mazzy\Lib\Storage\Session
     */
    protected function getSession()
    {
        return Session::getInstance();
    }

    /**
     * Récupère la réponse
     * 
     * @return \Shrew\Mazzy\Lib\Core\Response
     */
    protected function getResponse()
    {
        return Response::getInstance();
    }

    /**
     * Lance la compilation d'un template
     * 
     * @param \Shrew\Mazzy\Lib\Template\Template $tpl
     * @param integer $status Code http
     */
    protected function render(Template $tpl, $status = null)
    {
        Response::getInstance()->render($tpl, $status);
    }

    /**
     * Effectue une redirection http
     * 
     * @param string $url
     * @param integer $status
     */
    protected function redirect($url, $status = 302)
    {
        Response::getInstance()->redirect($url, $status);
    }

    /**
     * Effectue une redirection http suite à un traitement de données
     * reçues par formulaire
     * 
     * @param string $url
     */
    protected function redirectForm($url)
    {
        $this->redirect($url, 303);
    }

    /**
     * Permet de vérifier que la méthode http utilisée correspond bien à l'une
     * de celles passées en arguments.
     * 
     * @return boolean
     */
    protected function verifyHttpMethod()
    {
        $length = func_num_args();
        $method = Request::getInstance()->getMethod();
        for ($i = 0; $i < $length; $i++) {
            if ($method === strtoupper(func_get_arg($i))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Gestionnaire d'erreurs http
     * 
     * @param string $message Message à retourner au client (rédigé en markdown/html)
     * @param integer $code Code http à utiliser pour l'erreur
     */
    protected function sendError($message, $code)
    {
        $res = $this->getResponse();

        // Tente d'envoyer l'erreur par un template
        try {

            $tpl = new Template("error");
            $tpl->message = $message;
            $tpl->code = $code;

            $res->render($tpl);

            // Si aucun template n'est disponible pour l'affichage des erreurs
            // On la retourne au format texte.
        } catch (TemplateException $e) {

            Log::debug($e->getMessage(), $e->getFile(), $e->getLine());
            Log::notice($message);

            $res->setStatus($code);
            $res->setType("text");
            $res->setBody($message);
            $res->send();

            // On ferme l'application
        } finally {
            exit();
        }
    }

}
