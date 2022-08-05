<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator;

use muqsit\vanillagenerator\generator\biomegrid\MapLayer;
use muqsit\vanillagenerator\generator\biomegrid\utils\MapLayerPair;
use muqsit\vanillagenerator\generator\overworld\WorldType;
use muqsit\vanillagenerator\generator\utils\preset\GeneratorPreset;
use muqsit\vanillagenerator\generator\utils\WorldOctaves;
use pocketmine\world\ChunkManager;
use pocketmine\world\format\Chunk;
use pocketmine\world\generator\Generator;
use pocketmine\world\World;
use function array_push;
use function count;

/**
 * @phpstan-template T of WorldOctaves
 */
abstract class VanillaGenerator extends Generator{

	/** @phpstan-var T */
	private ?WorldOctaves $octaveCache = null;

	/** @var Populator[] */
	private array $populators = [];

	private MapLayerPair $biomeGrid;

	public function __construct(int $seed, int $environment, ?string $worldType, GeneratorPreset $preset){
		parent::__construct($seed, $preset->toString());
		$this->biomeGrid = MapLayer::initialize($seed, $environment, $worldType ?? WorldType::NORMAL);
	}

	/**
	 * @return int[]
	 */
	public function getBiomeGridAtLowerRes(int $x, int $z, int $sizeX, int $sizeZ) : array{
		return $this->biomeGrid->lowResolution->generateValues($x, $z, $sizeX, $sizeZ);
	}

	/**
	 * @return int[]
	 */
	public function getBiomeGrid(int $x, int $z, int $sizeX, int $sizeZ) : array{
		return $this->biomeGrid->highResolution->generateValues($x, $z, $sizeX, $sizeZ);
	}

	protected function addPopulators(Populator ...$populators) : void{
		array_push($this->populators, ...$populators);
	}

	/**
	 * @phpstan-return T
	 */
	abstract protected function createWorldOctaves() : WorldOctaves;

	public function generateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		$biomes = new VanillaBiomeGrid();
		$biomeValues = $this->biomeGrid->highResolution->generateValues($chunkX * 16, $chunkZ * 16, 16, 16);
		for($i = 0, $biomeValuesC = count($biomeValues); $i < $biomeValuesC; ++$i){
			$biomes->biomes[$i] = $biomeValues[$i];
		}

		$this->generateChunkData($world, $chunkX, $chunkZ, $biomes);
	}

	abstract protected function generateChunkData(ChunkManager $world, int $chunkX, int $chunkZ, VanillaBiomeGrid $biomes) : void;

	/**
	 * @phpstan-return T
	 */
	final protected function getWorldOctaves() : WorldOctaves{
		return $this->octaveCache ??= $this->createWorldOctaves();
	}

	/**
	 * @return Populator[]
	 */
	public function getDefaultPopulators() : array{
		return $this->populators;
	}

	public function populateChunk(ChunkManager $world, int $chunkX, int $chunkZ) : void{
		/** @var Chunk $chunk */
		$chunk = $world->getChunk($chunkX, $chunkZ);
		foreach($this->populators as $populator){
			$populator->populate($world, $this->random, $chunkX, $chunkZ, $chunk);
		}
	}

	public function getMaxY() : int{
		return World::Y_MAX;
	}
}
