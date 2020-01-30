<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\database;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\Item;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\utils\Binary;

final class WorldDatabaseUtils{

	private const TAG_EFFECTS = "Effects";
	private const TAG_CONTENTS = "Contents";

	private static function getSerializer() : BigEndianNBTStream{
		static $serializer = null;
		return $serializer ?? $serializer = new BigEndianNBTStream();
	}

	/**
	 * Serializes inventory contents, along with their key value relation.
	 *
	 * @param array<int, Item> $contents
	 * @return string
	 */
	public static function serializeInventoryContents(array $contents) : string{
		$tag = new ListTag(self::TAG_CONTENTS, [], NBT::TAG_Compound);
		/**
		 * @var int $slot
		 * @var Item $item
		 */
		foreach($contents as $slot => $item){
			$tag->push($item->nbtSerialize($slot));
		}
		$nbt = new CompoundTag();
		$nbt->setTag($tag);
		return self::getSerializer()->writeCompressed($nbt);
	}

	/**
	 * Unserializes serialized inventory contents, maintaining their key
	 * value relation.
	 *
	 * @param string $serialized
	 * @return array<int, Item>
	 */
	public static function unserializeInventoryContents(string $serialized) : array{
		$nbt = self::getSerializer()->readCompressed($serialized);
		assert($nbt instanceof CompoundTag);

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
		$tag = new ListTag(self::TAG_EFFECTS, [], NBT::TAG_Compound);
		/**
		 * @var int $slot
		 * @var EffectInstance $effect
		 */
		foreach($effects as $effect){
			$tag->push(new CompoundTag("", [
				new ByteTag("Id", $effect->getId()),
				new ByteTag("Amplifier", Binary::signByte($effect->getAmplifier())),
				new IntTag("Duration", $effect->getDuration()),
				new ByteTag("Ambient", $effect->isAmbient() ? 1 : 0),
				new ByteTag("ShowParticles", $effect->isVisible() ? 1 : 0)
			]));
		}
		$nbt = new CompoundTag();
		$nbt->setTag($tag);
		return self::getSerializer()->writeCompressed($nbt);
	}

	/**
	 * @param string $serialized
	 * @return EffectInstance[]
	 */
	public static function unserializeEffects(string $serialized) : array{
		$nbt = self::getSerializer()->readCompressed($serialized);
		assert($nbt instanceof CompoundTag);

		$effects = [];

		/** @var CompoundTag $entry */
		foreach($nbt->getListTag(self::TAG_EFFECTS) as $entry){
			$effect = Effect::getEffect($entry->getByte("Id"));
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