<?php

namespace App\Console;

use App\Factory\FlyApplication;

interface Fly
{
	public function flap(FlyApplication $app, string $param = null);
}