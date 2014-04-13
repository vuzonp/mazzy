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

namespace Shrew\Mazzy\Lib\Core;

/**
 * Collection de données sous la forme *clé => valeur*
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-13
 */
class Collection implements \Countable, \Iterator, \Serializable
{

    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data Tableau a convertir en collection
     */
    public function __construct(Array $data = array())
    {
        foreach ($data as $label => $value) {
            $this->set($label, $value);
        }
    }

    /**
     * Ajouter une valeur a la collection
     * 
     * @param string $label
     * @param mixed $value
     */
    public function set($label, $value)
    {
        if (is_array($value)) {
            $value = new self($value);
        }
        $this->data[$label] = $value;
    }

    /**
     * Recuperer une valeur de la collection
     * 
     * @param string $label
     * @return mixed|null
     */
    public function get($label)
    {
        if (isset($this->data[$label])) {
            return $this->data[$label];
        } else {
            return null;
        }
    }

    /**
     * Verifie si une entree est presente au sein de la collection
     * 
     * @param string $label
     * @return boolean
     */
    public function exists($label)
    {
        return (isset($this->data[$label]));
    }
    
    /**
     * Recupere la liste des labels de la collection
     * 
     * @return array
     */
    public function getLabels()
    {
        return array_keys($this->data);
    }

    /**
     * Recherche la presence d'une valeur au sein de la collection
     * 
     * @param string $label
     * @return boolean
     */
    public function search($label)
    {
        return (in_array($label, $this->data));
    }

    /**
     * Supprime une entree
     * 
     * @param string $label
     */
    public function delete($label)
    {
        if (isset($this->data[$label])) {
            $this->data[$label] = null;
            unset($this->data[$label]);
        }
    }
    
    /**
     * Convertit la collection en tableau
     * 
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    
    // Méthodes magiques
    //--------------------------------------------------------------------------
    
    public function __set($label, $value)
    {
        return $this->set($label, $value);
    }

    public function __get($label)
    {
        return $this->get($label);
    }

    public function __invoke($label)
    {
        return $this->get($label);
    }

    public function __isset($label)
    {
        return $this->exists($label);
    }

    public function __unset($label)
    {
        $this->delete($label);
    }

    public function __toString()
    {
        return json_encode($this->data);
    }

    public function __clone()
    {
        return new self($this->data);
    }

    // Countable
    //-------------------------------------------------------------------------

    public function count()
    {
        return sizeof($this->data);
    }

    // Iterator
    //-------------------------------------------------------------------------

    public function rewind()
    {
        return reset($this->data);
    }

    public function current()
    {
        return current($this->data);
    }

    public function key()
    {
        return key($this->data);
    }

    public function next()
    {
        return next($this->data);
    }

    public function valid()
    {
        return (key($this->data) !== null);
    }

    // Serializable
    //-------------------------------------------------------------------------

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($data)
    {
        $this->data = unserialize($data);
    }

}
