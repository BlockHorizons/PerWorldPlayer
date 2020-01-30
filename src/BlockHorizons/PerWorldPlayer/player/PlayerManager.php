<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\player;

use BlockHorizons\PerWorldPlayer\Loader;
use pocketmine\Player;

final class PlayerManager{

	/** @var PlayerInstance[] */
	private $players = [];

	public function __construct(Loader $plugin){
		$plugin->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $plugin);
	}

	public function onPlayerJoin(Player $player) : void{
		$this->players[$player->getId()] = new PlayerInstance();
	}

	public function onPlayerQuit(Player $player) : void{
		unset($this->players[$player->getId()]);
	}

	public function get(Player $player) : PlayerInstance{
		return $this->players[$player->getId()];
	}
}