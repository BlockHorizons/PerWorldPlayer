<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\WorldInstance;
use Closure;
use pocketmine\Player;

interface WorldDatabase{

	/**
	 * Loads player data from a given world.
	 *
	 * @param WorldInstance $world
	 * @param Player $player
	 * @param Closure $onLoad
	 * @phpstan-param Closure(PlayerWorldData $data) : void $onLoad
	 */
	public function load(WorldInstance $world, Player $player, Closure $onLoad) : void;

	/**
	 * Saves player data in a given world.
	 *
	 * @param WorldInstance $world
	 * @param Player $player
	 * @param PlayerWorldData $data
	 * @param bool $quit whether the player quit the server.
	 */
	public function save(WorldInstance $world, Player $player, PlayerWorldData $data, bool $quit) : void;

	/**
	 * Called when plugin disables to close any open resources and other stuff.
	 */
	public function close() : void;
}