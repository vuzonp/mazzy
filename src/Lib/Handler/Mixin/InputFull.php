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

namespace Shrew\Mazzy\Lib\Handler\Mixin;

use Shrew\Mazzy\Lib\Input\Input;


/**
 * Complète le système d'input par défaut des contrôleurs
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
trait InputFull
{
    
    use Session;

    /**
     * Récupère sans distinction de type une donnée d'input
     * 
     * @param string $label
     * @return mixed|Shrew\Mazzy\Lib\Input\File\FileObject
     */
    protected function get($label)
    {
        return Input::get($label);
    }

    /**
     * Récupération et filtrage d'une donnée de type input (hors fichiers)
     * 
     * @param string $label
     * @param integer $filter L'ID du filtre à appliquer. (voir page du manuel)[http://fr2.php.net/manual/fr/filter.filters.php]. 
     * @param mixed $options Tableau associatif d'options ou des drapeaux
     * @return mixed
     */
    protected function getInput($label, $filter = null, $options = null)
    {
        return Input::getRequest($label, $filter, $options);
    }

    /**
     * Récupération d'un fichier d'input
     * 
     * @param string $label
     * @return \Shrew\Mazzy\Lib\Input\File\FileObject
     */
    protected function getUpload($label)
    {
        return Input::getFile($label);
    }

    /**
     * Récupération de l'ensemble des fichiers d'input
     * 
     * @return \Shrew\Mazzy\Lib\Input\File\Upload
     */
    protected function getUploads()
    {
        return Input::getFiles();
    }

}
