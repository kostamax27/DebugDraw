<?php

declare(strict_types=1);

namespace kostamax27\debugdraw\shape;

use InvalidArgumentException;
use kostamax27\debugdraw\Shape;
use kostamax27\debugdraw\ShapeStyle;
use kostamax27\debugdraw\ShapeTrait;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapePyramidPayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

/**
 * A pyramid standing on $location. $depth defaults to $width.
 */
final readonly class PyramidShape implements Shape{
	use ShapeTrait;

	public ShapeStyle $style;

	public function __construct(
		public Vector3 $location,
		public float $width,
		public float $height,
		public ?float $depth = null,
		?ShapeStyle $style = null
	){
		$width > 0 || throw new InvalidArgumentException("Width must be positive, got {$width}");
		$height > 0 || throw new InvalidArgumentException("Height must be positive, got {$height}");
		$depth === null || $depth > 0 || throw new InvalidArgumentException("Depth must be positive, got {$depth}");
		$this->style = $style ?? ShapeStyle::default();
	}

	public function withLocation(Vector3 $location) : static{
		return new self($location, $this->width, $this->height, $this->depth, $this->style);
	}

	public function withStyle(ShapeStyle $style) : static{
		return new self($this->location, $this->width, $this->height, $this->depth, $style);
	}

	public function toPacketData(int $network_id) : PacketShapeData{
		return $this->createPacketData($network_id, PrimitiveShapeType::PYRAMID, new PrimitiveShapePyramidPayload($this->width, $this->depth, $this->height));
	}
}
