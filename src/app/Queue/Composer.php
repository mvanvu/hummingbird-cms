<?php


namespace App\Queue;

use App\Helper\FileSystem;

class Composer extends QueueAbstract
{
	const VERSION = '2.0.9';

	public function handle(): bool
	{
		$phar = BASE_PATH . '/composer.phar';
		$exec = is_file($phar);

		if (!$exec)
		{
			$exec = FileSystem::download('https://getcomposer.org/download/' . Composer::VERSION . '/composer.phar', $phar);
		}

		if ($exec)
		{
			$cmd = 'COMPOSER_VENDOR_DIR=' . $this->data['pathToJson'] . '/vendor'
				. ' COMPOSER=' . $this->data['pathToJson'] . '/composer.json php ' . $phar
				. ' ' . $this->data['command'];
			exec($cmd);

			return true;
		}

		return false;
	}
}