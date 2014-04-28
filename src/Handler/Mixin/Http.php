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

/**
 * Description of Http
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
trait Http
{
    /**
     * Permet de vérifier que la méthode http utilisée correspond bien à l'une
     * de celles passées en arguments.
     * 
     * @return boolean
     */
    protected function verifyHttpMethod()
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
    protected function redirect($url, $status = 302)
    {
        $this->response->redirect($url, $status);
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
}
