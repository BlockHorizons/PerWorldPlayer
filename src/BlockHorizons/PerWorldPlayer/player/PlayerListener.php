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
use pocketmine\Player;

final class PlayerListener implements Listener{

	/** @var PlayerManager */
	private $manager;

	public function __construct(PlayerManager $manager){
		$this->manager = $manager;
	}

	private function cancelIfWaiting(Player $player, Cancellable $event) : bool{
		$instance = $this->manager->getNullable($player);
		if($instance !== null && $instance->isWaiting()){
			$event->setCancelled();
			return true;
		}

		return false;
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
	 * @ignoreCancelled true
	 */
	public function onPlayerInteract(PlayerInteractEvent $event) : void{
		$this->cancelIfWaiting($event->getPlayer(), $event);
	}

	/**
	 * @param BlockPlaceEvent $event
	 * @priority LOW
	 * @ignoreCancelled true
	 */
	public function onBlockPlace(BlockPlaceEvent $event) : void{
		$this->cancelIfWaiting($event->getPlayer(), $event);
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority LOW
	 * @ignoreCancelled true
	 */
	public function onBlockBreak(BlockBreakEvent $event) : void{
		$this->cancelIfWaiting($event->getPlayer(), $event);
	}

	/**
	 * @param EntityDamageEvent $event
	 * @priority LOW
	 * @ignoreCancelled true
	 */
	public function onEntityDamage(EntityDamageEvent $event) : void{
		$victim = $event->getEntity();
		if($victim instanceof Player && $this->cancelIfWaiting($victim, $event)){
			return;
		}

		if($event instanceof EntityDamageByEntityEvent){
			$damager = $event->getDamager();
			if($damager instanceof Player){
				$this->cancelIfWaiting($damager, $event);
			}
		}
	}

	/**
	 * @param InventoryTransactionEvent $event
	 * @priority LOW
	 * @ignoreCancelled true
	 */
	public function onInventoryTransaction(InventoryTransactionEvent $event) : void{
		$this->cancelIfWaiting($event->getTransaction()->getSource(), $event);
	}
}