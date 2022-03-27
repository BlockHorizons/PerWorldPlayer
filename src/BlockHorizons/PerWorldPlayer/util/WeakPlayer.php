<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\util;

use pocketmine\player\Player;
use pocketmine\Server;
use Ramsey\Uuid\UuidInterface;
use function spl_object_id;

final class WeakPlayer{

	public static function from(Player $player) : self{
		return new self($player->getUniqueId(), spl_object_id($player));
	}

	private function __construct(
		private UuidInterface $uuid,
		private int $object_id
	){}

	public function get() : ?Player{
		$player = Server::getInstance()->getPlayerByUUID($this->uuid);
		return $player !== null && spl_object_id($player) === $this->object_id ? $player : null;
	}
}