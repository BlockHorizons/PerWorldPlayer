<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use BlockHorizons\PerWorldPlayer\events\PerWorldPlayerDataSaveEvent;
use BlockHorizons\PerWorldPlayer\Loader;
use BlockHorizons\PerWorldPlayer\world\bundle\BundleManager;
use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\database\WorldDatabase;
use BlockHorizons\PerWorldPlayer\world\database\WorldDatabaseFactory;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use pocketmine\world\World;

final class WorldManager{

	private Loader $loader;
	private BundleManager $bundle;
	private WorldDatabase $database;

	/** @var WorldInstance[] */
	private array $worlds = [];

	public function __construct(Loader $loader){
		$this->loader = $loader;
		$this->bundle = new BundleManager($this->loader->getConfig()->get("Bundled-Worlds"));
		$this->database = WorldDatabaseFactory::create($this->loader);
		$this->loader->getServer()->getPluginManager()->registerEvents(new WorldListener($this), $this->loader);
	}

	public function getDatabase() : WorldDatabase{
		return $this->database;
	}

	public function close() : void{
		foreach(Server::getInstance()->getWorldManager()->getWorlds() as $world){
			$instance = $this->get($world);
			foreach($world->getPlayers() as $player){
				$instance->save($player, PlayerWorldData::fromPlayer($player), PerWorldPlayerDataSaveEvent::CAUSE_PLAYER_QUIT);
			}
		}

		$this->database->close();
	}

	public function onWorldLoad(World $world) : void{
		$this->worlds[$world->getId()] = new WorldInstance($this->loader, $world, $this->bundle->getBundle($world->getFolderName()));
	}

	public function onWorldUnload(World $world, bool $instant = false) : void{
		if($instant){
			unset($this->worlds[$world->getId()]);
		}else{
			$this->loader->getScheduler()->scheduleDelayedTask(new ClosureTask(function() use($world) : void{
				if(!$world->isLoaded()){
					$this->onWorldUnload($world, true);
				}
			}), 1);
		}
	}

	public function get(World $world) : WorldInstance{
		return $this->worlds[$world->getId()];
	}
}