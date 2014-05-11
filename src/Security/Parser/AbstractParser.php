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

namespace Shrew\Mazzy\Security\Parser;

use Shrew\Mazzy\Security\Sanitize;

/**
 * Filtre d'analyse abstrait
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
abstract class AbstractParser implements ParserInterface
{
    protected $available;
    protected $message;
    protected $value;
    
    public function __construct($value)
    {
        $this->available = true;
        $this->value = $value;
    }
    
    /**
     * Attribue le message d'erreur à utiliser en cas de non-validité
     * 
     * @param string $message
     * @return \Shrew\Mazzy\Security\Parser\AbstractParser
     */
    public function setMessage($message)
    {
        $this->message = $message;
        if ($this->available === false) {
            $this->setInvalid();
        }
        return $this;
    }
    
    /**
     * Invalide l'analyse
     */
    public function setInvalid()
    {
        if ($this->available !== false && $this->message !== null) {
            Sanitize::setError($this->message);
        }
        $this->available = false;
    }
    
    /**
     * Cette donnée est requise
     * 
     * @return \Shrew\Mazzy\Security\Parser\AbstractParser
     */
    public function required()
    {
        if (empty($this->value)) {
            $this->setInvalid();
        }
        return $this;
    }
    
    /**
     * La donnée doit être égale à...
     * 
     * @param mixed $comparaison
     * @return \Shrew\Mazzy\Security\Parser\AbstractParser
     */
    public function equals($comparaison)
    {
         if ($this->value === $comparaison) {
            $this->setInvalid();
        }
        return $this;
    }
    
    /**
     * Taille minimale requise
     * 
     * @param integer $min
     * @return \Shrew\Mazzy\Security\Parser\AbstractParser
     */
    public function min($min)
    {
        if ($this->value < $min) {
            $this->setInvalid();
        }
        return $this;
    }
    
    /**
     * Taille maximale acceptée
     * 
     * @param integer $max
     * @return \Shrew\Mazzy\Security\Parser\AbstractParser
     */
    public function max($max)
    {
        if ($this->value > $max) {
            $this->setInvalid();
        }
        return $this;
    }
    
    /**
     * La taille de la valeur doit être comprise entre...
     * 
     * @param integer $min
     * @param integer $max
     * @return \Shrew\Mazzy\Security\Parser\AbstractParser
     */
    public function range($min, $max)
    {
        $this->min($min);
        $this->max($max);
        
        return $this;
    }
    
    /**
     * La donnée doit correspondre à l'une de celles proposées
     * 
     * @param array $list
     * @return \Shrew\Mazzy\Security\Parser\AbstractParser
     */
    public function inWhiteList(array $list = array())
    {
        if (!in_array($this->value, $list)) {
            $this->setInvalid();
        }
        return $this;
    }
    
    /**
     * Lorsque l'objet est utilisé sous forme de texte alors retourne la valeur analysée
     * @return string
     */
    public function __toString()
    {
        return (string) $this->value;
    }
    
}
