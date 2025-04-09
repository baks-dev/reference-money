# BaksDev Reference Money

[![Version](https://img.shields.io/badge/version-7.2.6-blue)](https://github.com/baks-dev/reference-money/releases)
![php 8.4+](https://img.shields.io/badge/php-min%208.4-red.svg)
[![packagist](https://img.shields.io/badge/packagist-green)](https://packagist.org/packages/baks-dev/reference-money)

Библиотека хранения денег Doctrine

Любая сумма, прежде чем сохраниться в базе данных, умножается на 100 и сохраняет число в виде строки. При получении из
базы данных, это число делится на 100, т.о. исключается арифметическая проблема чисел с плавающей точкой, которая
приводят к неверным результатам.

## Установка

``` bash
$ composer require baks-dev/reference-money
```

## Лицензия ![License](https://img.shields.io/badge/MIT-green)

The MIT License (MIT). Обратитесь к [Файлу лицензии](LICENSE.md) за дополнительной информацией.
