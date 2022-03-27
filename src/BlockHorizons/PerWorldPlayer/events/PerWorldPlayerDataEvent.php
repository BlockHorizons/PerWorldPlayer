<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\events;

use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\WorldInstance;
use pocketmine\event\Event;
use pocketmine\player\Player;

abstract class PerWorldPlayerDataEvent extends Event{

	/** @var Player */
	private $player;

	/** @var WorldInstance */
	private $worldInstance;

	/** @var PlayerWorldData */
	private $playerWorldData;

	public function __construct(Player $player, WorldInstance $worldInstance, PlayerWorldData $playerWorldData){
		$this->player = $player;
		$this->worldInstance = $worldInstance;
		$this->playerWorldData = $playerWorldData;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getWorldInstance() : WorldInstance{
		return $this->worldInstance;
	}

	public function getPlayerWorldData() : PlayerWorldData{
		return $this->playerWorldData;
	}

	public function setPlayerWorldData(PlayerWorldData $playerWorldData) : void{
		$this->playerWorldData = $playerWorldData;
	}
}