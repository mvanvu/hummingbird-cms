<?php

namespace MaiVu\Hummingbird\Lib\Helper;

class Database
{
	public static function splitSql($sql)
	{
		$start     = 0;
		$open      = false;
		$comment   = false;
		$endString = '';
		$end       = strlen($sql);
		$queries   = [];
		$query     = '';

		for ($i = 0; $i < $end; $i++)
		{
			$current      = substr($sql, $i, 1);
			$current2     = substr($sql, $i, 2);
			$current3     = substr($sql, $i, 3);
			$lenEndString = strlen($endString);
			$testEnd      = substr($sql, $i, $lenEndString);

			if ($current === '"' || $current === "'" || $current2 === '--'
				|| ($current2 === '/*' && $current3 !== '/*!' && $current3 !== '/*+')
				|| ($current === '#' && $current3 !== '#__')
				|| ($comment && $testEnd === $endString))
			{
				// Check if quoted with previous backslash
				$n = 2;

				while (substr($sql, $i - $n + 1, 1) === '\\' && $n < $i)
				{
					$n++;
				}

				// Not quoted
				if ($n % 2 === 0)
				{
					if ($open)
					{
						if ($testEnd === $endString)
						{
							if ($comment)
							{
								$comment = false;
								if ($lenEndString > 1)
								{
									$i       += ($lenEndString - 1);
									$current = substr($sql, $i, 1);
								}
								$start = $i + 1;
							}
							$open      = false;
							$endString = '';
						}
					}
					else
					{
						$open = true;

						if ($current2 === '--')
						{
							$endString = "\n";
							$comment   = true;
						}
						elseif ($current2 === '/*')
						{
							$endString = '*/';
							$comment   = true;
						}
						elseif ($current === '#')
						{
							$endString = "\n";
							$comment   = true;
						}
						else
						{
							$endString = $current;
						}

						if ($comment && $start < $i)
						{
							$query .= substr($sql, $start, $i - $start);
						}
					}
				}
			}
			if ($comment)
			{
				$start = $i + 1;
			}

			if (($current === ';' && !$open) || $i === $end - 1)
			{
				if ($start <= $i)
				{
					$query .= substr($sql, $start, $i - $start + 1);
				}

				$query = trim($query);

				if ($query)
				{
					if (($i === $end - 1) && ($current !== ';'))
					{
						$query .= ';';
					}

					$queries[] = $query;
				}

				$query = '';
				$start = $i + 1;
			}
		}

		return $queries;
	}
}