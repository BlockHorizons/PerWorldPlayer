<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\bundle;

final class Bundle{

	/** @var array<string, string> */
	private array $worlds = [];

	public function __construct(){
	}

	public function add(string $world) : void{
		$this->worlds[$world] = $world;
	}

	public function remove(string $world) : void{
		unset($this->worlds[$world]);
	}

	public function contains(string $world) : bool{
		return isset($this->worlds[$world]);
	}
}