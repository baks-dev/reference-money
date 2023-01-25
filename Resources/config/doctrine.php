<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Reference\Money\Type\Money;
use BaksDev\Reference\Money\Type\MoneyType;
use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrine)
{
	$doctrine->dbal()->type(Money::TYPE)->class(MoneyType::class);
};