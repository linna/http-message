![Linna Psr7](logo-psr7.png)
<br/>
<br/>
<br/>
[![Build Status](https://travis-ci.org/linna/psr7.svg?branch=master)](https://travis-ci.org/linna/psr7)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/linna/psr7/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/linna/psr7/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/linna/psr7/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/linna/psr7/?branch=master)
[![StyleCI](https://styleci.io/repos/96924222/shield?branch=master&style=flat)](https://styleci.io/repos/96924222)

# Linna Psr7 implementation
This package provide a implementations for [PSR-7 HTTP message interfaces](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md). Instead of
standard psr http message, classes in package use a [strict typed fork](https://github.com/s3b4stian/http-message).

## Status of the work
Uri class work, but there are some adjustament to do.

## Requirements
This package require php 7.

## Installation
With composer:
```
composer require linna/psr7
```