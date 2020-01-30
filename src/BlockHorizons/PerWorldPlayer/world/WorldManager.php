<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use BlockHorizons\PerWorldPlayer\Loader;
use BlockHorizons\PerWorldPlayer\player\PlayerManager;
use BlockHorizons\PerWorldPlayer\world\bundle\BundleManager;
use BlockHorizons\PerWorldPlayer\world\data\SaveDataManager;
use BlockHorizons\PerWorldPlayer\world\database\WorldDatabase;
use BlockHorizons\PerWorldPlayer\world\database\WorldDatabaseFactory;
use pocketmine\level\Level;
use pocketmine\Server;

final class WorldManager{

	/** @var BundleManager */
	private $bundle;

	/** @var WorldDatabase */
	private $database;

	/** @var PlayerManager */
	private $player_manager;

	/** @var WorldInstance[] */
	private $worlds = [];

	public function __construct(Loader $plugin){
		$this->bundle = new BundleManager($plugin->getConfig()->get("Bundled-Worlds"));
		$this->database = WorldDatabaseFactory::create($plugin);
		$this->player_manager = $plugin->getPlayerManager();
		$plugin->getServer()->getPluginManager()->registerEvents(new WorldListener($this), $plugin);
	}

	public function close() : void{
		foreach(Server::getInstance()->getLevels() as $world){
			$instance = $this->get($world);
			foreach($world->getPlayers() as $player){
				$instance->save($player);
			}
		}

		$this->database->close();
	}

	public function onWorldLoad(Level $world) : void{
		$this->worlds[$world->getId()] = new WorldInstance($world, $this->database, $this->player_manager, $this->bundle->getBundle($world->getFolderName()));
	}

	public function onWorldUnload(Level $world) : void{
		unset($this->worlds[$world->getId()]);
	}

	public function get(Level $world) : WorldInstance{
		return $this->worlds[$world->getId()];
	}
}