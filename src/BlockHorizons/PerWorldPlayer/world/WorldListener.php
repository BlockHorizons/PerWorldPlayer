<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world;

use BlockHorizons\PerWorldPlayer\player\PlayerManager;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\world\WorldLoadEvent;
use pocketmine\event\world\WorldUnloadEvent;
use pocketmine\player\Player;
use pocketmine\Server;

final class WorldListener implements Listener{

	private PlayerManager $player_manager;
	private WorldManager $world_manager;

	public function __construct(PlayerManager $player_manager, WorldManager $world_manager){
		$this->player_manager = $player_manager;
		$this->world_manager = $world_manager;

		foreach(Server::getInstance()->getWorldManager()->getWorlds() as $world){
			$this->world_manager->onWorldLoad($world);
		}
	}

	/**
	 * @param WorldLoadEvent $event
	 * @priority LOWEST
	 */
	public function onWorldLoad(WorldLoadEvent $event) : void{
		$this->world_manager->onWorldLoad($event->getWorld());
	}

	/**
	 * @param WorldUnloadEvent $event
	 * @priority MONITOR
	 */
	public function onWorldUnload(WorldUnloadEvent $event) : void{
		$this->world_manager->onWorldUnload($event->getWorld());
	}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority MONITOR
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$player = $event->getPlayer();
		$this->world_manager->get($player->getWorld())->onPlayerEnter($player);
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
		$this->world_manager->get($player->getWorld())->onPlayerExit($player, null, true);
	}

	/**
	 * @param EntityTeleportEvent $event
	 * @priority MONITOR
	 */
	public function onEntityTeleport(EntityTeleportEvent $event) : void{
		$player = $event->getEntity();
		if(
			$player instanceof Player &&
			$this->player_manager->getNullable($player) !== null // plugins may teleport players pre-PlayerJoinEvent
		){
			$from = $event->getFrom()->getWorld();
			$to = $event->getTo()->getWorld();
			if($from !== $to){
				$from_instance = $this->world_manager->get($from);
				$to_instance = $this->world_manager->get($to);
				$from_instance->onPlayerExit($player, $to_instance);
				$to_instance->onPlayerEnter($player, $from_instance);
			}
		}
	}
}