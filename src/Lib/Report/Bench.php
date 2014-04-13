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
 * Permet d'effectuer des mesures de performance sur partie ou totalité du code
 *  
 * @author      Thomas Girard <thomas@shrewstudio.com>
 * @license     http://opensource.org/licenses/MIT
 * @version     v0.1.0-alpha2
 * @since       2014-04-12
 */
class Bench
{

    /**
     * Calculer la consommation mémoire ou non ?
     * @var boolean 
     */
    private $useMemory;

    /**
     * Valeur temporelle du bench
     * @var float 
     */
    private $time;

    /**
     * Valeur de la consommation mémoire du bench
     * @var float 
     */
    private $memory;

    /**
     * Retourne la consommation de mémoire vive maximale 
     * rencontrée pendant l'exécution du script
     * @return float Mémoire vive consommée en Mio
     */
    public static function getMaxMemory()
    {
        return round((memory_get_peak_usage()) / 1048576, 2);
    }

    public function __construct()
    {
        $this->useMemory = false;
        $this->start();
    }

    /**
     * Active la prise en charge de la mémoire vive lors du benchmark
     * @return \Shrew\Mazzy\Lib\Report\Bench
     */
    public function enableMemory()
    {
        $this->useMemory = true;
        return $this;
    }

    /**
     * Démarre le benchmark
     */
    public function start()
    {
        $this->time = microtime(true);
        if ($this->useMemory === true) {
            $this->memory = memory_get_usage();
        }
    }

    /**
     * Arrête le benchmark et en génère les résultats
     * @return \Shrew\Mazzy\Lib\Report\Bench
     */
    public function stop()
    {
        $this->time = round(microtime(true) - $this->time, 3);
        if ($this->useMemory === true) {
            $this->memory = round((memory_get_usage() - $this->memory) / 1048576, 2);
        }
        return $this;
    }

    /**
     * Retourne le temps d'éxécution du script
     * @return float Temps d'éxécution en secondes, arrondi au millième de secondes 
     */
    public function getTimer()
    {
        return $this->time;
    }

    /**
     * Retourne la mémoire vive consommée durant le benchmark
     * @return float Mémoire vive consommée en Mio
     */
    public function getMemory()
    {
        return $this->memory;
    }

}
