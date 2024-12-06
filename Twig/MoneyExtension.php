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

namespace BaksDev\Reference\Money\Twig;

use BaksDev\Reference\Money\Type\Money;
use NumberFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MoneyExtension extends AbstractExtension
{

    private TranslatorInterface $translator;


    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }


    public function getFunctions(): array
    {
        return [
            new TwigFunction('money', [$this, 'call']),
        ];
    }


    public function call(Money|float|null $money, string|false|null $from = 'RUR', ?string $to = null)
    {

        if(empty($money))
        {
            return null;
        }

        if($money instanceof Money)
        {
            $money = ($money->getValue() * 1);
        }
        else
        {
            $money = ($money * 1 / 100);
        }

        if(empty($money))
        {
            return null;
        }

        if(empty($to))
        {
            $to = $from;
        }

        if($from !== $to)
        {
            /** TODO:Конвертируем валюту */
        }

        $fmt = new NumberFormatter($this->translator->getLocale(), NumberFormatter::CURRENCY);
        $fmt->setAttribute(NumberFormatter::FRACTION_DIGITS, 2);

        if(false === $from)
        {
            $fmt->setPattern("#,##0.00\u{A0}");
        }

        return str_replace([',00', '.00'], '', $fmt->formatCurrency($money, $to ?: 'RUR'));
    }

}