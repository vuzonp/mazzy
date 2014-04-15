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
 * Test unitaire de la classe Router
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-15
 */
class Router extends PHPUnit_Framework_TestCase
{
    
    /**
     * @expectedException Shrew\Mazzy\Lib\Route\RouterException
     * @expectedExceptionMessage Page `/foo/bar` introuvable
     * @expectedExceptionCode 404
     */
    public function testExceptionNotConfiguredRoutes()
    {
        $router = new Shrew\Mazzy\Lib\Route\Router();
        $router->find("/foo/bar");
    }
    
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage La classe contrôleur `Foo` n'existe pas
     * @expectedExceptionCode 500
     */
    public function testExceptionControllerNotExists()
    {
        $router = new Shrew\Mazzy\Lib\Route\Router();
        $router->set("/", "Foo", "bar");
        $router->find("/");
    }
    
    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage La méthode `Shrew\Mazzy\Example\Handler\Foo::notExistsAction` n'est pas disponible
     * @expectedExceptionCode 500
     */
    public function testExceptionMethodNotExists()
    {
        $router = new Shrew\Mazzy\Lib\Route\Router();
        $router->pushNamespace("Shrew\Mazzy\Example\Handler");
        $router->set("/", "Foo", "notExistsAction");
        $router->find("/");
    }
    
    public function testGoodRouteWithoutAlias()
    {
        $router = new Shrew\Mazzy\Lib\Route\Router();
        $router->pushNamespace("Shrew\Mazzy\Example\Handler");
        $router->set("/", "Foo", "bar");
        $this->assertEquals($router->find("/"), true);
    }
    
    public function testGoodRouteWithAlias()
    {
        $router = new Shrew\Mazzy\Lib\Route\Router();
        $router->pushNamespace("Shrew\Mazzy\Example\Handler");
        $router->setAliases(":id", "[0-9]{1,11}");
        $router->set("/:id", "Foo", "bar");
        $this->assertEquals($router->find("/:id"), true);
    }
}
