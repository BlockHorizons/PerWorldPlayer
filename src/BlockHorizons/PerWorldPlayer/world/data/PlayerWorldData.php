<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\data;

use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\Server;

final class PlayerWorldData{

	public static function empty() : PlayerWorldData{
		return self::emptyWithInventory([], [], []);
	}

	public static function emptyWithInventory(array $armor, array $inventory, array $ender) : PlayerWorldData{
		return new self($armor, $inventory, $ender, 20.0, [], Server::getInstance()->getDefaultGamemode(), 0, 20.0, 0.0, 5.0);
	}

	public static function fromPlayer(Player $player) : PlayerWorldData{
		if(!$player->isAlive()){
			return self::emptyWithInventory( // PlayerDeathEvent::getKeepInventory() may not wipe their inventory
				$player->getArmorInventory()->getContents(),
				$player->getInventory()->getContents(),
				$player->getEnderChestInventory()->getContents()
			);
		}

		$effects = [];
		foreach($player->getEffects() as $effect){
			$effects[] = new EffectInstance($effect->getType(), $effect->getDuration(), $effect->getAmplifier(), $effect->isVisible(), $effect->isAmbient(), $effect->getColor());
		}

		return new self(
			$player->getArmorInventory()->getContents(),
			$player->getInventory()->getContents(),
			$player->getEnderChestInventory()->getContents(),
			$player->getHealth(),
			$effects,
			$player->getGamemode(),
			$player->getCurrentTotalXp(),
			$player->getFood(),
			$player->getExhaustion(),
			$player->getSaturation()
		);
	}

	/** @var Item[] */
	public $armor_inventory;

	/** @var Item[] */
	public $inventory;

	/** @var Item[] */
	public $ender_inventory;

	/** @var float */
	public $health;

	/** @var EffectInstance[] */
	public $effects;

	/** @var int */
	public $gamemode;

	/** @var int */
	public $experience;

	/** @var float */
	public $food;

	/** @var float */
	public $exhaustion;

	/** @var float */
	public $saturation;

	public function __construct(
		array $armor,
		array $inventory,
		array $ender,
		float $health,
		array $effects,
		int $gamemode,
		int $experience,
		float $food,
		float $exhaustion,
		float $saturation
	){
		$this->armor_inventory = $armor;
		$this->inventory = $inventory;
		$this->ender_inventory = $ender;
		$this->health = $health;
		$this->effects = $effects;
		$this->gamemode = $gamemode;
		$this->experience = $experience;
		$this->food = $food;
		$this->exhaustion = $exhaustion;
		$this->saturation = $saturation;
	}

	public function inject(Player $player) : void{
		SaveDataManager::inject($this, $player);
	}
}