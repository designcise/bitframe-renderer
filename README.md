# BitFrame\Renderer

[![codecov](https://codecov.io/gh/designcise/bitframe-renderer/branch/master/graph/badge.svg)](https://codecov.io/gh/designcise/bitframe-renderer)
[![Build Status](https://travis-ci.org/designcise/bitframe-renderer.svg?branch=master)](https://travis-ci.org/designcise/bitframe-renderer)

Simple PHP-based templating engine.

## Installation

Install using composer:

```
$ composer require designcise/bitframe-renderer
```

Please note that this package requires PHP 7.4.0 or newer.

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

To run the tests you can use the following command:

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

Complete documentation for will be available soon.

## License

Please see [License File](LICENSE.md) for licensing information.
