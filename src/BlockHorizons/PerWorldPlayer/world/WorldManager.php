<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use BlockHorizons\PerWorldPlayer\events\PerWorldPlayerDataSaveEvent;
use BlockHorizons\PerWorldPlayer\Loader;
use BlockHorizons\PerWorldPlayer\player\PlayerManager;
use BlockHorizons\PerWorldPlayer\world\bundle\BundleManager;
use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\database\WorldDatabase;
use BlockHorizons\PerWorldPlayer\world\database\WorldDatabaseFactory;
use pocketmine\level\Level;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;
use pocketmine\Server;

final class WorldManager{

	/** @var BundleManager */
	private $bundle;

	/** @var WorldDatabase */
	private $database;

	/** @var PlayerManager */
	private $player_manager;

	/** @var TaskScheduler */
	private $scheduler;

	/** @var \Logger */
	private $logger;

	/** @var WorldInstance[] */
	private $worlds = [];

	public function __construct(Loader $plugin){
		$this->bundle = new BundleManager($plugin->getConfig()->get("Bundled-Worlds"));
		$this->database = WorldDatabaseFactory::create($plugin);
		$this->player_manager = $plugin->getPlayerManager();
		$this->logger = $plugin->getLogger();
		$this->scheduler = $plugin->getScheduler();
		$plugin->getServer()->getPluginManager()->registerEvents(new WorldListener($this), $plugin);
	}

	public function close() : void{
		foreach(Server::getInstance()->getLevels() as $world){
			$instance = $this->get($world);
			foreach($world->getPlayers() as $player){
				$instance->save($player, PlayerWorldData::fromPlayer($player), false, PerWorldPlayerDataSaveEvent::CAUSE_PLAYER_QUIT);
			}
		}

		$this->database->close();
	}

	public function onWorldLoad(Level $world) : void{
		$this->worlds[$world->getId()] = new WorldInstance($world, $this->database, $this->player_manager, $this->logger, $this->bundle->getBundle($world->getFolderName()));
	}

	public function onWorldUnload(Level $world, bool $instant = false) : void{
		if($instant){
			unset($this->worlds[$world->getId()]);
		}else{
			$this->scheduler->scheduleDelayedTask(new ClosureTask(function(int $currentTick) use($world) : void{
				if($world->isClosed()){
					$this->onWorldUnload($world, true);
				}
			}), 1);
		}
	}

	public function get(Level $world) : WorldInstance{
		return $this->worlds[$world->getId()];
	}
}