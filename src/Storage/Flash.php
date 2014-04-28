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

namespace Shrew\Mazzy\Storage;

/**
 * Stockage de données temporaire
 * 
 * Cette classe est destinée à transmettre des données entre deux requêtes. Elle
 * ne peut être ouverte qu'en écriture ou en lecture et jamais dans les deux modes 
 * simultanément.
 * Chaque fois que les données sont rechargées, elles sont supprimées afin
 * d'interdir une utilisation en stockage permanent. (Pour une telle utilisation
 * regarder du côté de la classe Session).
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 */
class Flash implements \Countable
{

    /**
     * Préfix des entrées pour le stockage en session
     */
    const PREFIX = "_flash.";

    /**
     * Mode Lecture seule
     */
    const READ = 0;

    /**
     * Mode écriture seule
     */
    const WRITE = 1;

    /**
     * @var \Shrew\Mazzy\Storage\Session
     */
    protected $session;

    /**
     * Identifiant permanent de l'objet au sein de la session
     * @var string
     */
    protected $pid;

    /**
     * Nom de l'objet actif
     * @var string
     */
    protected $name;

    /**
     * Valeurs de stockage
     * @var array 
     */
    protected $values;

    /**
     * Mode d'ouverture de l'objet (self::READ ou self::WRITE)
     * @var integer 
     */
    protected $mode;

    /**
     * Initialisation de l'objet (récupération des données si elles existent)
     * 
     * @param string $name Nom de l'objet
     * @param integer $mode Mode d'ouverture de l'objet
     * @throws \Shrew\Mazzy\Storage\StorageException Quand le mode d'écriture n'est pas proposé par la classe
     */
    public function __construct($name, $mode = self::READ)
    {
        $this->name = strtolower($name);
        $this->pid = self::PREFIX . $name;
        $this->session = Session::getInstance();

        if ($mode !== self::READ && $mode !== self::WRITE) {
            throw new StorageException("Le mode d'ouverture du stockage flash doit être écriture ou lecture");
        }

        $this->mode = $mode;

        if ($mode === self::READ) {
            $this->values = $this->loadAndPurge($this->pid);
        } else {
            $this->purge();
        }
    }

    /**
     * Vérifie si l'objet est accessible en lecture
     *
     * @return boolean retourne toujours vrai à moins de lever une exception
     * @throws \Shrew\Mazzy\Storage\StorageException Lorsque l'objet est verrouillé en écriture
     */
    final protected function isReadable()
    {
        if ($this->mode !== self::READ) {
            throw new StorageException("Impossible de lire dans la mémoire flash,"
            . " celle-ci est ouverte en écriture seule");
        }
        return true;
    }

    /**
     * Vérifie si l'objet est accessible en écriture
     *
     * @return boolean retourne toujours vrai à moins de lever une exception
     * @throws \Shrew\Mazzy\Storage\StorageException Lorsque l'objet est verrouillé en lecture
     */
    final protected function isWritable()
    {
        if ($this->mode !== self::WRITE) {
            throw new StorageException("Impossible d'écrire dans la mémoire flash,"
            . " celle-ci est ouverte en lecture seule");
        }
        return true;
    }

    /**
     * Récupère et supprime les valeurs depuis la session.
     *
     * @param string $key identifiant utilisé en session
     * @return array Tableau contenant les valeurs préalablement stockées en session
     */
    protected function loadAndPurge($key)
    {
        //$this->isReadable();
        $values = $this->session->get($key);
        if ($values === null) {
            $values = array();
        } else {
            $this->session->delete($key);
        }
        return $values;
    }

    /**
     * Sauvegarde les données en session
     */
    protected function save()
    {
        $this->isWritable();
        $this->session->set($this->pid, $this->values);
    }

    /**
     * Retourne la quantité de valeurs stockées dans l'objet
     *
     * @return integer
     */
    public function count()
    {
        return count($this->values);
    }

    /**
     * Vérifie si l'objet contient des données
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return ($this->count() === 0);
    }

    /**
     * Retourne le nom de l'objet
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Permet de récupérer les données pour une manipulation par la vue
     *
     * @return array
     */
    public function getPublic()
    {
        return $this->getAll();
    }

    /**
     * Récupère une valeur de stockage
     *
     * @param string $key offset du tableau
     * @return mixed Valeur stockée
     */
    public function get($key)
    {
        $this->isReadable();
        return (isset($this->values[$key])) ? $this->values[$key] : null;
    }

    /**
     * Récupère l'ensemble des données stockées
     *
     * @return array
     */
    public function getAll()
    {
        $this->isReadable();
        return $this->values;
    }

    /**
     * Fusionne les valeurs avec de nouvelles valeurs 
     * en donnant la priorite aux anciennes en cas de conflit
     *
     * @param array $values Données complémentaires
     * @return array Un tableau contenant les données stockées, complétées par $values
     */
    public function fill($values)
    {
        $newValues = array_merge($values, $this->values);
        $this->values = $newValues;
        if ($this->mode === self::WRITE) {
            $this->save();
        }
        return $this->values;
    }

    /**
     * Permet d'ajouter une nouvelle entrée
     *
     * @param string $key Identifiant de l'entrée
     * @param mixed $value Valeur à stocker
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
        $this->save();
    }

    /**
     * Vide intégralement les données stockées au sein de l'objet et dans la session
     */
    public function purge()
    {
        $this->values = array();
        $this->session->delete($this->pid);
    }

}
