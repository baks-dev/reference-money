<?php

namespace BaksDev\Reference\Money\Type;

final class Money
{
    public const TYPE = 'money';
    
    private int|float|null $value;
    
    public function __construct(int|float|null|self $value)
    {
        if($value instanceof self)
        {
            $value = $value->getValue();
        }
        
        
        $this->value = $value ? max(0, $value) : null;
    }
    
    public function getValue() : int|float|null
    {
        return $this->value;
    }
    
}