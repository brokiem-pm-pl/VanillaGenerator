<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator\biome;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeIds;
use muqsit\vanillagenerator\generator\overworld\decorator\types\DoublePlantDecoration;
use pocketmine\block\VanillaBlocks;

class SunflowerPlainsPopulator extends PlainsPopulator{

	/** @var DoublePlantDecoration[] */
	private static array $DOUBLE_PLANTS;

	public static function init() : void{
		parent::init();
		self::$DOUBLE_PLANTS = [
			new DoublePlantDecoration(VanillaBlocks::SUNFLOWER(), 1)
		];
	}

	protected function initPopulators() : void{
		$this->doublePlantDecorator->setAmount(10);
		$this->doublePlantDecorator->setDoublePlants(...self::$DOUBLE_PLANTS);
	}

	public function getBiomes() : ?array{
		return [BiomeIds::SUNFLOWER_PLAINS];
	}
}

SunflowerPlainsPopulator::init();
