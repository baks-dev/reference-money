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

namespace BaksDev\Reference\Money\Twig;

use NumberFormatter;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class MoneyToWordExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('money_word', [$this, 'numberToWords']),
        ];
    }


    public function numberToWords(string|int|float $number): string
    {
        $float = 0;

        if(is_float($number))
        {
            $float = $number - floor($number);
        }

        $number = (int) $number;


        return $this->toString($number).' '.($this->toString($float) ? ' копеек' : 'ноль копеек');
    }



    public function toString(int $number): string
    {
        // обозначаем словарь в виде статической переменной функции, чтобы
        // при повторном использовании функции его не определять заново
        static $dic = array(

            // словарь необходимых чисел
            array(
                -2	=> 'две',
                -1	=> 'одна',
                1	=> 'один',
                2	=> 'два',
                3	=> 'три',
                4	=> 'четыре',
                5	=> 'пять',
                6	=> 'шесть',
                7	=> 'семь',
                8	=> 'восемь',
                9	=> 'девять',
                10	=> 'десять',
                11	=> 'одиннадцать',
                12	=> 'двенадцать',
                13	=> 'тринадцать',
                14	=> 'четырнадцать' ,
                15	=> 'пятнадцать',
                16	=> 'шестнадцать',
                17	=> 'семнадцать',
                18	=> 'восемнадцать',
                19	=> 'девятнадцать',
                20	=> 'двадцать',
                30	=> 'тридцать',
                40	=> 'сорок',
                50	=> 'пятьдесят',
                60	=> 'шестьдесят',
                70	=> 'семьдесят',
                80	=> 'восемьдесят',
                90	=> 'девяносто',
                100	=> 'сто',
                200	=> 'двести',
                300	=> 'триста',
                400	=> 'четыреста',
                500	=> 'пятьсот',
                600	=> 'шестьсот',
                700	=> 'семьсот',
                800	=> 'восемьсот',
                900	=> 'девятьсот'
            ),

            // словарь порядков со склонениями для плюрализации
            array(
                array('рубль', 'рубля', 'рублей'),
                array('тысяча', 'тысячи', 'тысяч'),
                array('миллион', 'миллиона', 'миллионов'),
                array('миллиард', 'миллиарда', 'миллиардов'),
                array('триллион', 'триллиона', 'триллионов'),
                array('квадриллион', 'квадриллиона', 'квадриллионов'),
                // квинтиллион, секстиллион и т.д.
            ),

            // карта плюрализации
            array(
                2, 0, 1, 1, 1, 2
            )
        );


        // обозначаем переменную в которую будем писать сгенерированный текст
        $string = array();

        // дополняем число нулями слева до количества цифр кратного трем,
        // например 1234, преобразуется в 001234
        $number = str_pad($number, ceil(strlen($number)/3)*3, 0, STR_PAD_LEFT);

        // разбиваем число на части из 3 цифр (порядки) и инвертируем порядок частей,
        // т.к. мы не знаем максимальный порядок числа и будем бежать снизу
        // единицы, тысячи, миллионы и т.д.
        $parts = array_reverse(str_split($number,3));

        // бежим по каждой части
        foreach($parts as $i=>$part) {

            // если часть не равна нулю, нам надо преобразовать ее в текст
            if($part>0) {

                // обозначаем переменную в которую будем писать составные числа для текущей части
                $digits = array();

                // если число треххзначное, запоминаем количество сотен
                if($part>99) {
                    $digits[] = floor($part/100)*100;
                }

                // если последние 2 цифры не равны нулю, продолжаем искать составные числа
                // (данный блок прокомментирую при необходимости)
                if($mod1=$part%100) {
                    $mod2 = $part%10;
                    $flag = $i==1 && $mod1!=11 && $mod1!=12 && $mod2<3 ? -1 : 1;
                    if($mod1<20 || !$mod2) {
                        $digits[] = $flag*$mod1;
                    } else {
                        $digits[] = floor($mod1/10)*10;
                        $digits[] = $flag*$mod2;
                    }
                }

                // берем последнее составное число, для плюрализации
                $last = abs(end($digits));

                // преобразуем все составные числа в слова
                foreach($digits as $j=>$digit) {
                    $digits[$j] = $dic[0][$digit];
                }

                // добавляем обозначение порядка или валюту
                $digits[] = $dic[1][$i][(($last%=100)>4 && $last<20) ? 2 : $dic[2][min($last%10,5)]];

                // объединяем составные числа в единый текст и добавляем в переменную, которую вернет функция
                array_unshift($string, join(' ', $digits));
            }
        }

        // преобразуем переменную в текст и возвращаем из функции, ура!
        return implode(' ', $string);

    }


    public function _toString(int $number): string
    {
        $ones = [
            0 => '', 1 => 'один', 2 => 'два', 3 => 'три', 4 => 'четыре', 5 => 'пять',
            6 => 'шесть', 7 => 'семь', 8 => 'восемь', 9 => 'девять', 10 => 'десять',
            11 => 'одиннадцать', 12 => 'двенадцать', 13 => 'тринадцать', 14 => 'четырнадцать',
            15 => 'пятнадцать', 16 => 'шестнадцать', 17 => 'семнадцать', 18 => 'восемнадцать',
            19 => 'девятнадцать'
        ];

        $tens = [
            2 => 'двадцать', 3 => 'тридцать', 4 => 'сорок', 5 => 'пятьдесят',
            6 => 'шестьдесят', 7 => 'семьдесят', 8 => 'восемьдесят', 9 => 'девяносто'
        ];

        $hundreds = [
            1 => 'сто', 2 => 'двести', 3 => 'триста', 4 => 'четыреста', 5 => 'пятьсот',
            6 => 'шестьсот', 7 => 'семьсот', 8 => 'восемьсот', 9 => 'девятьсот'
        ];


        $text = '';

        if($number === 0)
        {
            return 'ноль';
        }

        // Преобразуем разряды в текстовое представление
        $units = array('', 'тысяч', 'миллион', 'миллиард', 'триллион', 'квадриллион'); // Добавьте сколько нужно разрядов

        $i = 0;
        while($number > 0)
        {
            $n = $number % 1000;

            if($n)
            {

//                if($n < 100)
//                {
//                    if($n < 20)
//                    {
//                        $text = $ones[$n];
//                    }
//                    else
//                    {
//                        $text = $tens[$n / 10].($n % 10 ? ' '.$ones[$n % 10] : '');
//                    }
//                }
//                else
//                {
//                    $text = $hundreds[$n / 100].' '.$this->numberToWords($n % 100);
//                }
//
//                $text .= ' '.$units[$i].' '.$text;


                $text = ($n < 100 ? ($n < 20 ? $ones[$n] : $tens[$n / 10] . ($n % 10 ? ' ' . $ones[$n % 10] : '')) : $hundreds[$n / 100] . ' ' . $this->numberToWords($n % 100)) . ' ' . $units[$i] . ' ' . $text;
            }

            $number = (int) ($number / 1000);

            $i++;
        }

        return trim($text);

    }

}