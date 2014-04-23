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

namespace Shrew\Mazzy\Lib\Security;

use Shrew\Mazzy\Lib\Storage\Session;


/**
 * Gestionnaire des jeton de sécurité
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Token
{

    /**
     * @var \Shrew\Mazzy\Lib\Storage\Session
     */
    private $session;

    /**
     * Identifiant du token
     * @var string 
     */
    private $pid;

    /**
     * Valeur de la session
     * @var string 
     */
    private $value;

    /**
     * Initialise le jeton de sécurité
     * 
     * @param string $name Nom du jeton à charger
     */
    public function __construct($name)
    {
        $this->pid = "_token.$name";
        $this->session = Session::getInstance();
        $this->value = $this->session->get($this->pid);
    }

    /**
     * Récupère le jeton de sécurité généré
     * 
     * @return string
     */
    public function get()
    {
        return (string) $this->value;
    }

    /**
     * Génère et attribue une nouvelle valeur
     * 
     * @return \Shrew\Mazzy\Lib\Security\Token
     */
    public function generate()
    {
        $strong = false;
        $value = base_convert(openssl_random_pseudo_bytes(8192, $strong), 2, 35);
        $this->session->set($this->pid, $value);
        $this->value = $value;
        return $this;
    }

    /**
     * Vérifie la validité d'un jeton en comparaison avec celui de l'objet
     * 
     * @param string $token
     * @return boolean
     */
    public function compare($token)
    {
        return ($this->value === $token);
    }

    /**
     * Réinitialise le token par une valeur nulle
     */
    public function delete()
    {
        $this->value = null;
        $this->session->delete($this->pid);
        return true;
    }

    public function __toString()
    {
        return $this->get();
    }

}
