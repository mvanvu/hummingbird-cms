<?php

use MaiVu\Php\Filter;

require_once dirname(__DIR__) . '/src/Filter.php';

// Example
$source = '<h1>Hello World!</h1>';

// Return 'Hello World!'
$filtered = Filter::clean($source, 'string');

// Source array
$source = [
	'<h1>Hello World!</h1>',
	'<h1>Hello Vietnam!</h1>',
];

// Return ['Hello World!', 'Hello VietNam!']
echo '<pre>' . print_r(Filter::clean($source, 'string:array'), true) . '</pre>';

// Multi-type
$source = '  <h1>Hello World!</h1>  ';

// Return 'Hello World!'
echo '<pre>' . print_r(Filter::clean($source, ['string', 'trim']), true) . '</pre>';

class CustomFilter extends Filter
{
	public static function arrayInteger($value)
	{
		return static::clean($value, 'int:array');
	}

}

// Return '[1, 2, 3]'
echo '<pre>' . print_r(CustomFilter::clean(['1abc2', '2b', 3], 'arrayInteger'), true) . '</pre>';