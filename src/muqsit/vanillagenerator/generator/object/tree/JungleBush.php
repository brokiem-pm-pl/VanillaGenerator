<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\TreeType;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function abs;

class JungleBush extends GenericTree{

	/**
	 * Initializes this bush, preparing it to attempt to generate.
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setType(TreeType::JUNGLE());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		while((($blockId = $world->getBlockAt($sourceX, $sourceY, $sourceZ)->getId()) === BlockLegacyIds::AIR || $blockId === BlockLegacyIds::LEAVES) && $sourceY > 0){
			--$sourceY;
		}

		// check only below block
		if(!$this->canPlaceOn($world->getBlockAt($sourceX, $sourceY - 1, $sourceZ))){
			return false;
		}

		// generates the trunk
		$adjustY = $sourceY;
		$this->transaction->addBlockAt($sourceX, $adjustY + 1, $sourceZ, $this->logType);

		// generates the leaves
		for($y = $adjustY + 1; $y <= $adjustY + 3; ++$y){
			$radius = 3 - ($y - $adjustY);

			for($x = $sourceX - $radius; $x <= $sourceX + $radius; ++$x){
				for($z = $sourceZ - $radius; $z <= $sourceZ + $radius; ++$z){
					if(
						!$this->transaction->fetchBlockAt($x, $y, $z)->isSolid() &&
						(
							abs($x - $sourceX) !== $radius ||
							abs($z - $sourceZ) !== $radius ||
							$random->nextBoolean()
						)
					){
						$this->transaction->addBlockAt($x, $y, $z, $this->leavesType);
					}
				}
			}
		}

		return true;
	}
}
