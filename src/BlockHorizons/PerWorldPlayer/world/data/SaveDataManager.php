<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\data;

use BlockHorizons\PerWorldPlayer\Loader;
use Closure;
use pocketmine\player\Player;
use ReflectionClass;

final class SaveDataManager{

	/** @var array<string, Closure(PlayerWorldData $data, Player $player) : void> */
	private array $injectors = [];

	public function __construct(Loader $loader){
		$this->registerInjector(SaveDataIds::NORMAL_INVENTORY, static function(PlayerWorldData $data, Player $player) : void{ $player->getInventory()->setContents($data->inventory); });
		$this->registerInjector(SaveDataIds::ARMOR_INVENTORY, static function(PlayerWorldData $data, Player $player) : void{ $player->getArmorInventory()->setContents($data->armor_inventory); });
		$this->registerInjector(SaveDataIds::ENDER_INVENTORY, static function(PlayerWorldData $data, Player $player) : void{ $player->getEnderInventory()->setContents($data->ender_inventory); });
		$this->registerInjector(SaveDataIds::HEALTH, static function(PlayerWorldData $data, Player $player) : void{ $player->setHealth($data->health); });
		$this->registerInjector(SaveDataIds::EFFECTS, static function(PlayerWorldData $data, Player $player) : void{
			$effects = $player->getEffects();
			$effects->clear();
			foreach($data->effects as $effect) {
				$effects->add($effect);
			}
		});
		$this->registerInjector(SaveDataIds::GAMEMODE, static function(PlayerWorldData $data, Player $player) : void{ $player->setGamemode($data->gamemode); });
		$this->registerInjector(SaveDataIds::EXPERIENCE, static function(PlayerWorldData $data, Player $player) : void{ $player->getXpManager()->setCurrentTotalXp($data->experience); });
		$this->registerInjector(SaveDataIds::FOOD_HUNGER, static function(PlayerWorldData $data, Player $player) : void{ $player->getHungerManager()->setFood($data->food); });
		$this->registerInjector(SaveDataIds::FOOD_SATURATION, static function(PlayerWorldData $data, Player $player) : void{ $player->getHungerManager()->setSaturation($data->saturation); });
		$this->registerInjector(SaveDataIds::FOOD_EXHAUSTION, static function(PlayerWorldData $data, Player $player) : void{ $player->getHungerManager()->setExhaustion($data->exhaustion); });

		$config = $loader->getConfig();
		$disabled = [];
		foreach((new ReflectionClass(SaveDataIds::class))->getConstants() as $identifier){
			if(!$config->getNested("Save-Data." . $identifier)){
				unset($this->injectors[$identifier]);
				$disabled[] = $identifier;
			}
		}

		if(count($disabled) > 0){
			$loader->getLogger()->debug("Disabled Save-Data for: " . implode(", ", $disabled));
		}
	}

	/**
	 * @param string $identifier
	 * @param Closure(PlayerWorldData $data, Player $player) : void $injector
	 */
	private function registerInjector(string $identifier, Closure $injector) : void{
		$this->injectors[$identifier] = $injector;
	}

	public function inject(PlayerWorldData $data, Player $player) : void{
		foreach($this->injectors as $injector){
			$injector($data, $player);
		}
	}
}