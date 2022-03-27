<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\data;

use BlockHorizons\PerWorldPlayer\Loader;
use Closure;
use pocketmine\player\Player;
use ReflectionClass;

final class SaveDataManager{

	/** @var Closure[] */
	private static $injectors = [];

	public static function init(Loader $loader) : void{
		self::registerInjector(SaveDataIds::NORMAL_INVENTORY, static function(PlayerWorldData $data, Player $player) : void{ $player->getInventory()->setContents($data->inventory); });
		self::registerInjector(SaveDataIds::ARMOR_INVENTORY, static function(PlayerWorldData $data, Player $player) : void{ $player->getArmorInventory()->setContents($data->armor_inventory); });
		self::registerInjector(SaveDataIds::ENDER_INVENTORY, static function(PlayerWorldData $data, Player $player) : void{ $player->getEnderInventory()->setContents($data->ender_inventory); });
		self::registerInjector(SaveDataIds::HEALTH, static function(PlayerWorldData $data, Player $player) : void{ $player->setHealth($data->health); });
		self::registerInjector(SaveDataIds::EFFECTS, static function(PlayerWorldData $data, Player $player) : void{
			$effects = $player->getEffects();
			$effects->clear();
			foreach($data->effects as $effect) {
				$effects->add($effect);
			}
		});
		self::registerInjector(SaveDataIds::GAMEMODE, static function(PlayerWorldData $data, Player $player) : void{ $player->setGamemode($data->gamemode); });
		self::registerInjector(SaveDataIds::EXPERIENCE, static function(PlayerWorldData $data, Player $player) : void{ $player->getXpManager()->setCurrentTotalXp($data->experience); });
		self::registerInjector(SaveDataIds::FOOD_HUNGER, static function(PlayerWorldData $data, Player $player) : void{ $player->getHungerManager()->setFood($data->food); });
		self::registerInjector(SaveDataIds::FOOD_SATURATION, static function(PlayerWorldData $data, Player $player) : void{ $player->getHungerManager()->setSaturation($data->saturation); });
		self::registerInjector(SaveDataIds::FOOD_EXHAUSTION, static function(PlayerWorldData $data, Player $player) : void{ $player->getHungerManager()->setExhaustion($data->exhaustion); });

		$config = $loader->getConfig();
		$disabled = [];
		foreach((new ReflectionClass(SaveDataIds::class))->getConstants() as $identifier){
			if(!$config->getNested("Save-Data." . $identifier)){
				unset(self::$injectors[$identifier]);
				$disabled[] = $identifier;
			}
		}

		if(count($disabled) > 0){
			$loader->getLogger()->debug("Disabled Save-Data for: " . implode(", ", $disabled));
		}
	}

	/**
	 * @param string $identifier
	 * @param Closure $injector
	 * @phpstan=param Closure(PlayerWorldData $data, Player $player) : void $injector
	 */
	private static function registerInjector(string $identifier, Closure $injector) : void{
		self::$injectors[$identifier] = $injector;
	}

	public static function inject(PlayerWorldData $data, Player $player) : void{
		foreach(self::$injectors as $injector){
			$injector($data, $player);
		}
	}
}