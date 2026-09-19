<?php

declare(strict_types=1);

namespace kostamax27\debugdraw\shape;

use InvalidArgumentException;
use kostamax27\debugdraw\Shape;
use kostamax27\debugdraw\ShapeStyle;
use kostamax27\debugdraw\ShapeTrait;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeTextPayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

final readonly class TextShape implements Shape{
	use ShapeTrait;

	public ShapeStyle $style;

	public function __construct(
		public Vector3 $location,
		public string $text,
		public bool $use_rotation = false,
		public ?Color $background_color = null,
		public float $line_gap_height = 0.0,
		public bool $depth_test = true,
		public bool $show_backface = true,
		public bool $show_text_backface = true,
		?ShapeStyle $style = null
	){
		$line_gap_height >= 0 || throw new InvalidArgumentException("Line gap height must be non-negative, got {$line_gap_height}");
		$this->style = $style ?? ShapeStyle::default();
	}

	public function withLocation(Vector3 $location) : static{
		return new self($location, $this->text, $this->use_rotation, $this->background_color, $this->line_gap_height, $this->depth_test, $this->show_backface, $this->show_text_backface, $this->style);
	}

	public function withStyle(ShapeStyle $style) : static{
		return new self($this->location, $this->text, $this->use_rotation, $this->background_color, $this->line_gap_height, $this->depth_test, $this->show_backface, $this->show_text_backface, $style);
	}

	public function withText(string $text) : static{
		return new self($this->location, $text, $this->use_rotation, $this->background_color, $this->line_gap_height, $this->depth_test, $this->show_backface, $this->show_text_backface, $this->style);
	}

	public function toPacketData(int $network_id) : PacketShapeData{
		return $this->createPacketData($network_id, PrimitiveShapeType::TEXT, new PrimitiveShapeTextPayload($this->text, $this->use_rotation, $this->background_color, $this->line_gap_height, $this->depth_test, $this->show_backface, $this->show_text_backface));
	}
}
