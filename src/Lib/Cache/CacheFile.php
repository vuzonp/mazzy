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

namespace Shrew\Mazzy\Lib\Cache;

/**
 * Gestionnaire de cache utilisant le système de fichiers
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.2.0-alpha3
 */
class CacheFile
{

    protected static $directory = "/tmp";
    protected $name;
    protected $life;
    protected $uid;
    protected $file;
    protected $globPattern;

    /**
     * Permet de définir un répertoire spécifique pour la sauvegarde 
     * des fichiers de cache
     * 
     * @param string $directory Répertoire de destination des fichiers de cache
     * @throws \Shrew\Mazzy\Lib\Cache\CacheException
     */
    public static function setPath($directory)
    {
        $dir = new \SplFileInfo($directory);
        if ($dir->isDir() && $dir->isWritable() && $dir->isReadable()) {
            self::$directory = $dir;
        } else {
            throw new CacheException("Le repertoire de cache $dir doit etre ouvert"
            . " en lecture et ecriture", 500);
        }
    }

    /**
     * @param string $name Nom du cache
     * @param integer $life Durée de vie du cache
     */
    public function __construct($name, $life = 0)
    {
        $this->name = $name;
        $this->file = new \SplFileInfo(self::$directory . "/cache-{$this->name}.cache");
        $this->globPattern = self::$directory . "/cache-*.cache";
        $this->life = intval($life);
    }

    /**
     * Génère un identifiant de cache unique à partir de données choisies permettant
     * de détecter un changement d'état du contenu placé en cache.
     * 
     * @param mixed $data Une donnée destinée à vérifier la modification du cache
     */
    final public function generateUID($data)
    {
        $this->uid = md5(serialize($data));
        $this->file = new \SplFileInfo(self::$directory . "/cache-{$this->name}-{$this->uid}.cache");
        $this->globPattern = self::$directory . "/cache-{$this->name}-*.cache";
    }

    /**
     * Sauvegarde le cache dans un fichier
     * 
     * @param string $data Contenu à sauvegarder
     */
    public function save($data)
    {
        file_put_contents((string) $this->file, $data);
    }

    /**
     * Vérifie si un fichier de cache valide existe pour la ressource
     * 
     * @return boolean
     */
    public function exists()
    {
        if ($this->life > 0) {
            return ($this->file->isReadable() 
                    && $this->file->getCTime() + $this->life > time());
        }
        return false;
    }

    /**
     * Récupère le fichier de cache
     * 
     * @return \SplFileInfo
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Récupère le contenu du cache
     * 
     * @return string
     */
    public function getContent()
    {
        return file_get_contents((string) $this->file);
    }

    /**
     * Supprime l'ensemble des caches correspondant à l'object en cours
     */
    public function purge()
    {
        $files = glob($this->globPattern);
        if ($files !== false) {
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * Supprime le cache actuel
     */
    public function delete()
    {
        @unlink((string) $this->file);
    }

}
