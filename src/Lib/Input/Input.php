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
 * Interface d'abstraction d'accès aux données en provenance du client
 * 
 * Les données input correspondent à `$_GET`, `$_POST` ou `$_FILES`. Sont écartées
 * les entêtes http et les données modifiables par le programme et de stocakge
 * telles que les sessions et les cookies. 
 * 
 * Ce choix se justifie par l'utilisation généralement faite de ces données 
 * par les applications. Les données de type get ou post et l'upload de fichier
 * correspondant à une manipulation CRUD des données, les autres étant plus
 * proches d'options de configuration... 
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-17
 */
class Input
{

    /**
     * Permet de récupérer une valeur spécifique sans se préocupper du type
     * de données concerné. 
     * 
     * La première occurence rencontrée est retournée en
     * testant dans l'ordre : **get**, **post** puis **fichiers**.
     * 
     * @param string $label
     * @return mixed|\Shrew\Mazzy\Lib\Input\File\FileObject
     */
    public static function get($label)
    {
        if (Request::getInstance()->exists($label)) {
            return Request::getInstance()->get($label);
        } else {
            return File\Upload::getInstance()->get($label);
        }
    }

    /**
     * Permet de récupérer l'instance d'accès aux données de type *get* et *post*
     * 
     * @return \Shrew\Mazzy\Lib\Input\Request
     */
    public static function getRequests()
    {
        return Request::getInstance();
    }


    /**
     * Permet de récupérer une donnée en provenance de **get** ou **post**
     * 
     * @param string $label
     * @param integer $filter L'ID du (filtre)[http://www.php.net/manual/fr/filter.filters.php] à appliquer.
     * @param mixed $options Tableau associatif d'options ou des drapeaux
     * @return mixed
     */
    public static function getRequest($label, $filter = FILTER_DEFAULT, $options = null)
    {
        return Request::getInstance()->get($label, $filter, $options);
    }
    
    /**
     * Permet de récupérer l'instance d'accès aux fichiers téléchargés
     * 
     * @return \Shrew\Mazzy\Lib\Input\File\Upload
     */
    public static function getFiles()
    {
        return File\Upload::getInstance();
    }

    /**
     * Permet de récupérer un fichier spécifique
     * 
     * @param string $label
     * @return \Shrew\Mazzy\Lib\Input\File\FileObject
     */
    public static function getFile($label)
    {
        return File\Upload::getInstance()->get($label);
    }

}
