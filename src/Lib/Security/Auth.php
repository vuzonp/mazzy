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

namespace Shrew\Mazzy\Lib\Security;

/**
 * Gestion des authorisations
 * 
 * Les autorisations sont de type CRUD (*Create*, *Read*, *Update* et *Delete*)
 * et sont réparties par groupes.
 * 
 * La classe dispose par défaut d'un rôle "guest" (invité) n'ayant aucun droit
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-13
 */
class Auth
{

    /**
     * Interdiction d'effectuer une action quelle qu'elle soit
     */
    const PROHIBIT = 0b0000;

    /**
     * Autoriser la création d'une ressource
     */
    const CREATE = 0b0001;

    /**
     * Autoriser la lecture d'une ressource
     */
    const READ = 0b0010;

    /**
     * Autoriser la mise à jour d'une ressource
     */
    const UPDATE = 0b0100;

    /**
     * Autoriser la suppression d'une ressource
     */
    const DELETE = 0b1000;

    /**
     * Donner tous les droits d'accès à une ressource
     */
    const ALL_RIGHTS = 0b1111;

    /**
     * Liste des groupes disponibles
     * @var array 
     */
    private static $availableRoles = array("guest" => self::PROHIBIT);

    /**
     * Groupe par défaut pour un utilisateur anonyme
     * @var string 
     */
    private static $defaultRole = "guest";

    /**
     * Tableau des rôles et de leurs droits respectifs pour l'instance active
     * @var array 
     */
    private $roles;

    /**
     * Rôle actuel de l'utilisateur chargé en session
     * @var string 
     */
    private $role;

    /**
     * Ajoute un rôle et ses droits par défauts à ceux disponibles
     * 
     * @param string $name Nom à attribuer au rôle (exemple: *editor*) 
     * @param integer $defaultRights Droits par défauts pour les utilisateurs possédant ce rôle
     */
    public static function setRole($name, $defaultRights = self::PROHIBIT)
    {
        self::$availableRoles[$name] = $defaultRights;
    }

    /**
     * Définit le rôle par défaut à appliquer à tout utilisateur inconnu
     * 
     * @param string $role Un rôle déjà définit
     * @return boolean Vrai quand le groupe peut être déclaré par défaut
     * @throws \DomainException Lorsque le rôle n'a pas été défini
     */
    public static function setDefaultRole($role)
    {
        if ((self::roleExists($role)) === false) {
            throw new \DomainException("Le groupe utilisateur *$role* n'existe pas", 500);
        }
        self::$defaultRole = $role;
        return true;
    }

    /**
     * Récupère la liste complète des rôles disponibles
     * 
     * @return array
     */
    public static function listRoles()
    {
        return array_keys(self::$availableRoles);
    }

    /**
     * Vérifie si un rôle existe
     * 
     * @param string $role
     * @return boolean
     */
    public static function roleExists($role)
    {
        return isset(self::$availableRoles[$role]);
    }
    
    /**
     * Supprime un rôle
     * 
     * @param string $role
     * @return boolean
     */
    public static function deleteRole($role)
    {
        unset(self::$availableRoles[$role]);
        return true;
    }
    
    /**
     * Replace la liste des rôles à l'état initial
     */
    public static function resetRoles()
    {
        self::$defaultRole = "guest";
        self::$availableRoles = array("guest" => self::PROHIBIT);
    }

    /**
     * Charge le groupe de l'utilisateur à partir de la session,
     * 
     * ou bien attribue le rôle par défaut si la session est vide.
     * Initialise les autorisations d'accès à partir des droits par défaut.
     * 
     * @param string $activeRole Rôle actif (celui de l'utilisateur le plus souvent)
     */
    public function __construct($activeRole = null)
    {
        // Définit le rôle par défaut s'il ne l'a pas encore été
        if (!self::roleExists("guest")) {
            self::setRole("guest", self::PROHIBIT);
        }

        // utilisateur
        // Si le rôle est invalide on attribue celui par défaut au client
        if (self::roleExists($activeRole)) {
            $this->role = $activeRole;
        } else {
            $this->role = self::$availableRoles[self::$defaultRole];
        }

        // initialisation des rôles 
        foreach (self::$availableRoles as $role => $rights) {
            $this->setAllowed($role, $rights);
        }
    }

    /**
     * Définit les autorisations de la ressource
     * 
     * @param string $role Rôle ciblé
     * @param integer $rights Autorisations accordées à ce rôle par la ressource
     * @throws AppException Lorsque le rôle n'existe pas
     */
    public function setAllowed($role, $rights)
    {
        if (self::roleExists($role) === false) {
            throw new AppException("Le groupe utilisateur *$role* n'existe pas");
        }
        $this->roles[$role] = $rights;
    }

    /**
     * Vérifie si l'utilisateur a le droit d'effectuer une action
     * 
     * @param integer $right Action à vérifier
     * @return boolean
     */
    public function isAllowed($right)
    {
        return ($this->roles[$this->role] & $right === $right );
    }

    /**
     * Vérifie si l'utilisateur a le droit de créer une ressource
     * 
     * @return boolean
     */
    public function canCreate()
    {
        return $this->isAllowed(self::CREATE);
    }

    /**
     * Vérifie si l'utilisateur a le droit de lire une ressource
     * 
     * @return boolean
     */
    public function canRead()
    {
        return $this->isAllowed(self::READ);
    }

    /**
     * Vérifie si l'utilisateur a le droit de mettre à jour une ressource
     * 
     * @return boolean
     */
    public function canUpdate()
    {
        return $this->isAllowed(self::UPDATE);
    }

    /**
     * Vérifie si l'utilisateur a le droit de détruire une ressource
     * 
     * @return boolean
     */
    public function canDelete()
    {
        return $this->isAllowed(self::DELETE);
    }

}
