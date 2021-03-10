<?php

namespace App\Console;

use App\Factory\FlyApplication;

interface Fly
{
	public function execute(FlyApplication $app, string $param);
}