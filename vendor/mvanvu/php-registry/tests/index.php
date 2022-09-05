<?php

use MaiVu\Php\Registry;

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Session
$session = Registry::session();
$session->start();
$session->set('foo', 'This is a session value of foo');

echo '<pre>' . print_r($session->getFlash('foo'), true) . '</pre>';
echo '<pre>' . print_r($session->get('foo', 'Session unsetted'), true) . '</pre>';

$request = Registry::request();

echo '<pre>' . print_r($request->get->toArray(), true) . '</pre>';
echo '<pre>' . print_r($request->post->toArray(), true) . '</pre>';
echo '<pre>' . print_r($request->server->toArray(), true) . '</pre>';
echo '<pre>' . print_r($request->files->toArray(), true) . '</pre>';

class CustomToArray
{
	public $data = ['foo' => 'bar'];

	public function toArray()
	{
		return $this->data;
	}
}

$registry = new Registry(new CustomToArray);
echo '<pre>' . print_r($registry->toArray(), true) . '</pre>';
