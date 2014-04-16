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
use Shrew\Mazzy\Lib\Input\Input;
use Shrew\Mazzy\Lib\Report\Log;
use Shrew\Mazzy\Lib\Template\Template;
use Shrew\Mazzy\Lib\Template\TemplateException;


/**
 * Application concrète de HandlerAbstract
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-14
 */
class Handler
{

    protected $request;
    protected $response;
    protected $input;

    public function __construct()
    {
        $this->request = Request::getInstance();
        $this->response = Response::getInstance();
        $this->input = Input::getRequests();
    }

    
    /**
     * Permet de vérifier que la méthode http utilisée correspond bien à l'une
     * de celles passées en arguments.
     * 
     * @return boolean
     */
    final protected function verifyHttpMethod()
    {
        $length = func_num_args();
        $method = $this->request->getMethod();
        for ($i = 0; $i < $length; $i++) {
            if ($method === strtoupper(func_get_arg($i))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Effectue une redirection http
     * 
     * @param string $url
     * @param integer $status
     */
    final protected function redirect($url, $status = 302)
    {
        $this->response->redirect($url, $status);
    }

    /**
     * Effectue une redirection http suite à un traitement de données
     * reçues par formulaire
     * 
     * @param string $url
     */
    final protected function redirectForm($url)
    {
        $this->redirect($url, 303);
    }

    /**
     * Gestionnaire d'erreurs http
     * 
     * @param string $message Message à retourner au client (rédigé en markdown/html)
     * @param integer $code Code http à utiliser pour l'erreur
     */
    final protected function sendError($message, $code)
    {

        // Tente d'envoyer l'erreur par un template
        try {

            $tpl = new Template("error");
            $tpl->message = $message;
            $tpl->code = $code;

            $this->response->render($tpl);

            // Si aucun template n'est disponible pour l'affichage des erreurs
            // On la retourne au format texte.
        } catch (TemplateException $e) {

            Log::debug($e->getMessage(), $e->getFile(), $e->getLine());
            Log::notice($message);

            $this->response->setStatus($code);
            $this->response->setType("text");
            $this->response->setBody($message);
            $this->response->send();

            // On ferme l'application
        } finally {
            exit();
        }
    }
    
    

}
