<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Reference\Money\Type\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Reference\Money\Type\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group reference-money
 */
#[When(env: 'test')]
class MoneyTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @see MoneyDTO */
        $MoneyInt = new Money(1);
        self::assertEquals(1.0, $MoneyInt->getValue());

        // Округление до двух знаков

        $MoneyFloat = new Money(123.124);
        self::assertEquals(123.12, $MoneyFloat->getValue());

        $MoneyFloat = new Money(123.125);
        self::assertEquals(123.13, $MoneyFloat->getValue());


        // Преобразуем строки

        $MoneyStringReplace = new Money('123,125');
        self::assertEquals(123.13, $MoneyStringReplace->getValue());

        $MoneyString = new Money('123.125');
        self::assertEquals(123.13, $MoneyString->getValue());


        $MoneySelf = new Money($MoneyString);
        self::assertEquals(123.13, $MoneySelf->getValue());


        // Математические действия

        $MoneySelf = $MoneySelf->add($MoneyString);
        self::assertEquals(246.26, $MoneySelf->getValue());


        $MoneySelf = $MoneySelf->sub($MoneyString);
        self::assertEquals(123.13, $MoneySelf->getValue());

    }

}