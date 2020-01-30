<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

final class MySQLWorldDatabase extends LibasynqlWorldDatabase{

	protected function fetchBinaryString(string $string) : string{
		return $string;
	}

	protected function saveBinaryString(string $string) : string{
		return $string;
	}
}