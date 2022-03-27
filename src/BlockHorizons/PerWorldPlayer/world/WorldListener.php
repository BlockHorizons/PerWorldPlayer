<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\player\Player;
use pocketmine\Server;

final class WorldListener implements Listener{

	private WorldManager $manager;

	public function __construct(WorldManager $manager){
		$this->manager = $manager;

		foreach(Server::getInstance()->getWorldManager()->getWorlds() as $world){
			$this->manager->onWorldLoad($world);
		}
	}

	/**
	 * @param WorldLoadEvent $event
	 * @priority LOWEST
	 */
	public function onWorldLoad(WorldLoadEvent $event) : void{
		$this->manager->onWorldLoad($event->getWorld());
	}

	/**
	 * @param WorldUnloadEvent $event
	 * @priority MONITOR
	 */
	public function onWorldUnload(WorldUnloadEvent $event) : void{
		$this->manager->onWorldUnload($event->getWorld());
	}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority MONITOR
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$this->manager->get($player->getWorld())->onPlayerEnter($player);
	}

	/**
	 * This event's priority is HIGHEST only because PlayerInstance
	 * objects are unset during MONITOR.
	 *
	 * @param PlayerQuitEvent $event
	 * @priority HIGHEST
	 */
	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$player = $event->getPlayer();
		$this->manager->get($player->getWorld())->onPlayerExit($player, null, true);
	}

	/**
	 * @param EntityTeleportEvent $event
	 * @priority MONITOR
	 */
	public function onEntityTeleport(EntityTeleportEvent $event) : void{
		$player = $event->getEntity();
		if($player instanceof Player){
			$from = $event->getFrom()->getWorld();
			$to = $event->getTo()->getWorld();
			if($from !== $to){
				$from_instance = $this->manager->get($from);
				$to_instance = $this->manager->get($to);
				$from_instance->onPlayerExit($player, $to_instance);
				$to_instance->onPlayerEnter($player, $from_instance);
			}
		}
	}
}