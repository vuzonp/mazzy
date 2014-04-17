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
 * Représentation sous form d'objet des fichiers reçus par http
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

    /**
     * Définit le répertoire de sauvegarde par défaut des fichiers
     * 
     * @param string $directory Répertoire de destination (ouvert en écriture)
     */
    public static function setTargetDirectory($directory)
    {
        self::$destDirectory = $directory;
    }

    /**
     * @param array $uploadInfos Informations sur le fichier reçu
     * @throws UploadException Lorsque le fichier défini n'est pas un fichier reçu en http
     */
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

    /**
     * Le fichier a-t-il rencontré un problème lors de son transfert ?
     * @return boolean
     */
    public function hasError()
    {
        return ($this->error !== 0);
    }

    /**
     * Récupération du code d'erreur renvoyé par php lors du transfert de fichier
     * @return integer
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Retourne le nom de sauvegarde du fichier
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Demande au programme de renommer le fichier 
     * @param string|null $name Lorsque null, alors un nom sera généré par la classe
     */
    public function rename($name = null)
    {
        if ($name === null) {
            $name = $this->generateName();
        }
        $this->name = "$name." . $this->getExtension();
    }

    /**
     * Génère un nom de fichier unique et aléatoire
     */
    private function generateName()
    {
        $strong = false;
        $prefix = dechex(time());
        $basename = bin2hex(openssl_random_pseudo_bytes(4096, $strong));
        $this->name = "$prefix-$basename";
    }

    /**
     * Sauvegarde le fichier transféré pour un stockage permanent
     * 
     * @param string|null $directory Répertoire de destination
     * @throws UploadException Lorsque le répertoire de destination n'est pas un chemin valide
     */
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

    /**
     * Sauvegarde le fichier en remplaçant un fichier plus ancien.
     * 
     * Attention, la nouvelle sauvegarde aura le même *basename* mais pourra
     * utiliser une extension différente. Il convient par conséquent de récupérer
     * le nom définitif avec `FileObject::getName()` après la sauvegarde. 
     * 
     * @param string $fileName Nom de l'ancien fichier
     * @param string Répertoire de destination (où se trouve l'ancien fichier)
     */
    public function replace($fileName, $directory = null)
    {
        // supprimer les anciens fichiers
        //...
        ///$this->save();
    }

}
