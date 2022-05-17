<div align="center">
    <a href="#"><img src="logo-linna-96.png" alt="Linna Logo"></a>
</div>

<br/>

<div align="center">
    <a href="#"><img src="logo-http-message.png" alt="Linna dotenv Logo"></a>
</div>

<br/>

<div align="center">

[![Tests](https://github.com/linna/http-message/actions/workflows/tests.yml/badge.svg)](https://github.com/linna/http-message/actions/workflows/tests.yml)
[![PHP 8.1](https://img.shields.io/badge/PHP-8.1-8892BF.svg)](http://php.net)

</div>

> **_NOTE:_**  Code porting to PHP 8.1 ongoing.

# About
This package provide a implementations for [PSR-7 HTTP message interfaces](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-7-http-message.md). Instead of
standard psr http message, classes in package use a [strict typed fork](https://github.com/s3b4stian/http-message).

# Status of the work
Message, Stream, Request and Uri classes works, working on others :)

# Requirements
This package require php 7.2

# Installation
With composer:
```
composer require linna/http-message
```
