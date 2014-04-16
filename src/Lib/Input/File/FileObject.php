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
namespace Shrew\Mazzy\Lib\Input\File;

/**
 * Description of FileObject
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-16
 */
class FileObject extends \Shrew\Mazzy\Lib\Media\MediaInfo
{

    private static $destDirectory;
    private $name;
    private $error;

    public static function setTargetDirectory($directory)
    {
        self::$destDirectory = $directory;
    }

    public function __construct(Array $uploadInfos = array())
    {
        //$fileName = get_cfg_var("upload_tmp_dir") . $uploadInfos["tmp_name"];
        $fileName = $uploadInfos["tmp_name"];

        if (is_uploaded_file($fileName) === false) {
            throw new UploadException("Le fichier *$fileName* n'est pas "
            . "un fichier envoye par le client");
        }
        parent::__construct($fileName);

        $this->name = $uploadInfos["name"];
        $this->error = $uploadInfos["error"];
    }

    public function hasError()
    {
        return ($this->error !== 0);
    }

    public function getError()
    {
        return $this->error;
    }

    public function getName()
    {
        return $this->name;
    }

    public function rename($name = null)
    {
        if ($name === null) {
            $name = $this->generateName();
        }
        $this->name = "$name." . $this->getExtension();
    }

    private function generateName()
    {
        $strong = false;
        $prefix = dechex(time());
        $basename = bin2hex(openssl_random_pseudo_bytes(4096, $strong));
        $this->name = "$prefix-$basename";
    }

    public function save($directory = null)
    {
        if ($directory === null) {
            $directory = self::$destDirectory;
        }
        $dest = new \SplFileInfo($directory);

        if ($dest->isDir() && $dest->isWritable()) {
            if (move_uploaded_file($this->tmpFilename, "$dest/$this->name") === false) {
                throw new UploadException("Une erreur est survenue lors de la "
                . "sauvegarde du fichier *{$this->name}* dans *$dest*", 500);
            }
        }
    }

    public function replace($fileName)
    {
        // supprimer les anciens fichiers
        //...
        ///$this->save();
    }

}
