<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

use BlockHorizons\PerWorldPlayer\Loader;
use InvalidArgumentException;

final class WorldDatabaseFactory{

	public static function create(Loader $plugin) : WorldDatabase{
		return match(strtolower($type = $plugin->getConfig()->getNested("Database.type"))){
			"mysql" => new MySQLWorldDatabase($plugin),
			"sqlite" => new SQLiteWorldDatabase($plugin),
			default => throw new InvalidArgumentException("Invalid database type " . $type)
		};
	}
}