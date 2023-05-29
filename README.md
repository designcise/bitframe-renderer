# BitFrame\Renderer

[![CI](https://github.com/designcise/bitframe-renderer/actions/workflows/ci.yml/badge.svg)](https://github.com/designcise/bitframe-renderer/actions/workflows/ci.yml)
[![Maintainability](https://api.codeclimate.com/v1/badges/d5c3dc2fadc44ffea89c/maintainability)](https://codeclimate.com/github/designcise/bitframe-renderer/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/d5c3dc2fadc44ffea89c/test_coverage)](https://codeclimate.com/github/designcise/bitframe-renderer/test_coverage)

Simple PHP-based templating engine.

## Installation

Install using composer:

```
$ composer require designcise/bitframe-renderer
```

Please note that this package requires PHP 8.2 or newer.

## Usage Example

```php
use BitFrame\Renderer\Renderer;

$renderer = new Renderer([
    'main' => __DIR__ . '/tpl/',
], 'tpl');

$renderer->withData(['foo' => 'bar']);

$output = $renderer->render('main::test', ['baz' => 'qux']);
```

```html
<!-- ~/tpl/test.tpl -->
<p><?= $foo; ?> <?= $baz; ?></p>
```

## Tests

To run the tests you can use the following commands:

| Command          | Type            |
| ---------------- |:---------------:|
| `composer test`  | PHPUnit tests   |
| `composer style` | CodeSniffer     |
| `composer md`    | MessDetector    |
| `composer check` | PHPStan         |

## Contributing

* File issues at https://github.com/designcise/bitframe-renderer/issues
* Issue patches to https://github.com/designcise/bitframe-renderer/pulls

## Documentation

Complete documentation for v3 will be available soon.

## License

Please see [License File](LICENSE.md) for licensing information.
