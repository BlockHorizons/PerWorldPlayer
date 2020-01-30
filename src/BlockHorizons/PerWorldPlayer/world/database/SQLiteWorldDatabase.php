<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

final class SQLiteWorldDatabase extends LibasynqlWorldDatabase{

	protected function fetchBinaryString(string $string) : string{
		return hex2bin($string);
	}

	protected function saveBinaryString(string $string) : string{
		return bin2hex($string);
	}
}