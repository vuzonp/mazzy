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

namespace Shrew\Mazzy\Lib\Input\File;

/**
 * Conteneur des fichiers envoyés à l'application
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-16
 */
class Upload extends \Shrew\Mazzy\Lib\Input\InputContainer implements \Countable, \IteratorAggregate
{

    private $counter;
    private $files;

    protected function initialize()
    {
        parent::initialize();

        $this->counter = 0;
        $this->loadFiles();
    }

    /**
     * Réordonne le tableau $_FILES par fichiers 
     * et lance l'instanciation de ceux-ci
     * 
     * @return array
     */
    private function loadFiles()
    {
        $files = array();
        // Tri de $_FILES pour une manipulation plus aisée
        foreach ($_FILES as $name => $props) {
            foreach ($props as $prop => $value) {
                if (is_array($value)) {
                    $this->normalize($files[$name], $value, $prop);
                } else {
                    $files[$name][$prop] = $value;
                }
            }
        }
        return $this->instanciateFiles($files);
    }

    /**
     * Méthode récursive permettant le tri du tableau $_FILES
     * 
     * http://www.php.net/manual/fr/features.file-upload.post-method.php#111561
     * 
     * @param string $files Tableau recevant les modifications
     * @param string $values Valeurs du tableau
     * @param string $prop Propriété du tableau
     */
    private function normalize(Array &$files, $values, $prop)
    {
        $method = __METHOD__;

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $this->$method($files[$key], $value, $prop);
            } else {
                $files[$key][$prop] = $value;
            }
        }
    }

    /**
     * Instancie les fichiers en objets FileObject
     * 
     * @param array $files
     * @uses \Shrew\Mazzy\Lib\Input\File\FileObject Représentation objet des fichiers uploadés
     */
    private function instanciateFiles(Array $files = array())
    {
        $labels = array_keys($files);
        $values = array_values($files);
        $this->counter = sizeof($files);

        for ($i = 0; $i < $this->counter; $i++) {
            $this->files[$labels[$i]] = new FileObject($labels[$i], $values[$i]);
        }
    }

    /**
     * Vérifie l'existence d'un fichier
     * 
     * @param string $label
     * @return boolean
     */
    final public function exists($label)
    {
        return isset($this->files[$label]);
    }
    
    /**
     * Retourne le nombre de fichiers uploadés lors de la requête
     * 
     * @return integer
     */
    final public function count()
    {
        return $this->counter;
    }

    /**
     * Récupère un fichier
     * 
     * @param string $label
     * @return \Shrew\Mazzy\Lib\Input\File\FileObject
     */
    final public function get($label)
    {
        return $this->files[$label];
    }

    /**
     * Exporte la collection de fichiers pour permettre l'itération
     * 
     * @return \Shrew\Mazzy\Lib\Input\File\ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this);
    }
}
