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
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeCylinderPayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

/**
 * A cylinder standing on $location. $radius_x and $radius_z hold the bottom (x)
 * and top (y) radius along that axis.
 */
final readonly class CylinderShape implements Shape{
	use ShapeTrait;

	public ShapeStyle $style;

	/**
	 * @param positive-int $segments
	 */
	public function __construct(
		public Vector3 $location,
		public Vector2 $radius_x,
		public Vector2 $radius_z,
		public float $height,
		public int $segments = 20,
		?ShapeStyle $style = null
	){
		$height > 0 || throw new InvalidArgumentException("Height must be positive, got {$height}");
		$segments > 0 || throw new InvalidArgumentException("Segments must be positive, got {$segments}");
		$this->style = $style ?? ShapeStyle::default();
	}

	public function withLocation(Vector3 $location) : static{
		return new self($location, $this->radius_x, $this->radius_z, $this->height, $this->segments, $this->style);
	}

	public function withStyle(ShapeStyle $style) : static{
		return new self($this->location, $this->radius_x, $this->radius_z, $this->height, $this->segments, $style);
	}

	public function toPacketData(int $network_id) : PacketShapeData{
		return $this->createPacketData($network_id, PrimitiveShapeType::CYLINDER, new PrimitiveShapeCylinderPayload($this->radius_x, $this->radius_z, $this->height, $this->segments));
	}
}
