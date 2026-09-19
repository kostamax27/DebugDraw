<?php

declare(strict_types=1);

namespace kostamax27\debugdraw\shape;

use InvalidArgumentException;
use kostamax27\debugdraw\Shape;
use kostamax27\debugdraw\ShapeStyle;
use kostamax27\debugdraw\ShapeTrait;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeEllipsoidPayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

/**
 * An ellipsoid centered at $location with radius $radii along each axis.
 */
final readonly class EllipsoidShape implements Shape{
	use ShapeTrait;

	public ShapeStyle $style;

	/**
	 * @param positive-int $segments_per_axis
	 */
	public function __construct(
		public Vector3 $location,
		public Vector3 $radii,
		public int $segments_per_axis = 20,
		?ShapeStyle $style = null
	){
		($radii->x > 0 && $radii->y > 0 && $radii->z > 0) || throw new InvalidArgumentException("Radii must be positive, got {$radii}");
		$segments_per_axis > 0 || throw new InvalidArgumentException("Segments per axis must be positive, got {$segments_per_axis}");
		$this->style = $style ?? ShapeStyle::default();
	}

	public function withLocation(Vector3 $location) : static{
		return new self($location, $this->radii, $this->segments_per_axis, $this->style);
	}

	public function withStyle(ShapeStyle $style) : static{
		return new self($this->location, $this->radii, $this->segments_per_axis, $style);
	}

	public function toPacketData(int $network_id) : PacketShapeData{
		return $this->createPacketData($network_id, PrimitiveShapeType::ELLIPSOID, new PrimitiveShapeEllipsoidPayload($this->radii, $this->segments_per_axis));
	}
}
