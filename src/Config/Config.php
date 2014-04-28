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

namespace Shrew\Mazzy\Config;

/**
 * Collection d'options de configurations
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Config
{
    
    /**
     * @var array 
     */
    private $options;

    /**
     * @param array $options Options de configuration
     */
    public function __construct(array $options = array())
    {
        $this->options = $options;
    }

    /**
     * Permet d'attribuer des valeurs par défaut aux options de configuration
     * 
     * Cette méthode peut être appelée à tout moment, les options préalablement
     * définies sont conservées et seules les options non déclarées sont 
     * ajoutées.
     * 
     * @param array $options Options par défaut
     */
    public function setDefault(array $options = array())
    {
        $this->options = array_replace_recursive($this->options, $options);
    }

    /**
     * Permet d'ajouter de nouvelles options de configuration par lots (sous forme de tableau)
     * 
     * @param array $options Lot d'options à ajouter à la configuration
     */
    public function setMany(array $options = array())
    {
        $this->options = array_replace_recursive($options, $this->options);
    }

    /**
     * Permet de définir une option de configuration
     * 
     * @param string $label Nom de l'option
     * @param mixed $option Valeur de l'option
     */
    public function set($label, $option)
    {
        $this->options[$label] = $option;
    }

    /**
     * Permet de récupérer une option de configuration.
     * 
     * @param string $label Nom de l'option
     * @param mixed $default Valeur de remplacement
     * @return mixed
     */
    public function get($label, $default = null)
    {
        if (isset($this->options[$label])) {
            return $this->options[$label];
        } else {
            $this->set($label, $default);
            return $default;
        }
    }

    /**
     * Permet de récupérer l'ensemble des options de configuration
     * 
     * @return array
     */
    public function getAll()
    {
        return $this->options;
    }

}
