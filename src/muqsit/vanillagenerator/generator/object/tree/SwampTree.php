<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\utils\TreeType;
use pocketmine\block\VanillaBlocks;
use pocketmine\utils\Random;
use pocketmine\world\BlockTransaction;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\World;
use function abs;
use function array_key_exists;

class SwampTree extends CocoaTree{

	/** @var int[] */
	private static array $WATER_BLOCK_TYPES;

	public static function init() : void{
		self::$WATER_BLOCK_TYPES = [];
		foreach([BlockLegacyIds::FLOWING_WATER, BlockLegacyIds::STILL_WATER] as $blockId){
			self::$WATER_BLOCK_TYPES[$blockId] = $blockId;
		}
	}

	public function __construct(Random $random, BlockTransaction $transaction){
		parent::__construct($random, $transaction);
		$this->setOverridables(BlockLegacyIds::AIR, BlockLegacyIds::LEAVES);
		$this->setHeight($random->nextBoundedInt(4) + 5);
		$this->setType(TreeType::OAK());
	}

	public function canPlaceOn(Block $soil) : bool{
		$id = $soil->getId();
		return $id === BlockLegacyIds::GRASS || $id === BlockLegacyIds::DIRT;
	}

	public function canPlace(int $baseX, int $baseY, int $baseZ, ChunkManager $world) : bool{
		for($y = $baseY; $y <= $baseY + 1 + $this->height; ++$y){
			if($y < 0 || $y >= World::Y_MAX){ // height out of range
				return false;
			}

			// Space requirement
			$radius = 1; // default radius if above first block
			if($y === $baseY){
				$radius = 0; // radius at source block y is 0 (only trunk)
			}elseif($y >= $baseY + 1 + $this->height - 2){
				$radius = 3; // max radius starting at leaves bottom
			}
			// check for block collision on horizontal slices
			for($x = $baseX - $radius; $x <= $baseX + $radius; ++$x){
				for($z = $baseZ - $radius; $z <= $baseZ + $radius; ++$z){
					// we can overlap some blocks around
					$type = $world->getBlockAt($x, $y, $z)->getId();
					if(array_key_exists($type, $this->overridables)){
						continue;
					}

					if($type === BlockLegacyIds::FLOWING_WATER || $type === BlockLegacyIds::STILL_WATER){
						if($y > $baseY){
							return false;
						}
					}else{
						return false;
					}
				}
			}
		}
		return true;
	}

	public function generate(ChunkManager $world, Random $random, int $sourceX, int $sourceY, int $sourceZ) : bool{
		/** @var Chunk $chunk */
		$chunk = $world->getChunk($sourceX >> 4, $sourceZ >> 4);
		$chunkBlockX = $sourceX & 0x0f;
		$chunkBlockZ = $sourceZ & 0x0f;
		$blockFactory = BlockFactory::getInstance();
		while(array_key_exists($blockFactory->fromFullBlock($chunk->getFullBlock($chunkBlockX, $sourceY, $chunkBlockZ))->getId(), self::$WATER_BLOCK_TYPES)){
			--$sourceY;
		}

		++$sourceY;
		if($this->cannotGenerateAt($sourceX, $sourceY, $sourceZ, $world)){
			return false;
		}

		// generate the leaves
		for($y = $sourceY + $this->height - 3; $y <= $sourceY + $this->height; ++$y){
			$n = $y - ($sourceY + $this->height);
			$radius = (int) (2 - $n / 2);
			for($x = $sourceX - $radius; $x <= $sourceX + $radius; ++$x){
				for($z = $sourceZ - $radius; $z <= $sourceZ + $radius; ++$z){
					if(
						abs($x - $sourceX) !== $radius ||
						abs($z - $sourceZ) !== $radius ||
						($random->nextBoolean() && $n !== 0)
					){
						$this->replaceIfAirOrLeaves($x, $y, $z, $this->leavesType, $world);
					}
				}
			}
		}

		$worldHeight = $world->getMaxY();
		// generate the trunk
		for($y = 0; $y < $this->height; ++$y){
			if($sourceY + $y < $worldHeight){
				$material = $blockFactory->fromFullBlock($chunk->getFullBlock($chunkBlockX, $sourceY + $y, $chunkBlockZ))->getId();
				if(
					$material === BlockLegacyIds::AIR ||
					$material === BlockLegacyIds::LEAVES ||
					$material === BlockLegacyIds::FLOWING_WATER ||
					$material === BlockLegacyIds::STILL_WATER
				){
					$this->transaction->addBlockAt($sourceX, $sourceY + $y, $sourceZ, $this->logType);
				}
			}
		}

		// add some vines on the leaves
		$this->addVinesOnLeaves($sourceX, $sourceY, $sourceZ, $world, $random);

		$this->transaction->addBlockAt($sourceX, $sourceY - 1, $sourceZ, VanillaBlocks::DIRT());
		return true;
	}
}

SwampTree::init();
