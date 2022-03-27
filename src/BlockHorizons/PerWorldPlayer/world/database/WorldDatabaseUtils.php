<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

use pocketmine\data\bedrock\EffectIdMap;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNbtSerializer;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\TreeRoot;
use pocketmine\utils\Binary;
use function zlib_decode;
use function zlib_encode;
use const ZLIB_ENCODING_GZIP;

final class WorldDatabaseUtils{

	private const TAG_EFFECTS = "Effects";
	private const TAG_CONTENTS = "Contents";

	private static function getSerializer() : BigEndianNbtSerializer{
		static $serializer = null;
		return $serializer ?? $serializer = new BigEndianNbtSerializer();
	}

	/**
	 * Serializes inventory contents, along with their key value relation.
	 *
	 * @param array<int, Item> $contents
	 * @return string
	 */
	public static function serializeInventoryContents(array $contents) : string{
		$tag = new ListTag([], NBT::TAG_Compound);
		/**
		 * @var int $slot
		 * @var Item $item
		 */
		foreach($contents as $slot => $item){
			$tag->push($item->nbtSerialize($slot));
		}
		$nbt = new CompoundTag();
		$nbt->setTag(self::TAG_CONTENTS, $tag);
		return zlib_encode(self::getSerializer()->write(new TreeRoot($nbt)), ZLIB_ENCODING_GZIP);
	}

	/**
	 * Unserializes serialized inventory contents, maintaining their key
	 * value relation.
	 *
	 * @param string $serialized
	 * @return array<int, Item>
	 */
	public static function unserializeInventoryContents(string $serialized) : array{
		$nbt = self::getSerializer()->read(zlib_decode($serialized))->mustGetCompoundTag();

		$contents = [];

		/** @var CompoundTag $entry */
		foreach($nbt->getListTag(self::TAG_CONTENTS) as $entry){
			$contents[$entry->getByte("Slot")] = Item::nbtDeserialize($entry);
		}

		return $contents;
	}

	/**
	 * @param EffectInstance[] $effects
	 * @return string
	 */
	public static function serializeEffects(array $effects) : string{
		$effect_id_map = EffectIdMap::getInstance();
		$tag = new ListTag([], NBT::TAG_Compound);
		/** @var EffectInstance $effect */
		foreach($effects as $effect){
			$tag->push(CompoundTag::create()
				->setByte("Id", $effect_id_map->toId($effect->getType()))
				->setByte("Amplifier", Binary::signByte($effect->getAmplifier()))
				->setInt("Duration", $effect->getDuration())
				->setByte("Ambient", $effect->isAmbient() ? 1 : 0)
				->setByte("ShowParticles", $effect->isVisible() ? 1 : 0)
			);
		}
		$nbt = new CompoundTag();
		$nbt->setTag(self::TAG_EFFECTS, $tag);
		return zlib_encode(self::getSerializer()->write(new TreeRoot($nbt)), ZLIB_ENCODING_GZIP);
	}

	/**
	 * @param string $serialized
	 * @return EffectInstance[]
	 */
	public static function unserializeEffects(string $serialized) : array{
		$nbt = self::getSerializer()->read(zlib_decode($serialized))->mustGetCompoundTag();

		$effect_id_map = EffectIdMap::getInstance();
		$effects = [];

		/** @var CompoundTag $entry */
		foreach($nbt->getListTag(self::TAG_EFFECTS) as $entry){
			$effect = $effect_id_map->fromId($entry->getByte("Id"));
			if($effect === null){
				continue;
			}

			$effects[] = new EffectInstance(
				$effect,
				$entry->getInt("Duration"),
				Binary::unsignByte($entry->getByte("Amplifier")),
				$entry->getByte("ShowParticles", 1) !== 0,
				$entry->getByte("Ambient", 0) !== 0
			);
		}

		return $effects;
	}
}