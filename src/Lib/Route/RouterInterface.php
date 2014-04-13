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

namespace Shrew\Mazzy\Lib\Route;

/**
 * Interface des routers de l'application
 *
 * Attention, classe App attend des exceptions de type HttpException
 * lorsque aucune route ne correspond à l'url demandée.
 * 
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-13
 */
interface RouterInterface
{
    /**
     * Permet de rendre accessible aux routes un espace de nom.
     *
     * @param string $namespace Espace de nom sans `\\` final
     */
    public function pushNamespace($namespace);

    /**
     * Ajoute une route
     *
     * @param string $pattern motif d'url
     * @param string $class Nom de la classe cible
     * @param string $method Nom de la méthode de classe cible
     */
    public function set($pattern, $class, $method);

    /**
     * Permet d'ajouter des alias de motifs regex pour les routes
     *
     * Par exemple, permet d'utiliser `:num` au lieu de `[0-9]+`
     *
     * @param string $needle
     * @param string $regex
     */
    public function setAliases($needle, $regex);

    /**
     * Effectue une recherche sur les routes disponibles à partir de l'url
     * passée en argument. Lorsqu'une route est trouvée, la méthode exécute son
     * contrôleur.
     *
     * @param string $path Url sur laquelle effectuer la recherche
     */
    public function find($path);
}

