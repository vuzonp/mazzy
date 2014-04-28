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

namespace Shrew\Mazzy\Media;

/**
 * Manipulation des images
 * 
 * Permet de créer des miniatures par redimensionnement et découpage,
 * de placer un marquage et de sauvegarder les changements ou de renvoyer
 * directement le résultat obtenu.
 * 
 * Les formats acceptés sont : `jpeg`, `png`, `gif` et `bmp`
 *
 * @todo    Mettre en place une fonctionnalité de marquage des images
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Image extends MediaInfo
{

    private $image;
    private $mimetype;
    private $width;
    private $height;

    /**
     * @param string $filename Fichier image
     * @throws \Shrew\Mazzy\Media\MediaException Lorsque l'image ne peut être manipulée
     */
    public function __construct($filename)
    {
        // Vérifie que l'image est manipulable
        if (!is_readable($filename)) {
            throw new MediaException("L'image `$filename` n'est pas accessible");
        }

        // Détection du type mime
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $this->mimetype = $finfo->file($filename);

        // Chargement de l'image avec GD2
        if ($this->mimetype === "image/jpeg") {
            $image = imagecreatefromjpeg($filename);
        } elseif ($this->mimetype === "image/png") {
            $image = imagecreatefrompng($filename);
        } elseif ($this->mimetype === "image/gif") {
            $image = imagecreatefromgif($filename);
        } elseif ($this->mimetype === "image/bmp") {
            $image = imagecreatefromwbmp($filename);
        } else {
            throw new MediaException("Impossible de manipuler les fichiers "
            . "de type `{$this->mimetype}`", 415);
        }
        
        parent::__construct($filename);

        // Initialisation :
        $this->saveChanges($image);
    }

    /**
     * Crée une image vide pour redimensionner, couper, etc. l'image originale
     *
     * @return gd ressource image
     */
    public function newImage($width, $height)
    {
        $new = imagecreatetruecolor($width, $height);

        // Transparence pour les images possédant une couche alpha
        if ($this->mimetype === "image/png" || $this->mimetype === "image/gif") {
            imagecolortransparent($new, imagecolorallocatealpha($new, 0, 0, 0, 127));
            imagealphablending($new, false);
            imagesavealpha($new, true);
        }
        return $new;
    }

    /**
     * Met à jour les propriétés de l'objet
     *
     * @param image Image de remplacement
     */
    private function saveChanges($image)
    {
        if (!empty($image)) {
            $this->image = $image;
            $this->width = imagesx($this->image);
            $this->height = imagesy($this->image);
        }
    }

    /**
     * Redimensionne une image aux dimensions indiquées en la déformant si besoin
     *
     * @param integer $width Largeur de l'image
     * @param integer $height Hauteur de l'image
     */
    public function resize($width, $height)
    {
        $width = intval($width);
        $height = intval($height);

        $image = $this->newImage($width, $height);
        imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        $this->saveChanges($image);
    }

    /**
     * Redimensionne une image proportionnellement en utilisant une nouvelle largeur
     *
     * @param integer $width Largeur de l'image
     */
    public function resizeByWidth($width)
    {
        if (!empty($width)) {
            $height = $width * $this->height / $this->width;
            $this->resize($width, $height);
        }
    }

    /**
     * Redimensionne une image proportionnellement en utilisant une nouvelle hauteur
     *
     * @param integer $height Hauteur de l'image
     */
    public function resizeByHeight($height)
    {
        if (!empty($height)) {
            $width = $height * $this->width / $this->height;
            $this->resize($width, $height);
        }
    }

    /**
     * Redimensionne une image aux dimensions indiquées 
     * en permettant de respecter les propotions du format original
     *
     * @param integer $width Largeur de l'image
     * @param integer $height Hauteur de l'image
     * @param boolean $ratio Si `true` alors les proportions seront respectées
     */
    public function scale($width, $height = null, $ratio = true)
    {
        if ($ratio === true || empty($width) || empty($height)) {
            if (empty($height) || $width < $height) {
                $this->resizeByWidth($width);
            } else {
                $this->resizeByHeight($height);
            }
        } else {
            $this->resize($width, $height);
        }
    }

    /**
     * Recadre une image à la taille indiquée
     * 
     * Par défaut, le recadrage se fait par le centre de l'image au niveau de l'horizontalité
     * et à 1/3 en hauteur pour la verticalité. C'est effectivement le plus souvent
     * à cet emplacement que se situent les éléments importants d'une photographie
     * 
     * @param integer $width Largeur de l'image
     * @param integer $height Hauteur de l'image
     * @param string $centering Position du cadrage
     */
    public function crop($width, $height, $centering = "")
    {
        $points = explode(" ", $centering);

        // left
        //$x = 0;
        //$y = 0;
        // center
        //$x = intval(($this->width / 2) - ($width / 2));
        //$y = intval(($this->height / 2) - ($height / 2));
        // right
        //$x = $this->width - $width;
        //$y = $this->height - $height;
        // Centré en x et à 1/3 en y (classique de la photographie)
        //$x = intval(($this->width / 2) - ($width / 2));
        //$y = intval(($this->height * 0.33) - ($height * 0.33));
        // Horizontalité
        if ($points[1] === "left") {
            $x = 0;
        } elseif ($points[1] === "center") {
            $x = intval(($this->width / 2) - ($width / 2));
        } elseif ($points[1] === "right") {
            $x = $this->width - $width;
        } else {
            $x = intval(($this->width / 2) - ($width / 2));
        }

        // Verticalité
        if ($points[0] === "top") {
            $y = 0;
        } elseif ($points[0] === "middle" || $points[0] === "center") {
            $y = intval(($this->height / 2) - ($height / 2));
        } elseif ($points[0] === "bottom") {
            $y = intval($this->height - $height);
        } else {
            $y = intval(($this->height * 0.33) - ($height * 0.33));
        }

        $image = $this->newImage($width, $height);
        imagecopyresampled($image, $this->image, 0, 0, $x, $y, $width, $height, $width, $height);
        $this->saveChanges($image);
    }

    /**
     * Création d'une miniature
     * 
     * Les miniatures sont des images réduites et recadrées afin de générer
     * une image sans bordures noires lors de la découpe.
     * 
     * @param integer $width Largeur de l'image
     * @param integer $height Hauteur de l'image
     * @param string $centering Position du cadrage
     */
    public function thumb($width, $height, $centering = "")
    {
        // Sélectionne la réduction à effectuer en comparant les formats des 2 images
        if (($this->width / $this->height) > ($width / $height)) {
            $this->resizeByHeight($height);
        } else {
            $this->resizeByWidth($width);
        }

        $this->crop($width, $height, $centering);
    }

    /**
     * Sauvegarde l'image générée dans un fichier
     *
     * @param string $filename Chemin de sauvegarde
     */
    public function save($filename)
    {
        if (!is_writable(dirname($filename))) {
            throw new AppException("Le programme ne peut pas écrire le fichier `$filename`");
        }

        $copy = $this->newImage($this->width, $this->height);
        imagecopyresampled($copy, $this->image, 0, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);

        $copy = $this->image;
        if ($this->mimetype === "image/jpeg") {
            imagejpeg($copy, $filename);
        } elseif ($this->mimetype === "image/png") {
            imagepng($copy, $filename);
        } elseif ($this->mimetype === "image/gif") {
            imagegif($copy, $filename);
        } else {
            imagewbmp($copy, $filename);
        }
    }

    /**
     * Retourne l'image au client
     */
    public function send()
    {
        $res = Response::getInstance();
        $res->setType($this->mimetype, false);
        $res->setStatus(200);
        $res->sendHeaders();

        if ($this->mimetype === "image/jpeg") {
            imagejpeg($this->image);
        } elseif ($this->mimetype === "image/png") {
            imagepng($this->image);
        } elseif ($this->mimetype === "image/gif") {
            imagegif($this->image);
        } else {
            imagewbmp($this->image);
        }
        
        exit();
    }

    public function __destruct()
    {
        imagedestroy($this->image);
    }

}
