<?php

declare(strict_types=1);

namespace kostamax27\debugdraw\shape;

use InvalidArgumentException;
use kostamax27\debugdraw\Shape;
use kostamax27\debugdraw\ShapeStyle;
use kostamax27\debugdraw\ShapeTrait;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeConePayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

/**
 * A cone standing on $location. $radii holds the base radius along the x (x)
 * and z (y) axes.
 */
final readonly class ConeShape implements Shape{
	use ShapeTrait;

	public ShapeStyle $style;

	/**
	 * @param positive-int $segments
	 */
	public function __construct(
		public Vector3 $location,
		public Vector2 $radii,
		public float $height,
		public int $segments = 20,
		?ShapeStyle $style = null
	){
		$height > 0 || throw new InvalidArgumentException("Height must be positive, got {$height}");
		$segments > 0 || throw new InvalidArgumentException("Segments must be positive, got {$segments}");
		$this->style = $style ?? ShapeStyle::default();
	}

	public function withLocation(Vector3 $location) : static{
		return new self($location, $this->radii, $this->height, $this->segments, $this->style);
	}

	public function withStyle(ShapeStyle $style) : static{
		return new self($this->location, $this->radii, $this->height, $this->segments, $style);
	}

	public function toPacketData(int $network_id) : PacketShapeData{
		return $this->createPacketData($network_id, PrimitiveShapeType::CONE, new PrimitiveShapeConePayload($this->radii, $this->height, $this->segments));
	}
}
