<?php

namespace BaksDev\Reference\Money\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType;

final class MoneyType extends BigIntType
{

    public function convertToDatabaseValue($value, AbstractPlatform $platform) : mixed
    {
        return $value instanceof Money ? $value->getValue() * 100 : $value * 100;
    }
    
    public function convertToPHPValue($value, AbstractPlatform $platform) : mixed
    {
        return !empty($value) ? new Money($value / 100) : null; //new Money(0);
    }
    
    public function getName() : string
    {
        return Money::NAME;
    }
    
    public function requiresSQLCommentHint(AbstractPlatform $platform) : bool
    {
        return true;
    }
    
}