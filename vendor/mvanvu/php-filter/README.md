# Php input filter

## Installation via Composer

```json
{
	"require": {
		"mvanvu/php-filter": "~1.0"
	}
}
```

Alternatively, from the command line:

```sh
composer require mvanvu/php-filter
```

## Usage

``` php
use MaiVu\Php\Filter;

// Syntax
Filter::clean($source, $type);

// Example
$source = '<h1>Hello World!</h1>';

// Return 'Hello World!'
$filtered = Filter::clean($source, 'string');

// Source array
$source = [
	'<h1>Hello World!</h1>',
	'<h1>Hello VietNam!</h1>',
];

// Return ['Hello World!', 'Hello VietNam!']
$filtered = Filter::clean($source, 'string:array');

// Multi-type
$source = '  <h1>Hello World!</h1>  ';

// Return 'Hello World!'
$filtered = Filter::clean($source, ['string', 'trim']);

```

## Add new custom rule
``` php
use MaiVu\Php\Filter;
Filter::setRule('custom', function($value) {
    return $value . ' is filtered by a Closure';
});

$source = 'Hello World!';

// Return 'Hello World! is filtered by a Closure'
$filtered = Filter::clean($source, 'custom');

// The same above
Filter::clean($source, function($value) {
    return $value . ' is filtered by a Closure';
});

```

## Extend filters
```php
use MaiVu\Php\Filter;

class CustomFilter extends Filter
{
	public static function arrayInteger($value)
	{
		return static::clean($value, 'int:array');
	}
}

// Return '[1, 2, 3]'
echo '<pre>' . print_r(CustomFilter::clean(['1abc2', '2b', 3], 'arrayInteger'), true) . '</pre>';
```

## Filter types

* int
* uint (unsigned int)
* float, double
* ufloat, udouble (unsigned float)
* boolean
* alphaNum (alpha number string)
* base64
* string (no HTML tags)
* email
* url
* slug (url alias without slash)
* path (url alias with slash)
* unset (return NULL value)
* jsonEncode
* jsonDecode
* yesNo, yes|no (return 'Y' or 'N')
* YES|NO (return 'YES' or 'NO')
* 1|0 (return 1 or 0)
* inputName (regex /[^a-zA-Z0-9_]/)
* unique (array unique)
* basicHtml

## Fallback default

``` php

if (isset(static::$rules[$type]))
    {
        $result = call_user_func_array(static::$rules[$type], [$value]);
    }
    elseif (is_callable($type))
    {
	    $result = call_user_func_array($type, [$value]);
    }
    elseif (function_exists($type))
    {
        $result = $type($value);
    }
    else
    {
        $result = $value;
    }
}

```