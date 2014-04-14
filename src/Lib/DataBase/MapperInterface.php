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

namespace Shrew\Mazzy\Lib\DataBase;

/**
 * Interface destinée aux modèles (Mvc)
 *
 * @author  Thomas Girard <thomas@shrewstudio.com>
 * @license http://opensource.org/licenses/MIT
 * @version v0.1.0-alpha2
 * @since   2014-04-14
 */
interface MapperInterface
{

    /**
     * Insertion d'une nouvelle ligne dans la table
     */
    public function insert(Shrew\Framework\Lib\Collection $collection);

    /**
     * Récupère des lignes dans la table par recherche égalitaire
     */
    public function findMany($prop, $value);

    /**
     * Récupère une ligne dans la table par recherche égalitaire
     */
    public function findOne($prop, $value);

    /**
     * Récupère une ligne dans la table par recherche sur clé primaire
     */
    public function findByPrimary($id);

    /**
     * Met-à-jour une ligne de la table
     */
    public function update(Shrew\Framework\Lib\Collection $collection);

    /**
     * Supprime une ligne dans la table par recherche sur clé primaire
     */
    public function delete($prop, $value);
}
