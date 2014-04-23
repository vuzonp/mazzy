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

use Shrew\Mazzy\Lib\Core\Collection;


/**
 * Générateur de templates html
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Template implements \Shrew\Mazzy\Lib\Core\OutputInterface
{

    /**
     * Thème par défaut
     * @var string 
     */
    protected static $default;

    /**
     * Thème du template
     * @var string 
     */
    protected static $theme;

    /**
     * Fichier de template
     * @var string 
     */
    protected $filename;

    /**
     * Données à passer au template
     * @var array
     */
    protected $data;
    
    /**
     * Permet d'ajouter des variables globales
     * 
     * _(alias de `Tpl::setGlobal`)_
     * 
     * @param string $label
     * @param mixed $value
     */
    final public static function setGlobal($label, $value)
    {
        Tpl::setGlobal($label, $value);
    }

    /**
     * Permet de définir un thème par défaut
     * 
     * Lorsque cette méthode est oubliée, le thème par défaut se dénomme *default*
     * 
     * @param string $theme Nom du thème 
     * @throws TemplateException Lorsque le thème n'existe pas
     */
    final public static function setDefaultTheme($theme = null)
    {
        if ($theme !== null && is_dir(APP_ROOT . "/templates/$theme")) {
            self::$default = $theme;
        } else {
            throw new TemplateException("Le thème *$theme*, définit par défaut n'existe pas", 500);
        }
    }
    
    final public static function hasDefaultTheme()
    {
        return (self::$default !== null);
    }

    /**
     * Définit le thème à charger
     * 
     * @param string $theme Nom du thème 
     * @throws TemplateException Lorsque le thème n'existe pas
     */
    final public static function setTheme($theme = null)
    {
        if ($theme !== null) {
            if (is_dir(APP_ROOT . "/templates/$theme")) {
                self::$theme = $theme;
            } else {
                self::$theme = self::$default;
                throw new TemplateException("Le thème *$theme* n'existe pas", 202);
            }
        }
    }

    /**
     * @param string $name Nom du template
     * @throws TemplateException Lorsqu'aucun fichier de template n'est trouvé
     */
    public function __construct($name)
    {
        // Fichier de template
        $theme = (self::$theme !== null) ? self::$theme : self::$default;
        $filename = APP_ROOT . "/templates/$theme/$name.php";

        // Si le fichier n'existe pas on tente de charger le template par défaut
        if (!file_exists($filename)) {
            $theme = self::$default;
            $filename = APP_ROOT . "/templates/$theme/$name.php";

            if (!file_exists($filename)) {
                throw new TemplateException("Le fichier de template *$name* n'existe pas", 500);
            }
        }

        $this->filename = $filename;
        $this->data = array();
        $this->data["tpl"] = new Collection();
    }

    /**
     * Retourne le type mime gérer par le moteur de template
     * 
     * @return string
     */
    public function getType()
    {
        return "text/html; charset=utf-8";
    }
    
    /**
     * Récupère le hash md5 du fichier de template
     * 
     * Cette méthode est utile pour vérifier si le fichier de template a
     * connu des modifications depuis la dernière éxécution.
     * 
     * @return string
     */
    public function getHash()
    {
        return md5_file($this->filename);
    }

    /**
     * Permet de transmettre des données à la vue
     * 
     * @param string $label Label permettant d'accéder aux données transmises
     * @param mixed $value Valeur des données transmises
     */
    final public function set($label, $value)
    {
        $this->data[$label] = $value;
    }

    final public function __set($label, $value)
    {
        $this->data["tpl"]->set($label, $value);
    }

    /**
     * Génère le rendu de la vue
     * 
     * @return string
     */
    public function generate()
    {
        extract($this->data, EXTR_PREFIX_SAME, "tpl_");

        ob_start("ob_iconv_handler");
        include $this->filename;
        $output = ob_get_contents();
        ob_end_clean();

        // Passage du garbage collector
        gc_collect_cycles();

        return $output;
    }
    
}
