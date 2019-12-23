<?php

use PHPUnit\Framework\TestCase;
use MaiVu\Hummingbird\Lib\Helper\Date;
use MaiVu\Hummingbird\Lib\Helper\User;

class DateHelperTest extends TestCase
{
	public function testGetInstance()
	{
		$this->assertInstanceOf(Date::class, Date::getInstance('now', 'Asia/Ho_Chi_Minh'));
	}

	public function testConvertTimezone()
	{
		$dateInUTC = '2019-12-19 08:04:00';
		$dateInHCM = '2019-12-19 15:04:00';
		$date      = Date::getInstance($dateInUTC, 'UTC');
		User::getInstance()->setParams(['timezone' => 'Asia/Ho_Chi_Minh']);
		$this->assertEquals($dateInHCM, $date->toFormat('Y-m-d H:i:s'));
	}
}