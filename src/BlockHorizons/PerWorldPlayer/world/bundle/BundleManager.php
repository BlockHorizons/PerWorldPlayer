<?php

declare(strict_types=1);

namespace BlockHorizons\PerWorldPlayer\world\bundle;

final class BundleManager{

	/** @var Bundle[] */
	private array $bundles = [];

	/** @var string[] */
	private array $bundled_worlds = [];

	/**
	 * @param mixed[] $bundled_worlds_configuration
	 *
	 * @phpstan-param array<string, array<string>> $bundled_worlds_configuration
	 */
	public function __construct(array $bundled_worlds_configuration){
		/**
		 * @var string $bundle
		 * @var string[] $worlds
		 */
		foreach($bundled_worlds_configuration as $bundle => $worlds){
			$this->createBundle($bundle);
			foreach($worlds as $world){
				$this->addToBundle($bundle, $world);
			}
		}
	}

	public function createBundle(string $name) : void{
		if(isset($this->bundles[$name])){
			throw new \InvalidArgumentException("Bundle " . $name . " already exists.");
		}

		$this->bundles[$name] = new Bundle();
	}

	public function addToBundle(string $bundle, string $world) : void{
		if(isset($this->bundled_worlds[$world])){
			throw new \InvalidArgumentException("World " . $world . " is already bundled in bundle " . $this->bundled_worlds[$world]);
		}

		if(!isset($this->bundles[$bundle])){
			throw new \InvalidArgumentException("Tried adding world " . $world . " into a non-existent bundle " . $bundle);
		}

		$this->bundled_worlds[$world] = $bundle;
	}

	public function getBundle(string $world) : ?string{
		return $this->bundled_worlds[$world] ?? null;
	}
}