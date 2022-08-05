<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\overworld\populator;

use muqsit\vanillagenerator\generator\overworld\biome\BiomeClimateManager;
use muqsit\vanillagenerator\generator\Populator;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use function in_array;

class SnowPopulator implements Populator {
	public function populate(ChunkManager $world, Random $random, int $chunk_x, int $chunk_z, Chunk $chunk): void {
		$disallowedBlocks = [
			VanillaBlocks::WATER()->getFullId(),
			VanillaBlocks::WATER()->setStill()->getFullId(),
			VanillaBlocks::SNOW()->getFullId(),
			VanillaBlocks::ICE()->getFullId(),
			VanillaBlocks::PACKED_ICE()->getFullId(),
			VanillaBlocks::DANDELION()->getFullId(),
			VanillaBlocks::POPPY()->getFullId(),
			VanillaBlocks::DOUBLE_TALLGRASS()->getFullId(),
			VanillaBlocks::BROWN_MUSHROOM()->getFullId(),
			VanillaBlocks::RED_MUSHROOM()->getFullId(),
			VanillaBlocks::ROSE_BUSH()->getFullId(),
			VanillaBlocks::LARGE_FERN()->getFullId(),
			VanillaBlocks::SUGARCANE()->getFullId(),
			VanillaBlocks::TALL_GRASS()->getFullId(),
			VanillaBlocks::LAVA()->getFullId(),
			VanillaBlocks::LAVA()->setStill()->getFullId(),
		];

		$dirt = VanillaBlocks::DIRT()->getFullId();
		$grass = VanillaBlocks::GRASS()->getFullId();
		$snow = VanillaBlocks::SNOW_LAYER()->getFullId();

		$sourceX = $chunk_x << 4;
		$sourceZ = $chunk_z << 4;
		for($x = 0; $x < 16; ++$x) {
			for($z = 0; $z < 16; ++$z) {
				$y = ($chunk->getHighestBlockAt($x, $z) ?? 0);
				if(BiomeClimateManager::isSnowy($chunk->getBiomeId($x, $z), $sourceX + $x, $y, $sourceZ + $z)) {
					$block = $chunk->getFullBlock($x, $y, $z);
					if(in_array($block, $disallowedBlocks)) {
						continue;
					}

					if($block === $dirt) {
						$chunk->setFullBlock($x, $y, $z, $grass);
					}
					$chunk->setFullBlock($x, $y + 1, $z, $snow);
				}
			}
		}
	}
}