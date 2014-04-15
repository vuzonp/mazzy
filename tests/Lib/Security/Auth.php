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

//var_dump(realpath(__DIR__ . "/../../../vendor/autoload.php"));
require __DIR__ . "/../../../vendor/autoload.php";

/**
 * Test unitaire de la gestion des droits d'accès
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-15
 */
class Auth extends PHPUnit_Framework_TestCase
{
    /**
     * Un seul groupe déclaré par défaut
     */
    public function testOnlyOneRoleDeclaredByDefault()
    {
        $roles = \Shrew\Mazzy\Lib\Security\Auth::listRoles();
        $this->assertEquals(sizeof($roles), 1);
    }
    
    /**
     * Le nom du groupe par défaut est *guest*
     */
    public function testListOfRolesWithoutAnyConfiguration()
    {
        $roles = \Shrew\Mazzy\Lib\Security\Auth::listRoles();
        $this->assertEquals($roles[0], "guest");
    }
    
    /**
     * Lorsqu'un groupe n'existe pas, vérifier son existence, retourne faux
     */
    public function testExistenceOfEmptyRole()
    {
        $exists = \Shrew\Mazzy\Lib\Security\Auth::roleExists("unknown");
        $this->assertEquals($exists, false);
    }

    /**
     * Le groupe par défaut est vérifiable comme existant
     */
    public function testPreconfigurationOfGuestRole()
    {
        $exists = \Shrew\Mazzy\Lib\Security\Auth::roleExists("guest");
        $this->assertEquals($exists, true);
    }

    /**
     * Après la déclaration d'un rôle, celui-ci existe et devient vérifiable
     */
    public function testExistenceOfDeclaredRole()
    {
        \Shrew\Mazzy\Lib\Security\Auth::setRole("tester");
        $exists = \Shrew\Mazzy\Lib\Security\Auth::roleExists("tester");
        $this->assertEquals($exists, true);
    }
    
    /**
     * Utiliser la méthode statique de réinitialisation des groupes permet
     * de revenir à l'état où seul guest existe
     */
    public function testResetRoles()
    {
        \Shrew\Mazzy\Lib\Security\Auth::resetRoles();
        $roles = \Shrew\Mazzy\Lib\Security\Auth::listRoles();
        $this->assertEquals(sizeof($roles), 1);
        $this->assertEquals($roles[0], "guest");
    }
    
    /**
     * Supprimer un rôle le rend inaccessible
     */
    public function testDeletingRole()
    {
        \Shrew\Mazzy\Lib\Security\Auth::setRole("tester");
        \Shrew\Mazzy\Lib\Security\Auth::deleteRole("tester");
        $exists = \Shrew\Mazzy\Lib\Security\Auth::roleExists("tester");
        $this->assertEquals($exists, false);
    }

    /**
     * Il est impossible de définir un groupe non déclaré comme groupe par défaut
     * 
     * @expectedException \DomainException
     * @expectedExceptionMessage Le groupe utilisateur *unknown* n'existe pas
     * @expectedExceptionCode 500
     */
    public function testDefaultRoleWhenEmpty()
    {
        \Shrew\Mazzy\Lib\Security\Auth::setDefaultRole("unknown");
    }

    /**
     * Il est possible de déclarer tout groupe déclaré comme groupe par défaut
     */
    public function testDefaultRoleAfterDeclaration()
    {
        \Shrew\Mazzy\Lib\Security\Auth::setRole("tester");
        $isAllowed = \Shrew\Mazzy\Lib\Security\Auth::setDefaultRole("tester");
        $this->assertEquals($isAllowed, true);
    }

}
