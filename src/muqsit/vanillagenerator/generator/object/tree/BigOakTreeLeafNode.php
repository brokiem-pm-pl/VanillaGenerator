<?php

declare(strict_types=1);

namespace muqsit\vanillagenerator\generator\object\tree;

final class BigOakTreeLeafNode{
	public function __construct(
		public int $x,
		public int $y,
		public int $z,
		public int $branch_y
	){}
}
