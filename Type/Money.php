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

namespace BaksDev\Reference\Money\Type;

use InvalidArgumentException;

final class Money
{
    public const string TYPE = 'money_type';

    public const float TEST = 100.25;

    private int|float|null $value;


    /**
     * $division = true - если число целое не разделено предварительно на 100 и не имеет плавающую точку - применяем деление
     */
    public function __construct(
        Money|int|float|string|null $value,
        bool $division = false // true - если нужно разделить на 100
    )
    {
        if(is_string($value) || is_int($value))
        {
            if(is_string($value))
            {
                $value = str_replace(',', '.', $value);
            }

            $value = (float) $value;
        }

        if($value instanceof self)
        {
            $value = $value->getValue();
        }

        /** Если стоимость больше 0 и division === true - приводим к копейкам умножая на 0.01 (деление на 100) */
        if(false === empty($value) && $division === true)
        {
            $value *= 0.01;
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }

    /**
     * $multiply = true - применить умножение на 100 для перевода копеек в целое число
     */
    public function getValue($multiply = false): int|float
    {
        if(empty($this->value))
        {
            return 0;
        }

        $value = round($this->value, 2);

        if($multiply === true)
        {
            $value *= 100;
        }

        return $value;
    }

    /** Умножаем сумму на количество */
    public function multiplication(int $count): self
    {
        $this->value *= $count;
        return $this;
    }

    /**
     * Приводит отрицательное число к 0, либо положительный результат
     */
    public function getOnlyPositive(): int|float|null
    {
        return round(max(0, $this->value), 2);
    }

    /**
     * Приводит отрицательное число к 0, округляет до целого числа (без копеек)
     *
     * если precision = 10 - округлить до десяток
     * пример: 1231.0 -> 1230
     *
     * если precision = 100 - округлить до соток
     * пример: 1231.0 -> 1200
     *
     * если precision = 1000 - округлить до тысяч
     * пример: 1231.0 -> 1000
     */
    public function getRoundValue($precision = null): int
    {
        $round = match ($precision)
        {
            10 => -1,
            100 => -2,
            1000 => -3,
            default => 0,
        };

        return (int) round(max(0, $this->value), $round);
    }


    public function add(self $money): self
    {
        if($money->getValue() < 0)
        {
            throw new InvalidArgumentException('Для суммы значение должно быть строго положительным');
        }

        $current = $this->getValue(true);
        $add = $money->getValue(true);
        $this->value = ($current + $add) * 0.01;

        return $this;
    }


    public function sub(self $money): self
    {
        if($money->getValue() < 0)
        {
            throw new InvalidArgumentException('Для разности значение должно быть строго положительным');
        }

        $current = $this->getValue(true);
        $sub = $money->getValue(true);

        $this->value = ($current - $sub) * 0.01;

        return $this;
    }


    /**
     * Метод суммирует при положительном $money и разность при отрицательном
     */
    public function mathematical(self $money): self
    {
        $current = $this->getValue(true);

        $value = $money->getValue(true);

        if(empty($value))
        {
            return $this;
        }

        if($value > 0)
        {
            $this->value = ($current + $value) * 0.01;
            return $this;
        }

        if($value < 0)
        {
            $value = abs($value);
            $this->value = ($current - $value) * 0.01;
            return $this;
        }

        return $this;
    }


    /**
     * Метод парсит строку и добавляет к цене
     *
     * Положительное либо отрицательное число в рублях, либо с процентом, пример:
     * 100.1
     * -100.1
     * 10.1%
     * -10.1%
     */
    public function applyString(int|float|string|null|false $number, bool $round = true): self
    {
        if(empty($number))
        {
            return $this;
        }

        $isPercent = false;

        /** Если в строке есть знак процента - применяется процент */

        if(str_contains($number, '%'))
        {
            $number = (float) filter_var($number, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $isPercent = true;
        }

        return $isPercent ? $this->applyPercent($number, $round) : $this->applyNumeric($number);

    }


    /**
     * Метод применяет процент к сумме
     */
    public function applyPercent(int|float $percent, bool $round = true): self
    {
        if($percent < -100 || $percent > 100)
        {
            throw new InvalidArgumentException('Для расчета процента значение должно быть от 0 до 100');
        }

        // Получаем сумму * 100
        $current = $this->getValue(true);

        // Определяем результат процента от суммы
        $discount = ($current * 0.01 * $percent);

        // При необходимости до целых чисел
        if(true === $round)
        {
            $discount *= 0.01; //
            $discount = round($discount, 0);
            $discount *= 100;

        }

        // Т.к. сумма и процент без копеек - приводим результат к копейкам
        $this->value = ($current + $discount) * 0.01;

        return $this;
    }


    /**
     * Метод применяет число к сумме
     */
    public function applyNumeric(int|float $number): self
    {
        $current = $this->getValue(true);

        $discount = $number * 100;

        $this->value = ($current + $discount) * 0.01;

        return $this;
    }


    /**
     * Метод возвращает процент от суммы (всегда положительный)
     */
    public function percent(int|float $percent): self
    {
        if($percent < -100 || $percent > 100)
        {
            throw new InvalidArgumentException('Для расчета процента значение должно быть от 0 до 100');
        }

        $current = $this->getValue(true);

        $discount = ($current * 0.01 * $percent) * 0.01;

        return new self($discount);
    }


    public function equals(mixed $money): bool
    {
        $money = new self($money);
        return $this->getValue(true) === $money->getValue(true);
    }


}
