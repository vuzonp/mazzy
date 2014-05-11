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

namespace Shrew\Mazzy\Security;

/**
 * Formattage des données pour différentes utilisation
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Format
{
    /**
     * Décode une chaîne html
     * 
     * @param type $str
     * @return type
     */
    public static function fromHtml($str)
    {
        return htmlspecialchars_decode($str,
                ENT_QUOTES | 
                ENT_SUBSTITUTE | 
                ENT_HTML5, 
                iconv_get_encoding("output_encoding")
        );
    }
    
    /**
     * Encode une chaîne de texte en html
     * 
     * @param string $str
     * @return string
     */
    public static function toHtml($str)
    {
        return htmlspecialchars($str, 
                ENT_QUOTES | 
                ENT_SUBSTITUTE | 
                ENT_HTML5, 
                iconv_get_encoding("output_encoding")
        );
    }
    
    /**
     * Décode une chaîne de texte CSS
     * 
     * @todo Écrire le codec
     */
    public static function fromCss($str)
    {
        return $str;
    }
    
    /**
     * Encode une chaîne de texte à destination d'une utilisation au sein d'une
     * feuille de style CSS
     * 
     * @param type $str
     * @return type
     */
    public static function toCss($str)
    {
        return $str;
    }
    
    /**
     * Décode un ensemble de données de type JSON
     * 
     * @param type $data
     * @return type
     */
    public static function fromEcmaScript($data)
    {
        return json_decode($data);
    }
    
    /**
     * Alias de fromEcmaScript
     * @param type $data
     */
    public static function fromJson($data)
    {
        return self::fromEcmaScript($data);
    }
    
    /**
     * 
     * @param type $data
     * @return type
     */
    public static function toEcmaScript($data)
    {
        return json_encode($data, 
                JSON_HEX_TAG |
                JSON_HEX_AMP |
                JSON_HEX_APOS |
                JSON_HEX_QUOT |
                JSON_NUMERIC_CHECK |
                JSON_BIGINT_AS_STRING |
                JSON_UNESCAPED_UNICODE |
                JSON_FORCE_OBJECT
        );
    }
    
    /**
     * 
     */
    public static function toJson()
    {
        
    }
    
    public static function fromB64($data)
    {
        return base64_decode($data);
    }
    
    public static function toB64($data)
    {
        return base64_encode($data);
    }
    
    public static function fromQP($str)
    {
        return quoted_printable_decode($str);
    }
    
    public static function toQP($str)
    {
        return quoted_printable_encode($str);
    }
    
    public static function fromURL($url)
    {
        return rawurldecode($url);
    }
    
    public static function toURL($url)
    {
        return rawurlencode($url);
    }
    
    
    
}
