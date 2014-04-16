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
namespace Shrew\Mazzy\Lib\Input;

/**
 * Description of InputStorage
 *
 * @author thomas
 */
class Request extends InputContainer
{

    private $egpcs;

    protected function initialize()
    {
        parent::initialize();

        $this->egpcs = array(
            INPUT_GET,
            INPUT_POST,
            INPUT_COOKIE,
            INPUT_SESSION
        );
    }
    
    private function getType($label)
    {
        foreach ($this->egpcs as $type) {
            if (filter_has_var($type, $label)) {
                return $type;
            }
        }
        return null;
    }

    final public function exists($label)
    {
        return ($this->getType($label) !== null);
    }

    final public function get($label, $filter = FILTER_DEFAULT, $options = null)
    {
        $type = $this->getType($label);
        return filter_input($type, $label, $filter, $options);

    }

}
