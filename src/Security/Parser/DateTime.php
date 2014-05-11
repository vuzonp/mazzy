<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Shrew\Mazzy\Security\Parser;

/**
 * Description of DateTime
 *
 * @author thomas
 */
class DateTime extends AbstractParser
{
    private $formatAvailable;
    
    public function __construct($value)
    {
        try {
            $value = new \DateTimeImmutable($value);
            $this->formatAvailable = true;
        } catch(\Exception $e) {
            $this->formatAvailable = false;
            $this->setMessage($e->getMessage());
            $value = new \DateTimeImmutable("0000-01-01");
        }
        
        parent::__construct($value);
    }
    
    public function required()
    {
        if ($this->formatAvailable !== true) {
            $this->setInvalid();
        }
        return $this;
    }
    
    public function equals($comparaison)
    {
        $comparaison = new \DateTimeImmutable($comparaison);
        parent::equals($comparaison);
    }

    public function min($min)
    {
        $min = new \DateTimeImmutable($min);
        parent::min($min);
    }
    
    public function max($max)
    {
        $max = new \DateTimeImmutable($max);
        parent::max($max);
    }
    
}
