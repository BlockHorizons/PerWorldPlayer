<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer;

use BlockHorizons\PerWorldPlayer\player\PlayerManager;
use BlockHorizons\PerWorldPlayer\world\data\SaveDataManager;
use BlockHorizons\PerWorldPlayer\world\WorldManager;
use pocketmine\plugin\PluginBase;

final class Loader extends PluginBase{

	private PlayerManager $player_manager;
	private SaveDataManager $save_data_manager;
	private WorldManager $world_manager;

	protected function onEnable() : void{
		$this->player_manager = new PlayerManager($this);
		$this->save_data_manager = new SaveDataManager($this);
		$this->world_manager = new WorldManager($this);
	}

	protected function onDisable() : void{
		$this->world_manager->close();
	}

	public function getPlayerManager() : PlayerManager{
		return $this->player_manager;
	}

	public function getSaveDataManager() : SaveDataManager{
		return $this->save_data_manager;
	}

	public function getWorldManager() : WorldManager{
		return $this->world_manager;
	}
}
