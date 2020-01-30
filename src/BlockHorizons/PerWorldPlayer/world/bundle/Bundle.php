<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\bundle;

use Ds\Set;

final class Bundle{

	/** @var Set<string> */
	private $worlds;

	public function __construct(){
		$this->worlds = new Set();
	}

	public function add(string $world) : void{
		$this->worlds->add($world);
	}

	public function remove(string $world) : void{
		$this->worlds->remove($world);
	}

	public function contains(string $world) : bool{
		return $this->worlds->contains($world);
	}
}