<?php

declare(strict_types=1);

namespace kostamax27\debugdraw\shape;

use kostamax27\debugdraw\Shape;
use kostamax27\debugdraw\ShapeStyle;
use kostamax27\debugdraw\ShapeTrait;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeLinePayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

final readonly class LineShape implements Shape{
	use ShapeTrait;

	public ShapeStyle $style;

	public function __construct(
		public Vector3 $location,
		public Vector3 $end,
		?ShapeStyle $style = null
	){
		$this->style = $style ?? ShapeStyle::default();
	}

	public function withLocation(Vector3 $location) : static{
		return new self($location, $this->end, $this->style);
	}

	public function withStyle(ShapeStyle $style) : static{
		return new self($this->location, $this->end, $style);
	}

	public function withEnd(Vector3 $end) : static{
		return new self($this->location, $end, $this->style);
	}

	public function toPacketData(int $network_id) : PacketShapeData{
		return $this->createPacketData($network_id, PrimitiveShapeType::LINE, new PrimitiveShapeLinePayload($this->end));
	}
}
