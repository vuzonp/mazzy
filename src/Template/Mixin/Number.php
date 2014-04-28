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

namespace Shrew\Mazzy\Template\Mixin;

/**
 * Formattage et internationalisation des nombres dans les vues
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
trait Number
{

    private static $fmtDecimal;
    private static $fmtSpellout;
    private static $fmtCurrency;
    private static $fmtPercent;

    /**
     * Formatte un nombre décimal (i18n)
     * 
     * @param integer $n
     * @return string
     */
    public static function decimal($n)
    {
        if (self::$fmtDecimal === null) {
            self::$fmtDecimal = NumberFormatter::create(null, \NumberFormatter::DECIMAL);
        }
        return self::$fmtDecimal->format($n);
    }

    /**
     * Formatte un nombre à la forme littérale (i18n)
     * 
     * @param integer $n
     * @return string
     */
    public static function numSpellout($n)
    {
        if (self::$fmtSpellout === null) {
            self::$fmtSpellout = NumberFormatter::create(null, \NumberFormatter::SPELLOUT);
        }
        return self::$fmtSpellout->format($n);
    }

    /**
     * Formatte un nombre sous forme de valeur monétaire (i18n)
     * 
     * @param integer $n
     * @return string
     */
    public static function currency($n)
    {
        if (self::$fmtCurrency === null) {
            self::$fmtCurrency = NumberFormatter::create(null, \NumberFormatter::CURRENCY);
        }
        return self::$fmtCurrency->format($n);
    }

    /**
     * Formatte un nombre sous forme de pourcentage (i18n)
     * 
     * @param integer $n
     * @return string
     */
    public static function percent($n)
    {
        if (self::$fmtPercent === null) {
            self::$fmtPercent = NumberFormatter::create(null, \NumberFormatter::PERCENT);
        }
        return self::$fmtPercent->format($n);
    }
    
}
