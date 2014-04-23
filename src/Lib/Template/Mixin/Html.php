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

namespace Shrew\Mazzy\Lib\Template\Mixin;

/**
 * Formattage des chaînes html dans les vues
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
trait Html
{

    private static $strMarkdownFormatter;
    private static $strHtmlCleaner;

    /**
     * Sécurise une chaîne de texte à afficher dans un page html en transformant
     * l'ensemble des caractères spéciaux en entités html. 
     * 
     * @param string $str Texte à sécuriser
     * @return string
     */
    public static function toText($str)
    {
        return htmlspecialchars(strip_tags($str), ENT_QUOTES | ENT_DISALLOWED | ENT_HTML5, "UTF-8");
    }

    /**
     * Récupère une variable de template en la convertissant sous forme ASCII
     * (suppression des accents et caractères > 128)
     * 
     * @param string $str Identifiant de la variable de template
     * @return string
     */
    public static function ascii($str)
    {
        $str = iconv("UTF-8", "us-ascii//TRANSLIT", $str);
        return self::text($str);
    }

    /**
     * Récupère une variable de template en acceptant du code html mais en
     * protégeant et en nettoyant préalablement celui-ci. Cette méthode
     * convertit par ailleurs le markdown en html. À utiliser essentiellement
     * pour les contenus rédigés par les rédacteurs identifiés.
     * 
     * @param string $str Identifiant de la variable de template
     * @return string
     */
    public static function toHtml($str)
    {
        // Initialisation du parseur de markdown
        if (self::$strMarkdownFormatter === null) {
            self::$strMarkdownFormatter = new \Parsedown();
        }

        // Initialisation de HTMLPurifier
        if (self::$strHtmlCleaner === null) {
            $config = \HTMLPurifier_Config::createDefault();
            $config->set("Cache.DefinitionImpl", null);
            $config->set("Core.Encoding", "UTF-8");
            $config->set("HTML.Doctype", "HTML 4.01 Transitional");
            self::$strHtmlCleaner = new \HTMLPurifier($config);
        }

        // transformation du markdown en html
        $html = self::$strMarkdownFormatter->parse($str);

        // nettoyage du code html
        return self::$strHtmlCleaner->purify($html);
    }

}
