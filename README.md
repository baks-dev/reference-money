# BaksDev Money

![Version](https://img.shields.io/badge/version-6.2-blue) ![php 8.1+](https://img.shields.io/badge/php-min%208.1-red.svg)

Библиотека хранения денег Doctrine

Сюбая сумма, предже чем сохраниться в дазе данных, умножается на 100 и сохраняет число в виде строки. При получении из
базы данных, это число делится на 100, т.о. исключается арифметическая проблема чисел с плавающей точкой, которая
приводят к неверным результатам.

## Установка

``` bash
$ composer require baks-dev/reference-money
```

## Журнал изменений ![Changelog](https://img.shields.io/badge/changelog-yellow)

О том, что изменилось за последнее время, обратитесь к [CHANGELOG](CHANGELOG.md) за дополнительной информацией.

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.


