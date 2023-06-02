<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\data;

use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\Item;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\Server;

final class PlayerWorldData{

	public static function empty() : PlayerWorldData{
		return self::emptyWithInventory([], [], []);
	}

	/**
	 * @param array<int, Item> $armor
	 * @param array<int, Item> $inventory
	 * @param array<int, Item> $ender
	 * @return self
	 */
	public static function emptyWithInventory(array $armor, array $inventory, array $ender) : PlayerWorldData{
		return new self($armor, $inventory, $ender, 20.0, [], Server::getInstance()->getGamemode(), 0, 20.0, 0.0, 5.0);
	}

	public static function fromPlayer(Player $player) : PlayerWorldData{
		if(!$player->isAlive()){
			return self::emptyWithInventory( // PlayerDeathEvent::getKeepInventory() may not wipe their inventory
				$player->getArmorInventory()->getContents(),
				$player->getInventory()->getContents(),
				$player->getEnderInventory()->getContents()
			);
		}

		$effects = [];
		foreach($player->getEffects()->all() as $effect){
			$effects[] = new EffectInstance($effect->getType(), $effect->getDuration(), $effect->getAmplifier(), $effect->isVisible(), $effect->isAmbient(), $effect->getColor());
		}

		return new self(
			$player->getArmorInventory()->getContents(),
			$player->getInventory()->getContents(),
			$player->getEnderInventory()->getContents(),
			$player->getHealth(),
			$effects,
			$player->getGamemode(),
			$player->getXpManager()->getCurrentTotalXp(),
			$player->getHungerManager()->getFood(),
			$player->getHungerManager()->getExhaustion(),
			$player->getHungerManager()->getSaturation()
		);
	}

	/**
	 * @param array<int, Item> $armor_inventory
	 * @param array<int, Item> $inventory
	 * @param array<int, Item> $ender_inventory
	 * @param float $health
	 * @param EffectInstance[] $effects
	 * @param GameMode $gamemode
	 * @param int $experience
	 * @param float $food
	 * @param float $exhaustion
	 * @param float $saturation
	 */
	public function __construct(
		public array $armor_inventory,
		public array $inventory,
		public array $ender_inventory,
		public float $health,
		public array $effects,
		public GameMode $gamemode,
		public int $experience,
		public float $food,
		public float $exhaustion,
		public float $saturation
	){}
}