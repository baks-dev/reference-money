<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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
    public const TYPE = 'money_type';

    public const TEST = 1.25;

    private int|float|null $value;


    public function __construct(Money|int|float|string|null $value)
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

        $this->value = $value;
    }

    public function __toString(): string
    {
        return (string) $this->getValue();
    }

    public function getValue(): int|float|null
    {
        return round($this->value, 2);
    }


    /**
     * Приводит отрицательное число к 0, либо положительный результат
     */
    public function getOnlyPositive(): int|float|null
    {
        return round(max(0, $this->value), 2);
    }

    public function add(self $money): self
    {
        if($money->getValue() < 0)
        {
            throw new InvalidArgumentException('Для суммы значение должно быть строго положительным');
        }

        $current = $this->getValue() * 100;
        $add = $money->getValue() * 100;
        $this->value = ($current + $add) / 100;

        return $this;
    }

    public function sub(self $money): self
    {
        if($money->getValue() < 0)
        {
            throw new InvalidArgumentException('Для разности значение должно быть строго положительным');
        }

        $current = $this->getValue() * 100;
        $sub = $money->getValue() * 100;
        $this->value = ($current - $sub) / 100;

        return $this;
    }

    public function equals(mixed $money)
    {
        $money = new self($money);
        return $this->getValue() === $money->getValue();
    }


}