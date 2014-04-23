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

namespace Shrew\Mazzy\Lib\Report;


/**
 * Gestionnaire d'historique des événements
 * 
 * Ce gestionnaire de log ne respecte pas les standarts PSR-3, mais il
 * fait son boulot.
 *
 * Les logs sont sauvegardés au format `.csv` pour faciliter leur exploitation
 * par des webmasters ne disposant pas de syslog.
 *  
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Log
{

    /**
     * Système inutilisable
     */
    const EMERGENCY = 0b10000000;

    /**
     * Une intervention immédiate est nécessaire
     */
    const ALERT = 0b01000000;

    /**
     * Erreur critique pour le système
     */
    const CRITICAL = 0b00100000;

    /**
     * Erreur de fonctionnement
     */
    const ERROR = 0b00010000;

    /**
     * Avertissement
     */
    const WARNING = 0b00001000;

    /**
     * Événement normal méritant d'être signalé
     */
    const NOTICE = 0b00000100;

    /**
     * Pour information seulement
     */
    const INFO = 0b00000010;

    /**
     * Message de mise au point pour débogage du site
     */
    const DEBUG = 0b00000001;

    /**
     * @var integer 
     */
    private static $directory;

    /**
     * @var integer 
     */
    private static $filename;

    /**
     * @var integer 
     */
    private static $lvlReports = 0;

    /**
     * @var array 
     */
    private static $logs = array();

    /**
     * Chemin de sauvegarde des logs (répertoires)
     * 
     * Les logs sont sauvegardés au format csv avec des fichiers mensuels séparés
     * (Nettement plus simple que le format syslog pour organiser les infos
     * lors de leur consultation : trie par colonnes + formattage des dates)
     * 
     * @param string $dir Repertoire de sauvegarde des logs
     */
    public static function setPath($dir)
    {
        self::$directory = $dir;
        self::$filename = "$dir/log-" . date("Y-m") . ".csv";
    }

    /**
     * Attribue le niveau de rapport à appliquer
     * 
     * @param integer $lvl Constante de \Shrew\Mazzy\Lib\Report\Log
     */
    public static function setLeveLReports($lvl)
    {
        self::$lvlReports = (int) $lvl;
    }

    /**
     * @return string
     */
    public static function getDirectory()
    {
        return self::$directory;
    }

    /**
     * Permet de récupérer les logs actuels
     * 
     * @return array|false Le tableau contenant les logs ou bien faux si vide
     */
    public static function getTrace()
    {
        return (empty(self::$logs)) ? self::$logs : false;
    }

    /**
     * Récupère les derniers logs ne dépendant pas de la requête en cours
     * 
     * @param integer $maxRows Nombre de lignes à récupérer
     */
    public static function findMany($maxRows = 10)
    {
        $logs = array();

        if (file_exists(self::$filename)) {
            $maxRows;
            $keys = array("dateCreated", "level", "message", "file", "line");

            $file = new \SplFileObject(self::$filename, "r");
            $file->setFlags(\SplFileObject::DROP_NEW_LINE);

            // Hack pas trop propre permettant de ne récupérer que les `$maxRows`
            // dernières lignes du fichier de logs... malheureusement c'est le 
            // plus efficace. On se place bien au delà de la dernière ligne, ce 
            // qui nous repositionne automatiquement à la dernière ligne.
            $max = intval($file->fstat()["size"] / 50);
            $file->seek($max);

            // On se place maintenant $maxRows lignes avant la fin
            $key = $file->key();
            if ($key > $maxRows) {
                $line = $file->key() - $maxRows;
                $file->seek($line);
            } else {
                $file->seek(0);
            }

            // On récupère les dernières lignes dans un tableau php
            while ($file->valid()) {
                $log = $file->fgetcsv();
                if (!empty($log[0])) {
                    $logs[] = array_combine($keys, $log);
                }
            }
        }

        return $logs;
    }

    /**
     * Récupère la liste complète des fichiers de logs existants
     * 
     * @return array
     */
    public static function listFiles($max = null)
    {
        $files = array();
        $filesRaw = scandir(self::$directory);

        foreach ($filesRaw as $file) {
            if ($file !== "." && $file !== "..") {
                $files[] = $file;
            }
        }

        if (is_int($max) && count($files) > $max) {
            $files = array_slice($files, 0, $max);
        }

        return $files;
    }

    /**
     * Méthode générique pour ajouter un log
     * 
     * @param integer $level Type de log
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function set($level, $message, $file = null, $line = null)
    {
        if (is_int($level) && $level >= self::$lvlReports) {
            $date = date("Y-m-d H:i:s");
            $message = strip_tags($message);
            $levelStr = "";

            if ($level === self::DEBUG) {
                $levelStr = "DEBUG";
            } elseif ($level === self::INFO) {
                $levelStr = "DEBUG";
            } elseif ($level === self::NOTICE) {
                $levelStr = "DEBUG";
            } elseif ($level === self::WARNING) {
                $levelStr = "DEBUG";
            } elseif ($level === self::ERROR) {
                $levelStr = "DEBUG";
            } elseif ($level === self::CRITICAL) {
                $levelStr = "DEBUG";
            } elseif ($level === self::ALERT) {
                $levelStr = "DEBUG";
            } elseif ($level === self::EMERGENCY) {
                $levelStr = "DEBUG";
            } else {
                $levelStr = "UNKNOWN";
            }

            self::$logs[] = array($date, $levelStr, $message, $file, $line);
        }
    }

    /**
     * Système inutilisable
     * 
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function emergency($message, $file = null, $line = null)
    {
        //trigger_error($message, E_USER_ERROR);
        self::set(self::EMERGENCY, $message, $file, $line);
    }

    /**
     * Une intervention immédiate est nécessaire
     * 
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function alert($message, $file = null, $line = null)
    {
        //trigger_error($message, E_USER_ERROR);
        self::set(self::ALERT, $message, $file, $line);
    }

    /**
     * Erreur critique pour le système
     * 
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function critical($message, $file = null, $line = null)
    {
        //trigger_error($message, E_USER_ERROR);
        self::set(self::CRITICAL, $message, $file, $line);
    }

    /**
     * Erreur de fonctionnement
     * 
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function error($message, $file = null, $line = null)
    {
        //trigger_error($message, E_USER_ERROR);
        self::set(self::ERROR, $message, $file, $line);
    }

    /**
     * Avertissement
     * 
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function warning($message, $file = null, $line = null)
    {
        //trigger_error($message, E_USER_WARNING);
        self::set(self::WARNING, $message, $file, $line);
    }

    /**
     * Événement normal méritant d'être signalé
     * 
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function notice($message, $file = null, $line = null)
    {
        //trigger_error($message, E_USER_NOTICE);
        self::set(self::NOTICE, $message, $file, $line);
    }

    /**
     * Pour information seulement
     * 
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function info($message, $file = null, $line = null)
    {
        self::set(self::INFO, $message, $file, $line);
    }

    /**
     * Message de mise au point pour débogage du site
     * 
     * @param string $message Message de description
     * @param string $file Fichier d'où provient le log (optionnel)
     * @param integer $line Ligne d'où provient le log (optionnel)
     */
    public static function debug($message, $file = null, $line = null)
    {
        self::set(self::DEBUG, $message, $file, $line);
    }

    /**
     * Sauvegarde du log 
     * 
     * @return boolean
     */
    public static function save()
    {
        if (!empty(self::$logs)) {
            if (!file_exists(self::$filename) && is_writable(dirname(self::$filename))) {
                touch(self::$filename);
                chmod(self::$filename, 0666);
            }

            $file = new \SplFileObject(self::$filename, "a+");
            foreach (self::$logs as $log) {
                $file->fputcsv($log);
            }
        }
        return true;
    }

}
