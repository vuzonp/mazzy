<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace Shrew\Mazzy\Security\Parser;


/**
 * Description of Text
 *
 * @author thomas
 */
class Text extends AbstractParser
{

    protected $available;
    protected $length;
    protected $message;
    protected $value;

    public function __construct($value)
    {
        $value = (string) $value;
        $this->available = true;
        $this->length = mb_strlen($value);
        
        parent::__construct($value);
    }

    /**
     * 
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public function required()
    {
        if (empty(trim($this->value))) {
            $this->setInvalid();
        }
        return $this;
    }

    /**
     * 
     * @param type $size
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public function min($size)
    {
        if ($this->length < $size) {
            $this->setInvalid();
        }
        return $this;
    }

    /**
     * 
     * @param type $size
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public function max($size)
    {
        if ($this->length > $size) {
            $this->setInvalid();
        }
        return $this;
    }

    /**
     * 
     * @param type $min
     * @param type $max
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public function range($min, $max)
    {
        if ($this->length < $min || $this->length > $max) {
            $this->setInvalid();
        }
        return $this;
    }

    /**
     * 
     * @param type $pattern
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public function matches($pattern)
    {
        if (preg_match($pattern, $this->value) === false) {
            $this->setInvalid();
        }
        return $this;
    }
    
    /**
     * 
     * @param type $comparaison
     * @return \Shrew\Mazzy\Security\Parser\Text
     */
    public function contains($comparaison)
    {
        if (strpos($this->value, $comparaison) === false) {
            $this->setInvalid();
        }
        return $this;
    }
    
}
