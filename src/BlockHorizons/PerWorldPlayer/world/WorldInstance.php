<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use BlockHorizons\PerWorldPlayer\player\PlayerManager;
use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\database\WorldDatabase;
use pocketmine\level\Level;
use pocketmine\Player;

final class WorldInstance{

	private static function haveSameBundles(self $a, self $b) : bool{
		return $a->bundle !== null && $b->bundle !== null && $a->bundle === $b->bundle;
	}

	/** @var string */
	private $name;

	/** @var WorldDatabase */
	private $database;

	/** @var PlayerManager */
	private $player_manager;

	/** @var string|null */
	private $bundle;

	public function __construct(Level $level, WorldDatabase $database, PlayerManager $player_manager, ?string $bundle){
		$this->name = $level->getFolderName();
		$this->database = $database;
		$this->player_manager = $player_manager;
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
				$instance = $this->player_manager->getNullable($player);
				if($instance !== null){
					$instance->wait($this);
					$this->database->load($this, $player, function(PlayerWorldData $data) use($player, $instance) : void{
						if($player->isOnline()){
							$data->inject($player);
							$instance->notify($this);
						}
					});
				}
			}
		}
	}

	public function onPlayerExit(Player $player, ?WorldInstance $to_world = null, bool $quit = false) : void{
		if($to_world === null || !self::haveSameBundles($this, $to_world)){
			$this->save($player, PlayerWorldData::fromPlayer($player), false, $quit);
		}
	}

	public function save(Player $player, PlayerWorldData $data, bool $force = false, bool $quit = false) : void{
		if($force || !$player->hasPermission("per-world-player.bypass")){
			$this->database->save($this, $player, $data, $quit);
		}
	}
}