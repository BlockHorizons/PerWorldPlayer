<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\player;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;

final class PlayerListener implements Listener{

	public function __construct(
		private PlayerManager $manager
	){}

	private function shouldCancelEvent(Player $player) : bool{
		return $this->manager->getNullable($player)?->isWaiting() ?? false;
	}

	/**
	 * @param PlayerJoinEvent $event
	 * @priority LOWEST
	 */
	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		$this->manager->onPlayerJoin($event->getPlayer());
	}

	/**
	 * @param PlayerQuitEvent $event
	 * @priority MONITOR
	 */
	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$this->manager->onPlayerQuit($event->getPlayer());
	}

	/**
	 * @param PlayerInteractEvent $event
	 * @priority LOW
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		if($this->shouldCancelEvent($event->getPlayer())){
			$event->cancel();
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 * @priority LOW
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void{
		if($this->shouldCancelEvent($event->getPlayer())){
			$event->cancel();
		}
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority LOW
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		if($this->shouldCancelEvent($event->getPlayer())){
			$event->cancel();
		}
	}

	/**
	 * @param EntityDamageEvent $event
	 * @priority LOW
	 */
	public function onEntityDamage(EntityDamageEvent $event) : void{
		$victim = $event->getEntity();
		if($victim instanceof Player && $this->shouldCancelEvent($victim)){
			$event->cancel();
			return;
		}

		if($event instanceof EntityDamageByEntityEvent){
			$damager = $event->getDamager();
			if($damager instanceof Player && $this->shouldCancelEvent($damager)){
				$event->cancel();
			}
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 * @priority LOW
	 */
	public function onInventoryTransaction(InventoryTransactionEvent $event) : void{
		if($this->shouldCancelEvent($event->getTransaction()->getSource())){
			$event->cancel();
		}
	}
}