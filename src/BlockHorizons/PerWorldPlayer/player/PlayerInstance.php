<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\player;

use BlockHorizons\PerWorldPlayer\Loader;
use BlockHorizons\PerWorldPlayer\util\WeakPlayer;
use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\WorldInstance;
use Closure;
use Logger;
use pocketmine\player\Player;
use PrefixedLogger;
use function array_key_first;
use function assert;
use function count;
use function spl_object_id;

final class PlayerInstance{

	private const WORLD_DATA_CACHE_SIZE = 8;

	private Loader $loader;
	private Player $player;
	private Logger $logger;

	private int $lock_ids = 0;

	/** @var int[] */
	private array $locks = [];

	/** @var PlayerWorldData[] */
	private array $world_data = [];

	/**
	 * @var Closure[][]
	 *
	 * @phpstan-var array<string, array<int, Closure(PlayerWorldData) : void>>
	 */
	private array $world_data_callbacks = [];

	public function __construct(Loader $loader, Player $player){
		$this->loader = $loader;
		$this->player = $player;
		$this->logger = new PrefixedLogger($this->loader->getLogger(), $player->getName());
	}

	public function getLogger() : Logger{
		return $this->logger;
	}

	public function acquireLock() : int{
		$this->locks[$id = $this->lock_ids++] = $id;
		return $id;
	}

	public function releaseLock(int $id) : void{
		unset($this->locks[$id]);
	}

	public function isLocked() : bool{
		return count($this->locks) > 0;
	}

	/**
	 * @param WorldInstance $world
	 * @param Closure $callback
	 *
	 * @phpstan-param Closure(PlayerWorldData) : void $callback
	 */
	public function loadWorldData(WorldInstance $world, Closure $callback) : void{
		if(isset($this->world_data[$name = $world->getName()])){
			$this->getLogger()->debug("Loaded data for world " . $name . " from memory");
			$callback($this->world_data[$name]);
			return;
		}

		if(isset($this->world_data_callbacks[$name])){
			$this->world_data_callbacks[$name][spl_object_id($callback)] = $callback;
			return;
		}

		$this->world_data_callbacks[$name][spl_object_id($callback)] = $callback;

		$weak_player = WeakPlayer::from($this->player);
		$loader = $this->loader;
		$lock = $this->acquireLock();
		$loader->getWorldManager()->getDatabase()->load($world, $this->player, static function(PlayerWorldData $data) use($loader, $weak_player, $name, $lock) : void{
			$player = $weak_player->get();
			if($player !== null){
				$instance = $loader->getPlayerManager()->get($player);
				$instance->getLogger()->debug("Loaded data for world " . $name . " from database");
				$instance->onWorldDataLoad($name, $data);
				$instance->releaseLock($lock);
			}
		});
	}

	private function onWorldDataLoad(string $world_name, PlayerWorldData $data) : void{
		if(!isset($this->world_data[$world_name]) && count($this->world_data) === self::WORLD_DATA_CACHE_SIZE){
			$removed_world_name = array_key_first($this->world_data);
			assert($removed_world_name !== null);
			unset($this->world_data[$removed_world_name]);
		}

		$this->world_data[$world_name] = $data;
		if(isset($this->world_data_callbacks[$world_name])){
			foreach($this->world_data_callbacks[$world_name] as $callback){
				$callback($data);
			}
			unset($this->world_data_callbacks[$world_name]);
		}
	}

	public function saveWorldData(WorldInstance $world, PlayerWorldData $data, int $cause) : void{
		$name = $world->getName();
		$logger = $this->getLogger();
		$this->loader->getWorldManager()->getDatabase()->save($world, $this->player, $data, $cause, function(bool $success) use($name, $logger, $cause) : void{
			if($success){
				$logger->debug("Data for world " . $name . " successfully saved");
			}else{
				$logger->error("Data for world " . $name . " failed to save (cause of save: " . $cause . ")");
			}
		});
	}

	public function close() : void{
		$this->world_data_callbacks = [];
	}
}