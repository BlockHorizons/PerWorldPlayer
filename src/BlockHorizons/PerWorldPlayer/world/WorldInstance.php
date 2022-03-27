<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use BlockHorizons\PerWorldPlayer\Loader;
use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\events\PerWorldPlayerDataInjectEvent;
use BlockHorizons\PerWorldPlayer\events\PerWorldPlayerDataSaveEvent;
use pocketmine\player\Player;
use pocketmine\world\World;

final class WorldInstance{

	private static function haveSameBundles(self $a, self $b) : bool{
		return $a->bundle !== null && $b->bundle !== null && $a->bundle === $b->bundle;
	}

	private Loader $loader;
	private string $name;
	private ?string $bundle;

	public function __construct(Loader $loader, World $world, ?string $bundle){
		$this->loader = $loader;
		$this->name = $world->getFolderName();
		$this->bundle = $bundle;
	}

	public function getName() : string{
		return $this->name;
	}

	public function getBundle() : ?string{
		return $this->bundle;
	}

	public function onPlayerEnter(Player $player, ?WorldInstance $from_world = null) : void{
		if(!$player->hasPermission("per-world-player.bypass")){
			if($from_world === null || !self::haveSameBundles($this, $from_world)){
				$instance = $this->loader->getPlayerManager()->getNullable($player);
				if($instance !== null){
					$instance->wait($this);
					$this->loader->getWorldManager()->getDatabase()->load($this, $player, function(PlayerWorldData $data) use($player, $instance) : void{
						if($player->isOnline()){
							$ev = new PerWorldPlayerDataInjectEvent($player, $this, $data);
							$ev->call();
							if(!$ev->isCancelled()){
								$this->loader->getSaveDataManager()->inject($ev->getPlayerWorldData(), $player);
							}
							$instance->notify($this);
						}
					});
				}
			}
		}
	}

	public function onPlayerExit(Player $player, ?WorldInstance $to_world = null, bool $quit = false) : void{
		if($to_world === null || !self::haveSameBundles($this, $to_world)){ //TODO: currently plugins cannot bypass this
			$this->save($player, PlayerWorldData::fromPlayer($player), $quit ? PerWorldPlayerDataSaveEvent::CAUSE_PLAYER_QUIT : PerWorldPlayerDataSaveEvent::CAUSE_WORLD_CHANGE);
		}
	}

	public function save(Player $player, PlayerWorldData $data, int $cause = PerWorldPlayerDataSaveEvent::CAUSE_CUSTOM, bool $force = false) : void{
		$ev = new PerWorldPlayerDataSaveEvent($player, $this, $data, $cause);
		if(!$force && $player->hasPermission("per-world-player.bypass")){
			$ev->cancel();
		}
		$ev->call();
		if(!$ev->isCancelled()){
			$this->loader->getWorldManager()->getDatabase()->save($this, $player, $ev->getPlayerWorldData(), $cause, function(bool $success) use($player) : void{
				if($success){
					$this->loader->getLogger()->debug("Data successfully saved for player {$player->getName()} in world {$this->getName()}.");
				}else{
					$this->loader->getLogger()->error("Could not save data for player {$player->getName()} in world {$this->getName()}.");
				}
			});
		}else{
			$this->loader->getLogger()->debug("Data save cancelled for player {$player->getName()} in world {$this->getName()}.");
		}
	}
}