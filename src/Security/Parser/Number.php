<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Shrew\Mazzy\Security\Parser;

/**
 * Description of Number
 *
 * @author thomas
 */
class Number extends AbstractParser
{
    public function __construct($value)
    {
        $value = ($value) ?: 0;
        parent::__construct($value);
    }
    
    
}
