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
 * Conteneur abstrait des données *input*
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
abstract class InputContainer implements InputContainerInterface
{
    /**
     * Tableau d'instances singletonnes
     * @var array 
     */
    private static $instances;
    
    private function __clone() {}
    
    private function __wakeup() {}

    /**
     * Chargeur de la classe
     */
    public static function getInstance()
    {
        $c = get_called_class();
        if(!isset(self::$instances[$c])) {
            self::$instances[$c] = new $c;
        }
        return self::$instances[$c];
    }

    private function __construct()
    {
        $this->initialize();
    }
    
    /**
     * Initialisation de la classe
     */
    protected function initialize()
    {
        
    }

    /**
     * Récupération d'un élément d'input
     */
    abstract public function get($label);

    final public function __get($label)
    {
        return $this->get($label);
    }

}
