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
 * Formattage et internationalisation des dates dans les vues
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
trait DateTime
{

    /**
     * Formatte une date au format désiré
     * 
     * @uses \Datetime
     * @param string $time Chaîne correspondant à un format de date valide
     * @param string $format Formattage de type `\Datetime`
     * @return string
     */
    public static function dateTime($time = "now", $format = "d/m/Y H:i:s")
    {
        $datetime = new \DateTime($time);
        return $datetime->format($format);
    }

    /**
     * Reformatte une date au format W3C
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string Valeur temporelle au format `Y-m-d\TH:i:sP`
     */
    public static function w3cDateTime($time = "now")
    {
        $datetime = new \DateTime($time);
        return $datetime->format(DATE_W3C);
    }

    /**
     * Formatte une date au format ICU désiré (i18n)
     * 
     * (plus d'infos)[http://www.icu-project.org/apiref/icu4c/classSimpleDateFormat.html#details]
     * 
     * @uses \IntlCalendar
     * @param string $time Chaîne correspondant à un format de date valide
     * @param string $format
     * @return string
     */
    public static function dateTimeICU($time = "now", $format = null)
    {
        $calendar = \IntlCalendar::fromDateTime($time);
        return \IntlDateFormatter::formatObject($calendar, $format);
    }

    /**
     * Formatte une date en renvoyant un maximum d'informations (i18n)
     * 
     * _Les méthodes de type _date_ ne traitent pas les heures, minutes et 
     * secondes contrairement aux méthodes de type _dateTime_ _
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string
     */
    public static function dateFull($time = "now")
    {
        return self::dateTimeICU($time, array(\IntlDateFormatter::FULL, \IntlDateFormatter::NONE,));
    }

    /**
     * Formatte une date en renvoyant de nombreuses informations (i18n)
     * 
     * _Les méthodes de type _date_ ne traitent pas les heures, minutes et 
     * secondes contrairement aux méthodes de type _dateTime_ _
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string
     */
    public static function dateLong($time = "now")
    {
        return self::dateTimeICU($time, array(\IntlDateFormatter::LONG, \IntlDateFormatter::NONE,));
    }

    /**
     * Formatte une date en renvoyant peu d'informations (i18n)
     * 
     * _Les méthodes de type _date_ ne traitent pas les heures, minutes et 
     * secondes contrairement aux méthodes de type _dateTime_ _
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string
     */
    public static function dateMedium($time = "now")
    {
        return self::dateTimeICU($time, array(\IntlDateFormatter::MEDIUM, \IntlDateFormatter::NONE));
    }

    /**
     * Formatte une date en renvoyant très peu d'informations (i18n)
     * 
     * _Les méthodes de type _date_ ne traitent pas les heures, minutes et 
     * secondes contrairement aux méthodes de type _dateTime_ _
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string
     */
    public static function dateShort($time = "now")
    {
        return self::dateTimeICU($time, array(\IntlDateFormatter::SHORT, \IntlDateFormatter::NONE));
    }

    /**
     * Formatte une date en renvoyant un maximum d'informations (i18n)
     * 
     * _Les méthodes de type _dateTime_ traitent les heures, minutes et 
     * secondes contrairement aux méthodes de type _date_ _
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string
     */
    public static function dateTimeFull($time = "now")
    {
        return self::dateTimeICU($time, array(\IntlDateFormatter::FULL, \IntlDateFormatter::MEDIUM));
    }

    /**
     * Formatte une date en renvoyant de nombreuses informations (i18n)
     * 
     * _Les méthodes de type _dateTime_ traitent les heures, minutes et 
     * secondes contrairement aux méthodes de type _date_ _
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string
     */
    public static function dateTimeLong($time = "now")
    {
        return self::dateTimeICU($time, array(\IntlDateFormatter::LONG, \IntlDateFormatter::MEDIUM));
    }

    /**
     * Formatte une date en renvoyant peu d'informations (i18n)
     * 
     * _Les méthodes de type _dateTime_ traitent les heures, minutes et 
     * secondes contrairement aux méthodes de type _date_ _
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string
     */
    public static function dateTimeMedium($time = "now")
    {
        return self::dateTimeICU($time, array(\IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM));
    }

    /**
     * Formatte une date en renvoyant très peu d'informations (i18n)
     * 
     * _Les méthodes de type _dateTime_ traitent les heures, minutes et 
     * secondes contrairement aux méthodes de type _date_ _
     * 
     * @param string $time Chaîne correspondant à un format de date valide
     * @return string
     */
    public static function dateTimeShort($time = "now")
    {
        return self::dateTimeICU($time, array(\IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT));
    }

}
