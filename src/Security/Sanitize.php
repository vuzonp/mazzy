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
 * Vérification et validation des données suspectes
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Sanitize
{

    /**
     * Pile de stockage des erreurs relevées
     * @var \SplStack
     */
    protected static $errors;

    /**
     * Récupération de la pile des erreurs
     * 
     * @return \SplStack
     */
    private static function getErrorStack()
    {
        if (self::$errors === null) {
            self::$errors = new \SplStack();
        }
        return self::$errors;
    }

    /**
     * Ajoute une erreur à la pile
     * 
     * @param string $message
     */
    public static function setError($message)
    {
        self::getErrorStack()->push($message);
    }

    /**
     * Vérifie si des erreurs ont été rencontrées lors des tests de validité
     * 
     * @return boolean
     */
    public static function hasErrors()
    {
        return (self::getErrorStack()->count() > 0);
    }

    /**
     * Récupère les erreurs relevées lors des tests de validité
     * 
     * @return \SplStack
     */
    public static function getErrors()
    {
        return self::getErrorStack();
    }

    /**
     * Exécute une analyse numérique pré-filtrée
     * 
     * @param float|integer $num
     * @param integer $filter
     * @return \Shrew\Mazzy\Security\Parser\Number
     */
    private static function filterNumber($num, $filter = FILTER_DEFAULT)
    {
        $parser = new Parser\Number($num);
        if (empty($num) === false && filter_var($num, $filter) === false) {
            $parser->setInvalid();
        }
        return $parser;
    }

    /**
     * Exécute une analyse textuelle pré-filtrée
     * 
     * @param string $str
     * @param integer $filter
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    private static function filterText($str, $filter = FILTER_DEFAULT)
    {
        $parser = new Parser\Text($str);
        if (empty($str) === false && filter_var($str, $filter) === false) {
            $parser->setInvalid();
        }
        return $parser;
    }

    /**
     * Permet de vérifier une valeur de type booleén
     * 
     * @param boolean|string $data
     * @return \Shrew\Mazzy\Security\Parser\Bool
     */
    public static function bool($data)
    {
        return new Parser\Bool(filter_var($data, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Permet de vérifier une valeur de type date
     * 
     * @param string $str
     * @return \Shrew\Mazzy\Security\Parser\DateTime
     */
    public static function datetime($str)
    {
        return new Parser\DateTime($str);
    }

    /**
     * Permet de vérifier une valeur numérique
     * 
     * @param float|integer $num
     * @return \Shrew\Mazzy\Security\Parser\Number
     */
    public static function number($num)
    {
        return self::filterNumber($num, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Permet de vérifier une valeur numérique flottante
     * 
     * @param float|integer $num
     * @return \Shrew\Mazzy\Security\Parser\Number
     */
    public static function float($num)
    {
        return self::filterNumber($num, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Permet de vérifier une valeur numérique de type entier
     * 
     * @param integer $num
     * @return \Shrew\Mazzy\Security\Parser\Number
     */
    public static function int($num)
    {
        return self::filterNumber($num, FILTER_VALIDATE_INT);
    }

    /**
     * Permet de vérifier une valeur de type texte
     * 
     * @param string $str
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public static function text($str)
    {
        return new Parser\Text($str);
    }

    /**
     * Permet de vérifier une valeur de type texte correspondant à un motif spécifique
     * 
     * @param string $str
     * @param string $pattern
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public static function pattern($str, $pattern)
    {
        $parser = new Parser\Text($str);
        if (!empty($str) && preg_match($pattern, $str)) {
            $parser->setInvalid();
        }
        return $parser;
    }

    /**
     * Permet de vérifier une url
     * 
     * @param string $str
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public static function url($str)
    {
        return self::filterText($str, FILTER_VALIDATE_URL);
    }

    /**
     * Permet de vérifier un numéro de téléphone
     * 
     * @param string $str
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public static function tel($str)
    {
        $pattern = "/^\+?[\d\s\-\.]{4,}$/";
        return self::pattern($str, $pattern);
    }

    /**
     * Permet de vérifier une addresse email
     * 
     * @param string $str
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public static function email($str)
    {
        return self::filterText($str, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Permet de vérifier une couleur (héxadécimal)
     * 
     * @param string $str
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public static function color($str)
    {
        return self::pattern($str, "/^#(0-9){,6}$/i");
    }

}
