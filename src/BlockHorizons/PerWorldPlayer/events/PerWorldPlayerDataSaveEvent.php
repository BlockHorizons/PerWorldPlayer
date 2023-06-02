<?php
declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\events;

use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\WorldInstance;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class PerWorldPlayerDataSaveEvent extends PerWorldPlayerDataEvent implements Cancellable{
	use CancellableTrait;

	public const CAUSE_WORLD_CHANGE = 0;
	public const CAUSE_PLAYER_QUIT = 1;
	public const CAUSE_CUSTOM = 2;

	readonly public int $cause;

	public function __construct(Player $player, WorldInstance $worldInstance, PlayerWorldData $playerWorldData, int $cause){
		parent::__construct($player, $worldInstance, $playerWorldData);
		$this->cause = $cause;
	}

	public function getCause() : int{
		return $this->cause;
	}
}