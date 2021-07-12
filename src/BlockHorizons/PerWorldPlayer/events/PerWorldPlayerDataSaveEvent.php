<?php
declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\events;

use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\WorldInstance;
use pocketmine\event\Cancellable;
use pocketmine\Player;

class PerWorldPlayerDataSaveEvent extends PerWorldPlayerDataEvent implements Cancellable{
	/** @var bool */
	private $quit;

	public function __construct(Player $player, WorldInstance $worldInstance, PlayerWorldData $playerWorldData, bool $quit){
		parent::__construct($player, $worldInstance, $playerWorldData);
		$this->quit = $quit;
	}

	public function hasQuit() : bool{
		return $this->quit;
	}
}