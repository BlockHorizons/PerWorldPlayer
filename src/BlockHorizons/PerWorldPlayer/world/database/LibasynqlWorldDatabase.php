<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

use BlockHorizons\PerWorldPlayer\Loader;
use BlockHorizons\PerWorldPlayer\world\data\PlayerWorldData;
use BlockHorizons\PerWorldPlayer\world\WorldInstance;
use Closure;
use pocketmine\Player;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

abstract class LibasynqlWorldDatabase implements WorldDatabase{

	private static function createIdentifier(Player $player, WorldInstance $world) : string{
		$name = strtolower($player->getName());
		$bundle = $world->getBundle();
		return chr(strlen($name)) . $name . chr($bundle !== null ? 1 : 0) . ($bundle ?? $world->getName());
	}

	/** @var DataConnector */
	private $database;

	public function __construct(Loader $plugin){
		$this->database = libasynql::create($plugin, $plugin->getConfig()->get("Database"), ["sqlite" => "db/sqlite.sql", "mysql" => "db/mysql.sql"]);

		$this->database->executeGeneric(WorldDatabaseStmts::INIT);
		$this->database->waitAll();
	}

	public function load(WorldInstance $world, Player $player, Closure $onLoad) : void{
		$this->database->executeSelect(WorldDatabaseStmts::LOAD, ["id" => $this->saveBinaryString(self::createIdentifier($player, $world))], function(array $rows) use ($onLoad) : void{
			if(isset($rows[0])){
				[
					"armor_inventory" => $armor,
					"inventory" => $inventory,
					"ender_inventory" => $ender_inventory,
					"health" => $health,
					"effects" => $effects,
					"gamemode" => $gamemode,
					"experience" => $experience,
					"food" => $food,
					"saturation" => $saturation,
					"exhaustion" => $exhaustion
				] = $rows[0];
				$onLoad(new PlayerWorldData(
					WorldDatabaseUtils::unserializeInventoryContents($this->fetchBinaryString($armor)),
					WorldDatabaseUtils::unserializeInventoryContents($this->fetchBinaryString($inventory)),
					WorldDatabaseUtils::unserializeInventoryContents($this->fetchBinaryString($ender_inventory)),
					$health,
					WorldDatabaseUtils::unserializeEffects($this->fetchBinaryString($effects)),
					$gamemode,
					$experience,
					$food,
					$exhaustion,
					$saturation
				));
			}else{
				$onLoad(PlayerWorldData::empty());
			}
		});
	}

	public function save(WorldInstance $world, Player $player, PlayerWorldData $data, bool $quit) : void{
		$this->database->executeInsert(WorldDatabaseStmts::SAVE, [
			"id" => $this->saveBinaryString(self::createIdentifier($player, $world)),
			"armor_inventory" => $this->saveBinaryString(WorldDatabaseUtils::serializeInventoryContents($data->armor_inventory)),
			"inventory" => $this->saveBinaryString(WorldDatabaseUtils::serializeInventoryContents($data->inventory)),
			"ender_inventory" => $this->saveBinaryString(WorldDatabaseUtils::serializeInventoryContents($data->ender_inventory)),
			"health" => $data->health,
			"effects" => $this->saveBinaryString(WorldDatabaseUtils::serializeEffects($data->effects)),
			"gamemode" => $data->gamemode,
			"experience" => $data->experience,
			"food" => $data->food,
			"exhaustion" => $data->exhaustion,
			"saturation" => $data->saturation
		], function(int $insertId, int $affectedRows) use($world, $player) : void{
			$player->getServer()->getLogger()->debug("Player world data successfully saved for player {$player->getName()} in {$world->getName()}.");
		});
	}

	abstract protected function fetchBinaryString(string $string) : string;

	abstract protected function saveBinaryString(string $string) : string;

	public function close() : void{
		$this->database->waitAll();
		$this->database->close();
	}
}