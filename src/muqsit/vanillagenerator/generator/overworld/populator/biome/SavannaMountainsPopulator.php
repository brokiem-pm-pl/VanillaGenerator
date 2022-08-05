<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;

class SavannaMountainsPopulator extends SavannaPopulator{

	protected function initPopulators() : void{
		$this->treeDecorator->setAmount(2);
		$this->treeDecorator->setTrees(...self::$TREES);
		$this->flowerDecorator->setAmount(2);
		$this->flowerDecorator->setFlowers(...self::$FLOWERS);
		$this->tallGrassDecorator->setAmount(5);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::SAVANNA_MUTATED, BiomeIds::SAVANNA_PLATEAU_MUTATED];
	}
}
SavannaMountainsPopulator::init();
