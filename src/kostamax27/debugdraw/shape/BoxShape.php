<?php

declare(strict_types=1);

namespace kostamax27\debugdraw\shape;

use InvalidArgumentException;
use kostamax27\debugdraw\Shape;
use kostamax27\debugdraw\ShapeStyle;
use kostamax27\debugdraw\ShapeTrait;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeBoxPayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

/**
 * A box with its min corner at $location and size $bound along each axis (multiplied by scale).
 */
final readonly class BoxShape implements Shape{
	use ShapeTrait;

	public ShapeStyle $style;

	public function __construct(
		public Vector3 $location,
		public Vector3 $bound,
		?ShapeStyle $style = null
	){
		($bound->x >= 0 && $bound->y >= 0 && $bound->z >= 0) || throw new InvalidArgumentException("Box bound must be non-negative, got {$bound}");
		$this->style = $style ?? ShapeStyle::default();
	}

	public function withLocation(Vector3 $location) : static{
		return new self($location, $this->bound, $this->style);
	}

	public function withStyle(ShapeStyle $style) : static{
		return new self($this->location, $this->bound, $style);
	}

	public function withBound(Vector3 $bound) : static{
		return new self($this->location, $bound, $this->style);
	}

	public function toPacketData(int $network_id) : PacketShapeData{
		return $this->createPacketData($network_id, PrimitiveShapeType::BOX, new PrimitiveShapeBoxPayload($this->bound));
	}
}
