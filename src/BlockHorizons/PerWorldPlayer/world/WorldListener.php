<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\Server;

final class WorldListener implements Listener{

	/** @var WorldManager */
	private $manager;

	public function __construct(WorldManager $manager){
		$this->manager = $manager;

		foreach(Server::getInstance()->getLevels() as $level){
			$this->manager->onWorldLoad($level);
		}
	}

	/**
	 * @param LevelLoadEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onWorldLoad(LevelLoadEvent $event) : void{
		$this->manager->onWorldLoad($event->getLevel());
	}

	/**
	 * @param LevelUnloadEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onWorldUnload(LevelUnloadEvent $event) : void{
		$this->manager->onWorldUnload($event->getLevel());
	}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority MONITOR
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$this->manager->get($player->getLevel())->onPlayerEnter($player);
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
		$this->manager->get($player->getLevel())->onPlayerExit($player, null, true);
	}

	/**
	 * @param EntityTeleportEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onEntityTeleport(EntityTeleportEvent $event) : void{
		$player = $event->getEntity();
		if($player instanceof Player){
			$from = $event->getFrom()->getLevel();
			$to = $event->getTo()->getLevel();
			if($from !== $to){
				$from_instance = $from !== null ? $this->manager->get($from) : null;
				$to_instance = $to !== null ? $this->manager->get($to) : null;

				if($from_instance !== null){
					$from_instance->onPlayerExit($player, $to_instance);
				}
				if($to_instance !== null){
					$to_instance->onPlayerEnter($player, $from_instance);
				}
			}
		}
	}
}