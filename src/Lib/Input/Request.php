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

namespace Shrew\Mazzy\Lib\Input;

/**
 * Gestionnaire de récupération des données de type **get** et **post**.
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Request extends InputContainer
{
    
    /**
     * Détecte la provenance d'une valeur
     * 
     * @param string $label
     * @return integer
     */
    private function getType($label)
    {
        return (filter_has_var(INPUT_GET, $label)) ? INPUT_GET : INPUT_POST;
    }

    /**
     * Vérifie l'existence d'une valeur
     * 
     * @param string $label
     * @return boolean
     */
    final public function exists($label)
    {
        return (filter_has_var(INPUT_GET, $label) || filter_has_var(INPUT_GET, $label));
    }

    /**
     * 
     * Récupération d'une valeur
     * 
     * @param string $label
     * @param integer $filter L'ID du (filtre)[http://www.php.net/manual/fr/filter.filters.php] à appliquer.
     * @param mixed $options Tableau associatif d'options ou des drapeaux
     * @return mixed
     */
    final public function get($label, $filter = FILTER_DEFAULT, $options = null)
    {
        $type = $this->getType($label);
        return filter_input($type, $label, $filter, $options);
    }

}
