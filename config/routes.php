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

use Shrew\Mazzy\Lib\Route\Router;

/**
 * Configuration des routes de l'application
 */
$route = new Router();


// Espaces de noms des handlers
//------------------------------------------------------------------------------

$route->pushNamespace("Shrew\Mazzy\Example\Handler");


// Définition des alias de route
//------------------------------------------------------------------------------

$route->setAliases(":num", "[0-9]+");
//$route->setAliases(":slug", "[A-Za-z0-9_\-]+");
//$route->setAliases(":file", "[A-Za-z0-9_\-]+\.[a-zA-Z]{2,4}");


// Routage de l'application
//------------------------------------------------------------------------------

$route->set("/", "Hello", "indexAction");


//------------------------------------------------------------------------------
return $route;