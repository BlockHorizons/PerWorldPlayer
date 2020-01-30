<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

use BlockHorizons\PerWorldPlayer\world\WorldInstance;
use Closure;
use pocketmine\Player;

interface WorldDatabase{

	/**
	 * Loads player inventory from a given world.
	 *
	 * @param WorldInstance $world
	 * @param Player $player
	 * @param Closure $onLoad
	 * @phpstan-param Closure(PlayerWorldData $data) : void $onLoad
	 */
	public function load(WorldInstance $world, Player $player, Closure $onLoad) : void;

	/**
	 * Saves player inventory in a given world.
	 *
	 * @param WorldInstance $world
	 * @param Player $player
	 * @param bool $quit whether the player quit the server.
	 */
	public function save(WorldInstance $world, Player $player, bool $quit) : void;

	/**
	 * Called when plugin disables to close any open resources and other stuff.
	 */
	public function close() : void;
}