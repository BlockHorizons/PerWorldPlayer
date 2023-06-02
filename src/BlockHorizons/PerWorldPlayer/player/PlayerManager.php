<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\player;

use BlockHorizons\PerWorldPlayer\Loader;
use pocketmine\player\Player;

final class PlayerManager{

	/** @var PlayerInstance[] */
	private array $players = [];

	public function __construct(
		readonly private Loader $loader
	){
		$this->loader->getServer()->getPluginManager()->registerEvents(new PlayerListener($this), $this->loader);
	}

	public function onPlayerJoin(Player $player) : void{
		$this->players[$player->getId()] = new PlayerInstance($this->loader, $player);
	}

	public function onPlayerQuit(Player $player) : void{
		if(isset($this->players[$id = $player->getId()])){ // TODO: Load players during PlayerLoginEvent instead of PlayerJoinEvent to avoid this isset() check
			$this->players[$id]->close();
			unset($this->players[$id]);
		}
	}

	public function get(Player $player) : PlayerInstance{
		return $this->players[$player->getId()];
	}

	public function getNullable(Player $player) : ?PlayerInstance{
		return $this->players[$player->getId()] ?? null;
	}
}