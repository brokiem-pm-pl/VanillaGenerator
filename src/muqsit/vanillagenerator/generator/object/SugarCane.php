<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Dirt;
use pocketmine\block\VanillaBlocks;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\utils\Random;
use pocketmine\world\ChunkManager;

class SugarCane extends TerrainObject{

	private const FACES = [Facing::NORTH, Facing::EAST, Facing::SOUTH, Facing::WEST];

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if($world->getBlockAt($sourceX, $sourceY, $sourceZ)->getId() !== BlockLegacyIds::AIR){
			return false;
		}

		$vec = new Vector3($sourceX, $sourceY - 1, $sourceZ);
		$adjacentWater = false;
		foreach(self::FACES as $face){
			// needs a directly adjacent water block
			$blockTypeV = $vec->getSide($face);
			$blockType = $world->getBlockAt($blockTypeV->x, $blockTypeV->y, $blockTypeV->z)->getId();
			if($blockType === BlockLegacyIds::STILL_WATER || $blockType === BlockLegacyIds::FLOWING_WATER){
				$adjacentWater = true;
				break;
			}
		}
		if(!$adjacentWater){
			return false;
		}
		for($n = 0; $n <= $random->nextBoundedInt($random->nextBoundedInt(3) + 1) + 1; ++$n){
			$block = $world->getBlockAt($sourceX, $sourceY + $n - 1, $sourceZ);
			$blockId = $block->getId();
			if($blockId === BlocKLegacyIds::SUGARCANE_BLOCK
				|| $blockId === BlocKLegacyIds::GRASS
				|| $blockId === BlocKLegacyIds::SAND
				|| ($block instanceof Dirt && !$block->isCoarse())
			){
				$caneBlock = $world->getBlockAt($sourceX, $sourceY + $n, $sourceZ);
				if($caneBlock->getId() !== BlockLegacyIds::AIR && $world->getBlockAt($sourceX, $sourceY + $n + 1, $sourceZ)->getId() !== BlockLegacyIds::AIR){
					return $n > 0;
				}

				$world->setBlockAt($sourceX, $sourceY + $n, $sourceZ, VanillaBlocks::SUGARCANE());
			}
		}
		return true;
	}
}
