<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

        $MoneyNull = new Money(null);
        self::assertSame($MoneyNull->getValue(), 0);

        $MoneyNull = new Money(0);
        self::assertSame($MoneyNull->getValue(), 0);

        $MoneyNull = new Money(0.0);
        self::assertSame($MoneyNull->getValue(), 0);

        $MoneyNull = new Money('0.0');
        self::assertSame($MoneyNull->getValue(), 0);

        $MoneyNull = new Money('0');
        self::assertSame($MoneyNull->getValue(), 0);

        $MoneyNull = new Money('');
        self::assertSame($MoneyNull->getValue(), 0);


        /** @see MoneyDTO */
        $MoneyInt = new Money(1);
        self::assertEquals(1.0, $MoneyInt->getValue());
        self::assertEquals(100, $MoneyInt->getValue(true));

        // Округление до двух знаков

        $MoneyFloat = new Money(123.124);
        self::assertEquals(123.12, $MoneyFloat->getValue());
        self::assertEquals(12312, $MoneyFloat->getValue(true));

        $MoneyFloat = new Money(123.125);
        self::assertEquals(123.13, $MoneyFloat->getValue());
        self::assertEquals(12313, $MoneyFloat->getValue(true));


        // Преобразуем строки

        $MoneyStringReplace = new Money('123,125');
        self::assertEquals(123.13, $MoneyStringReplace->getValue());
        self::assertEquals(12313, $MoneyStringReplace->getValue(true));

        $MoneyString = new Money('123.125');
        self::assertEquals(123.13, $MoneyString->getValue());
        self::assertEquals(12313, $MoneyString->getValue(true));


        $MoneySelf = new Money($MoneyString);
        self::assertEquals(123.13, $MoneySelf->getValue());
        self::assertEquals(12313, $MoneySelf->getValue(true));


        // Математические действия

        $MoneySelf = $MoneySelf->add($MoneyString);
        self::assertEquals(246.26, $MoneySelf->getValue());
        self::assertEquals(24626, $MoneySelf->getValue(true));


        $MoneySelf = $MoneySelf->sub($MoneyString);
        self::assertEquals(123.13, $MoneySelf->getValue());
        self::assertEquals(12313, $MoneySelf->getValue(true));


        /** DIVISION */

        $MoneyStringReplace = new Money('123121', true);
        self::assertEquals(1231.21, $MoneyStringReplace->getValue());
        self::assertEquals(123121, $MoneyStringReplace->getValue(true));

        $MoneyString = new Money('123122', true);
        self::assertEquals(1231.22, $MoneyString->getValue());
        self::assertEquals(123122, $MoneyString->getValue(true));

        $MoneyFloat = new Money(123123, true);
        self::assertEquals(1231.23, $MoneyFloat->getValue());
        self::assertEquals(123123, $MoneyFloat->getValue(true));


        $MoneyFloat = new Money(123123, true);

        self::assertEquals(1231, $MoneyFloat->getRoundValue());
        self::assertEquals(1230, $MoneyFloat->getRoundValue(10));
        self::assertEquals(1200, $MoneyFloat->getRoundValue(100));
        self::assertEquals(1000, $MoneyFloat->getRoundValue(1000));


        $MoneyFloat = new Money(100);
        $MoneyFloat->applyPercent(10, false); // false - без округления
        self::assertEquals(110, $MoneyFloat->getRoundValue(10));

        $MoneyFloat = new Money(100);
        $MoneyFloat->applyPercent(10);
        self::assertEquals(110, $MoneyFloat->getRoundValue(10));


        $MoneyFloat = new Money(100);
        $MoneyFloat->applyPercent(10.1, false); // false - без округления
        self::assertEquals(110.1, $MoneyFloat->getValue());

        $MoneyFloat = new Money(100);
        $MoneyFloat->applyPercent(10.1, true); // true - округляем
        self::assertEquals(110.0, $MoneyFloat->getValue());


        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyPercent(10.1, false); // false - без округления
        self::assertEquals(1101, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyPercent(10.1); // true - округляем
        self::assertEquals(1101, $MoneyFloat->getValue());


        $MoneyFloat = new Money(100);
        $MoneyFloat->applyPercent(-10, false); // false - без округления
        self::assertEquals(90, $MoneyFloat->getRoundValue(10));

        $MoneyFloat = new Money(100);
        $MoneyFloat->applyPercent(-10); // true - округляем
        self::assertEquals(90, $MoneyFloat->getRoundValue(10));


        $MoneyFloat = new Money(100);
        $MoneyFloat->applyPercent(-10.1, false); // false - без округления
        self::assertEquals(89.9, $MoneyFloat->getValue());

        $MoneyFloat = new Money(100);
        $MoneyFloat->applyPercent(-10.1); // true - округляем
        self::assertEquals(90.0, $MoneyFloat->getValue());




        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyPercent(-10.11, false); // false - без округления
        self::assertEquals(898.9, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyPercent(-10.11); // true - округляем
        self::assertEquals(899, $MoneyFloat->getValue());


        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyNumeric(-10.1);
        self::assertEquals(989.9, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyNumeric(-10.11);
        self::assertEquals(989.89, $MoneyFloat->getValue());


        // При передаче чисел - не округляем!

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyNumeric(10.1);
        self::assertEquals(1010.1, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyNumeric(10.11);
        self::assertEquals(1010.11, $MoneyFloat->getValue());





        $MoneyFloat = new Money(100);
        $MoneyFloat->applyString('10.11%', false); // false - без округления
        self::assertEquals(110.11, $MoneyFloat->getValue());

        $MoneyFloat = new Money(100);
        $MoneyFloat->applyString('10.11%', true); // округляем
        self::assertEquals(110, $MoneyFloat->getValue());


        $MoneyFloat = new Money(100);
        $MoneyFloat->applyString('-10.11%', false); // false - без округления
        self::assertEquals(89.89, $MoneyFloat->getValue());


        $MoneyFloat = new Money(100);
        $MoneyFloat->applyString('-10.11%', true); // округляем
        self::assertEquals(90, $MoneyFloat->getValue());


        /** @note При передаче чисел - результат не округляется! */

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString(20.11, false); // false - без округления
        self::assertEquals(1020.11, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString(20.11); // округляем
        self::assertEquals(1020.11, $MoneyFloat->getValue());


        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString(-20.1, false);
        self::assertEquals(979.9, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString(-20.1);
        self::assertEquals(979.9, $MoneyFloat->getValue());


        /** empty значения не применяют скидку */

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString(null);
        self::assertEquals(1000, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString(false);
        self::assertEquals(1000, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString('');
        self::assertEquals(1000, $MoneyFloat->getValue());

        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString('0');
        self::assertEquals(1000, $MoneyFloat->getValue());


        $MoneyFloat = new Money(1000);
        $MoneyFloat->applyString(0);
        self::assertEquals(1000, $MoneyFloat->getValue());


        /** Performance */

        //        // Пример использования
        //        $executionTime = $this->benchmark(function() {
        //            for ($i = 0; $i < 1000000; $i++) {
        //                $MoneyFloat = new Money(1000);
        //                $MoneyFloat->applyString('10.11%');
        //                $result = $MoneyFloat->getValue();
        //            }
        //        });
        //
        //        echo "Время выполнения: " . $executionTime . " секунд.";


    }


    function benchmark(callable $function)
    {
        // Запоминаем время начала выполнения
        $startTime = microtime(true);

        // Вызываем переданную функцию
        $function();

        // Запоминаем время окончания выполнения
        $endTime = microtime(true);

        // Вычисляем разницу во времени
        return $endTime - $startTime;
    }

}