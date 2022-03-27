<?php
declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class PerWorldPlayerDataInjectEvent extends PerWorldPlayerDataEvent implements Cancellable{
	use CancellableTrait;
}