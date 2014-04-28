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

namespace Shrew\Mazzy\Storage;

/**
 * Stockage de type flash permettant de lever des alertes destinées aux utilisateurs
 * 
 * *Contrairement à Flash, cette classe utilise un système **Filo** pour stocker les
 * différentes valeurs fournies à l'objet.*
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class FlashAlert extends Flash
{

    /**
     * Valeurs de stockage
     * @var SplStack 
     */
    protected $values;

    /**
     * Initialisation
     *
     * @param integer $mode Mode d'ouverture de l'objet
     */
    public function __construct($mode = self::READ)
    {
        $name = "alert";
        parent::__construct($name, $mode);
    }

    /**
     * Récupère et supprime les valeurs depuis la session.
     *
     * @param string $key identifiant utilisé en session
     * @return \SplStack Tableau contenant les valeurs préalablement stockées en session
     */
    protected function loadAndPurge($key)
    {
        $values = $this->session->get($key);
        if ($values === null) {
            $values = new \SplStack();
        } else {
            $this->session->delete($key);
        }
        return $values;
    }

    /**
     * Les alertes sont-elles vides ?
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return $this->values->isEmpty();
    }

    /**
     * Permet d'ajouter une nouvelle entrée
     *
     * @param string $level Identifiant de l'entrée
     * @param mixed $message Valeur à stocker
     */
    public function set($level, $message)
    {
        $row = array("type" => $level, "message" => $message);
        $this->values->push($row);
        $this->save();
    }

    /**
     * Alias de la méthode set
     *
     * @param string $level Identifiant de l'entrée
     * @param mixed $message Valeur à stocker
     */
    public function push($level, $message)
    {
        $this->set($level, $message);
    }

    /**
     * Vide intégralement les données stockées au sein de l'objet et dans la session
     */
    public function purge()
    {
        $this->values = new \SplStack();
        $this->session->delete($this->pid);
    }

    /**
     * Permet de récupérer les données pour une manipulation par la vue
     *
     * @return array
     */
    public function getPublic()
    {
        return iterator_to_array(parent::getPublic());
    }

}
