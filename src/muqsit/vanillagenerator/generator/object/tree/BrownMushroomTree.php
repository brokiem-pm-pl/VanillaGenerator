<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use function array_key_exists;

class BrownMushroomTree extends GenericTree{

	protected int $type = BlockLegacyIds::BROWN_MUSHROOM_BLOCK;

	/**
	 * Initializes this mushroom with a random height, preparing it to attempt to generate.
	 */
	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(
			BlockLegacyIds::AIR,
			BlockLegacyIds::LEAVES,
			BlockLegacyIds::LEAVES2
		);
		$this->setHeight($random->nextBoundedInt(3) + 4);
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT || $id === BlockLegacyIds::MYCELIUM;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		$worldHeight = $world->getMaxY();
		for($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y){
			// Space requirement is 7x7 blocks, so brown mushroom's cap
			// can be directly touching a mushroom next to it.
			// Since red mushrooms fits in 5x5 blocks it will never
			// touch another huge mushroom.
			$radius = 3;
			if($y <= $baseY + 3){
				$radius = 0; // radius is 0 below 4 blocks tall (only the stem to take in account)
			}

			// check for block collision on horizontal slices
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					if($y < 0 || $y >= $worldHeight){ // height out of range
						return false;
					}
					// skip source block check
					if($y !== $baseY || $x !== $baseX || $z !== $baseZ){
						// we can overlap leaves around
						if(!array_key_exists($world->getBlockAt($x, $y, $z)->getId(), $this->overridables)){
							return false;
						}
					}
				}
			}
		}
		return true;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		if($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)){
			return false;
		}

		$blockFactory = BlockFactory::getInstance();

		// generate the stem
		$stem = $blockFactory->get($this->type, 10);
		for($y = 0; $y < $this->height; ++$y){
			$this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $stem); // stem texture
		}

		// get the mushroom's cap Y start
		$capY = $sourceY + $this->height; // for brown mushroom it starts on top directly
		if($this->type === BlockLegacyIds::RED_MUSHROOM_BLOCK){
			$capY = $sourceY + $this->height - 3; // for red mushroom, cap's thickness is 4 blocks
		}

		// generate mushroom's cap
		for($y = $capY; $y <= $sourceY + $this->height; ++$y){ // from bottom to top of mushroom
			$radius = 1; // radius for the top of red mushroom
			if($y < $sourceY + $this->height){
				$radius = 2; // radius for red mushroom cap is 2
			}
			if($this->type === BlockLegacyIds::BROWN_MUSHROOM_BLOCK){
				$radius = 3; // radius always 3 for a brown mushroom
			}
			// loop over horizontal slice
			for($x = $sourceX - $radius; $x <= $sourceX + $radius; ++$x){
				for($z = $sourceZ - $radius; $z <= $sourceZ + $radius; ++$z){
					$data = 5; // cap texture on top
					// cap's borders/corners treatment
					if($x === $sourceX - $radius){
						$data = 4; // cap texture on top and west
					}elseif($x === $sourceX + $radius){
						$data = 6; // cap texture on top and east
					}
					if($z === $sourceZ - $radius){
						$data -= 3;
					}elseif($z === $sourceZ + $radius){
						$data += 3;
					}

					// corners shrink treatment
					// if it's a brown mushroom we need it always
					// it's a red mushroom, it's only applied below the top
					if($this->type === BlockLegacyIds::BROWN_MUSHROOM_BLOCK || $y < $sourceY + $this->height){

						// excludes the real corners of the cap structure
						if(($x === $sourceX - $radius || $x === $sourceX + $radius)
							&& ($z === $sourceZ - $radius || $z === $sourceZ + $radius)){
							continue;
						}

						// mushroom's cap corners treatment
						if($x === $sourceX - ($radius - 1) && $z === $sourceZ - $radius){
							$data = 1; // cap texture on top, west and north
						}elseif($x === $sourceX - $radius && $z === $sourceZ - ($radius
								- 1)){
							$data = 1; // cap texture on top, west and north
						}elseif($x === $sourceX + $radius - 1 && $z === $sourceZ - $radius){
							$data = 3; // cap texture on top, north and east
						}elseif($x === $sourceX + $radius && $z === $sourceZ - ($radius - 1)){
							$data = 3; // cap texture on top, north and east
						}elseif($x === $sourceX - ($radius - 1) && $z === $sourceZ + $radius){
							$data = 7; // cap texture on top, south and west
						}elseif($x === $sourceX - $radius && $z === $sourceZ + $radius - 1){
							$data = 7; // cap texture on top, south and west
						}elseif($x === $sourceX + $radius - 1 && $z === $sourceZ + $radius){
							$data = 9; // cap texture on top, east and south
						}elseif($x === $sourceX + $radius && $z === $sourceZ + $radius - 1){
							$data = 9; // cap texture on top, east and south
						}
					}

					// a $data of 5 below the top layer means air
					if($data !== 5 || $y >= $sourceY + $this->height){
						$this->transaction->addBlockAt($x, $y, $z, $blockFactory->get($this->type, $data));
					}
				}
			}
		}

		return true;
	}
}
