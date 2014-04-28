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

namespace Shrew\Mazzy\Template;

use Shrew\Mazzy\Config\Config;
use Shrew\Mazzy\Config\ConfigAwareInterface;
use Psr\Log\LoggerAwareInterface;


/**
 * Moteur de templates
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Template implements ConfigAwareInterface, LoggerAwareInterface
{

    use \Shrew\Mazzy\Config\ConfigAwareTrait;
    use \Psr\Log\LoggerAwareTrait;

    /**
     * @var string 
     */
    protected $filename;

    /**
     * @var array 
     */
    protected $data;

    /**
     * Pointeur vers `Tpl::setGlobal`
     * 
     * @param string $label
     * @param mixed $value
     */
    final public static function setGlobal($label, $value)
    {
        Tpl::setGlobal($label, $value);
    }
    
    public function __construct(Config $config = null)
    {
        if ($config !== null) {
            $this->settings = $config;
        } else {
            $this->settings = new Config();
        }
    }

    /**
     * @param string $name Nom du template
     * @param string|null $theme Thème à utiliser
     * @throws \Shrew\Mazzy\Template\TemplateException Lorsque le fichier de template n'existe pas
     */
    public function prepare($name, $theme = null)
    {
        $directory = $this->settings->get("view.directory", __DIR__ . "/../../templates/");
        $extension = $this->settings->get("view.extension", ".php");
        $defaultTheme = $this->settings->get("view.default", "default");

        if ($theme === null) {
            $theme = $this->settings->get("view.main", $defaultTheme);
        }

        $filename = "$directory/$theme/{$name}{$extension}";

        if (!file_exists($filename)) {
            $filename = "$directory/$defaultTheme/{$name}{$extension}";
            if (!file_exists($filename)) {
                throw new TemplateException("Le fichier de template *$filename* n'existe pas", 500);
            }
        }

        $this->filename = $filename;
        $this->data = array();
    }

    /**
     * Ajoute une nouvelle variable de template
     * 
     * @param string $label
     * @param mixed $value
     */
    public function set($label, $value)
    {
        if (isset($this->data[$label]) && is_array($value) && is_array($this->data[$label])) {
            $this->data[$label] = array_replace_recursive($value, $this->data[$label]);
        } else {
            $this->data[$label] = $value;
        }
    }

    /**
     * Récupère la signature des données de template
     * 
     * @return string
     */
    public function getHashData()
    {
        return md5(serialize($this->data));
    }

    /**
     * Récupère la signature du fichier de template
     * 
     * @return string
     */
    public function getHashFile()
    {
        return md5_file($this->filename);
    }

    /**
     * Supprime une variable de template
     * 
     * @param string $label
     */
    public function delete($label)
    {
        if (isset($this->data[$label])) {
            $this->data[$label] = null;
            unset($this->data[$label]);
        }
    }

    public function __set($label, $value)
    {
        $this->set($label, $value);
    }

    /**
     * Préparation des données pour être manipulées par le template
     */
    private function prepareData()
    {
        foreach ($this->data as $label => $value) {
            if (is_array($value)) {
                $this->data[$label] = new TemplateObject($value);
            }
        }
    }

    /**
     * Génère le rendu de la vue
     * 
     * @return string
     */
    public function generate()
    {
        $this->prepareData();
        extract($this->data, EXTR_PREFIX_SAME, "tpl_");

        try {
            ob_start("ob_iconv_handler");
            include $this->filename;
            $output = ob_get_contents();
            ob_end_clean();
        }
        catch(\Exception $e) {
            ob_end_clean();
            throw new TemplateException($e->getMessage(), $e->getCode(), $e);
        }
        
        

        // Passage du garbage collector
        gc_collect_cycles();

        return $output;
    }

}
