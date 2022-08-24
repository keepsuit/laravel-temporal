# Laravel temporal.io 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/keepsuit/laravel-temporal.svg?style=flat-square)](https://packagist.org/packages/keepsuit/laravel-temporal)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/keepsuit/laravel-temporal/run-tests?label=tests)](https://github.com/keepsuit/laravel-temporal/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/keepsuit/laravel-temporal/Fix%20PHP%20code%20style%20issues?label=code%20style)](https://github.com/keepsuit/laravel-temporal/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/keepsuit/laravel-temporal.svg?style=flat-square)](https://packagist.org/packages/keepsuit/laravel-temporal)

Laravel integration with temporal.io

## Installation

You can install the package via composer:

```bash
composer require keepsuit/laravel-temporal
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-temporal-config"
```

This is the contents of the published config file:

```php
return [
];
```

[//]: # (## Usage)

[//]: # ()
[//]: # (```php)

[//]: # ($laravelTemporal = new Keepsuit\LaravelTemporal&#40;&#41;;)

[//]: # (echo $laravelTemporal->echoPhrase&#40;'Hello, Keepsuit!'&#41;;)

[//]: # (```)

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Fabio Capucci](https://github.com/keepsuit)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
