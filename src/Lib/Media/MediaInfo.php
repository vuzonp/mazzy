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

namespace Shrew\Mazzy\Lib\Media;

/**
 * Interface de haut niveau avec les informations de medias
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-13
 */
class MediaInfo extends \SplFileInfo
{
    private static $finfoObject;
    private $rawMime;
    private $charset;
    private $mime;
    
    public function __construct($file_name)
    {
        parent::__construct($file_name);
        $this->setInfoClass(__CLASS__);
    }
    
    final public function exists()
    {
        return file_exists($this->__toString());
    }

    /**
     * @return \finfo
     */
    private static function finfo()
    {
        if (self::$finfoObject === null) {
            self::$finfoObject = new \finfo(FILEINFO_MIME);
        }
        return self::$finfoObject;
    }
    
    /**
     * Charge les données optionnelles à propos du fichier
     */
    private function loadOptionnalInfos()
    {
        if ($this->isDir() === false) {
            $this->rawMime = self::finfo()->file($this->getRealPath());
            $infos = explode(";", $this->rawMime);

            if (isset($infos[1])) {
                $this->charset = $infos[0];
                $this->mime = $infos[1];
            } else {
                $this->charset = false;
                $this->mime = $infos[0];
            }
        }
        else {
            $this->rawMime = false;
            $this->charset = false;
            $this->mime = false;
        }
    }
    
    /**
     * Retourne le mimetype du fichier au format *FILEINFO_MIME* 
     * @return string|false
     */
    final public function getRawMime()
    {
        if ($this->rawMime === null) {
            $this->loadOptionnalInfos();
        }
        return $this->rawMime;
    }
    
    /**
     * @return string|false Encodage du fichier lorsque disponible
     */
    final public function getCharset()
    {
        if ($this->charset === null) {
            $this->loadOptionnalInfos();
        }
        return $this->charset;
    }
    
    /**
     * Verifie si le fichier est de ype binaire
     * @return boolean
     */
    final public function isBinary()
    {
        return ($this->getCharset() === "binary");
    }
    
    /**
     * @return string|false Type mime du fichier
     */
    public function getMime()
    {
        if ($this->mime === null) {
            $this->loadOptionnalInfos();
        }
        return $this->mime;
    }
}
