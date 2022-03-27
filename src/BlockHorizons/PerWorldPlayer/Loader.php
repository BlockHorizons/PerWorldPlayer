<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer;

use BlockHorizons\PerWorldPlayer\player\PlayerManager;
use BlockHorizons\PerWorldPlayer\world\data\SaveDataManager;
use BlockHorizons\PerWorldPlayer\world\WorldManager;
use pocketmine\plugin\PluginBase;

final class Loader extends PluginBase{

	private PlayerManager $player_manager;
	private WorldManager $world_manager;

	protected function onEnable() : void{
		SaveDataManager::init($this);
		$this->player_manager = new PlayerManager($this);
		$this->world_manager = new WorldManager($this);
	}

	protected function onDisable() : void{
		$this->world_manager->close();
	}

	/**
	 * @return PlayerManager
	 * @internal
	 */
	public function getPlayerManager() : PlayerManager{
		return $this->player_manager;
	}
}
