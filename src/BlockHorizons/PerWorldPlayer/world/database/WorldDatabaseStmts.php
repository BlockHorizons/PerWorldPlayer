<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

interface WorldDatabaseStmts{

	public const INIT = "perworldplayer.init.data";
	public const LOAD = "perworldplayer.load.data";
	public const SAVE = "perworldplayer.save.data";
}