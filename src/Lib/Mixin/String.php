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

namespace Shrew\Mazzy\Lib\Helper;

/**
 * Manipulation de chaînes de caractères
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
trait String
{

    /**
     * Tronque une chaîne de caractères en conservant les mots en entier
     * 
     * @param string $str Chaîne à tronquer
     * @param integer $maxLength Nombre de caractères maximum acceptés
     * @return string
     */
    public static function truncByWords($str, $maxLength = 170)
    {
        return (mb_strlen($str, APP_CHARSET) > $maxLength) 
                ? mb_substr(mb_substr($str, 0, $maxLength), 0, mb_strrpos(mb_substr($str, 0, $maxLength), " "), "UTF-8") 
                : $str;
    }

    /**
     * Récupère les mots clés d'une chaîne de caractères.
     * 
     * La détection des mots clés est uniquement basée sur la taille des mots disponibles.
     * Effectivement, les mots les plus rares étant les plus longs, il semble adéquat de
     * mettre ceux-ci en valeur. Cette fonctionnalité est donc limité et ne sera réellement
     * efficace que sur des titres ou des phrases mais pas sur des paragraphes entiers où
     * la répétition des mots serait un critère à ajouter.
     * 
     * @param string $str Phrase à analyser
     * @param integer $maxWords Nombre de mots à récupérer
     * @return array Tableau contenant les mots clés triés dans l'ordre 
     *               d'apparition originel des mots au sein de la phrase fournie en paramètre.
     */
    public static function getKeywords($str, $maxWords)
    {
        // Découpe la chaîne en mots puis supprime les doublons
        $words = explode(' ', $str);
        $words = array_unique($words);
        $length = count($words);

        if ($length > $maxWords) {
            $keywords = array();
            foreach ($words as $word) {
                $keywords[$word] = mb_strlen($str, APP_CHARSET);
            }

            // Trie le tableau par tailles des mots
            arsort($keywords);

            // Ne conserve que les plus grandes entrées pour atteindre $maxwords
            $keywords = array_slice($keywords, 0, $maxWords);

            // remise en ordre des mots clés
            $words = array_intersect($words, array_keys($keywords));
        }

        return $words;
    }

    /**
     * Générateur d'urls pour les pages du site.
     * 
     * Ne conserve que les mots les plus longs d'une phrase 
     * puis les formatte en ASCII reliés par des tirets
     * Inspired from : http://snipplr.com/view/71861/slugify/
     * 
     * @param string $str Chaîne à transformer en url
     * @param integer $maxWords Nombre de mots à conserver dans l'url
     * @return string
     */
    public static function slugify($str, $maxWords = 5)
    {

        //var_dump($str);
        // Nettoyage de la chaîne
        $str = preg_replace("~[^\pL\pN]+~u", " ", $str);
        $str = trim($str);

        $words = self::getKeywords($str, $maxWords);

        // Création de la chaîne d'url
        $slug = mb_strtolower(implode("-", $words), APP_CHARSET);
        $slug = @iconv(APP_CHARSET, "us-ascii//TRANSLIT", $slug);

        if (empty($slug)) {
            return "n-a";
        } else {
            return rawurlencode($slug);
        }
    }

}
