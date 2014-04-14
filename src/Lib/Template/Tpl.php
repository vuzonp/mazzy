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

namespace Shrew\Mazzy\Lib\Template;

/**
 * Helpers de formattages des variables de template
 * 
 * Classe statique offrant différentes actions de formattage des données passées
 * en template. Par défaut, seules les formattages HTML sont disponibles. Pour
 * plus de possibilités, il existe la classe TplFull. Il est aussi possible
 * d'étendre cette classe selon les projets...
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-14
 */
class Tpl
{

    /**
     * Charset du site
     */
    const CHARSET = "utf-8";

    use Mixin\Html;

    /**
     * @var string 
     */
    private static $lang;

    /**
     * Valeurs globales à destination des vues
     * @var array 
     */
    private static $globals;

    /**
     * Ajouter une variable globale à destination des vues
     * 
     * @param string $name Identifiant de la variable
     * @param mixed $value
     */
    final public static function setGlobal($name, $value)
    {
        self::$globals[$name] = $value;
    }

    /**
     * Récupérer le charset du site
     * 
     * @return string
     */
    final public static function getCharset()
    {
        return self::CHARSET;
    }

    /**
     * Permet de récupérer la langue utilisée pour la réponse
     * 
     * @return string
     */
    final public static function getLang()
    {
        if (self::$lang === null) {
            self::$lang = \Locale::getPrimaryLanguage(\Locale::getDefault());
        }
        return self::$lang;
    }

    /**
     * Récupère une variable globale
     * 
     * @param string $label Identifiant de la variable
     * @return mixed|null
     */
    final public static function getGlobal($label)
    {
        if (isset(self::$globals[$label])) {
            return self::$globals[$label];
        } else {
            return null;
        }
    }

    /**
     * Offre la possibilité de traiter les variables globales par getters
     */
    public static function __callStatic($name, $arguments)
    {
        $global = str_replace("get", "", $name);
        if ($global !== $name) {
            return self::getGlobal(lcfirst($global));
        }
    }

}
