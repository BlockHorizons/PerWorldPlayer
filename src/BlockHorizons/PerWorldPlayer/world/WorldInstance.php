<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use BlockHorizons\PerWorldPlayer\events\PerWorldPlayerDataInjectEvent;
use BlockHorizons\PerWorldPlayer\events\PerWorldPlayerDataSaveEvent;
use BlockHorizons\PerWorldPlayer\Loader;
use BlockHorizons\PerWorldPlayer\util\WeakPlayer;
use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
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
		if($player->hasPermission("per-world-player.bypass")){
			return;
		}

		if($from_world !== null && self::haveSameBundles($this, $from_world)){
			return;
		}

		$weak_player = WeakPlayer::from($player);
		$this->loader->getPlayerManager()->get($player)->loadWorldData($this, function(PlayerWorldData $data) use($weak_player) : void{
			$player = $weak_player->get();
			if($player === null){
				return;
			}

			$ev = new PerWorldPlayerDataInjectEvent($player, $this, $data);
			$ev->call();
			if(!$ev->isCancelled()){
				$this->loader->getSaveDataManager()->inject($ev->getPlayerWorldData(), $player);
			}
		});
	}

	public function onPlayerExit(Player $player, ?WorldInstance $to_world = null, bool $quit = false) : void{
		if(
			($to_world === null || !self::haveSameBundles($this, $to_world)) &&
			$this->loader->getPlayerManager()->getNullable($player) !== null
		){ //TODO: currently plugins cannot bypass this
			$this->save($player, PlayerWorldData::fromPlayer($player), $quit ? PerWorldPlayerDataSaveEvent::CAUSE_PLAYER_QUIT : PerWorldPlayerDataSaveEvent::CAUSE_WORLD_CHANGE);
		}
	}

	/**
	 * Saves a player's world data. Plugins must use CAUSE_CUSTOM as other causes
	 * (CAUSE_WORLD_CHANGE, CAUSE_PLAYER_QUIT) are handled by PerWorldPlayer.
	 *
	 * The $force parameter skips checking whether the player can bypass data
	 * saving by having the permission: "per-world-player.bypass".
	 *
	 * @param Player $player
	 * @param PlayerWorldData $data
	 * @param int $cause
	 * @param bool $force
	 */
	public function save(Player $player, PlayerWorldData $data, int $cause = PerWorldPlayerDataSaveEvent::CAUSE_CUSTOM, bool $force = false) : void{
		$ev = new PerWorldPlayerDataSaveEvent($player, $this, $data, $cause);
		if(!$force && $player->hasPermission("per-world-player.bypass")){
			$ev->cancel();
		}
		$ev->call();

		$instance = $this->loader->getPlayerManager()->get($player);
		if($ev->isCancelled()){
			$instance->getLogger()->debug("Data for world " . $this->getName() . " failed to save due to event cancellation");
			return;
		}

		$instance->saveWorldData($this, $data, $cause);
	}
}