<?php

declare(strict_types=1);

namespace kostamax27\debugdraw\shape;

use InvalidArgumentException;
use kostamax27\debugdraw\Shape;
use kostamax27\debugdraw\ShapeStyle;
use kostamax27\debugdraw\ShapeTrait;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeArrowPayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

/**
 * A line from $location to $end with an arrow head at $end. Null head values
 * use the client default.
 */
final readonly class ArrowShape implements Shape{
	use ShapeTrait;

	public ShapeStyle $style;

	/**
	 * @param positive-int|null $segments
	 */
	public function __construct(
		public Vector3 $location,
		public Vector3 $end,
		public ?float $head_length = null,
		public ?float $head_radius = null,
		public ?int $segments = null,
		?ShapeStyle $style = null
	){
		$head_length === null || $head_length > 0 || throw new InvalidArgumentException("Arrow head length must be positive, got {$head_length}");
		$head_radius === null || $head_radius > 0 || throw new InvalidArgumentException("Arrow head radius must be positive, got {$head_radius}");
		$segments === null || $segments > 0 || throw new InvalidArgumentException("Segments must be positive, got {$segments}");
		$this->style = $style ?? ShapeStyle::default();
	}

	public function withLocation(Vector3 $location) : static{
		return new self($location, $this->end, $this->head_length, $this->head_radius, $this->segments, $this->style);
	}

	public function withStyle(ShapeStyle $style) : static{
		return new self($this->location, $this->end, $this->head_length, $this->head_radius, $this->segments, $style);
	}

	public function withEnd(Vector3 $end) : static{
		return new self($this->location, $end, $this->head_length, $this->head_radius, $this->segments, $this->style);
	}

	public function toPacketData(int $network_id) : PacketShapeData{
		return $this->createPacketData($network_id, PrimitiveShapeType::ARROW, new PrimitiveShapeArrowPayload($this->end, $this->head_length, $this->head_radius, $this->segments));
	}
}
