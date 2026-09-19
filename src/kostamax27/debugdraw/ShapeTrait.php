<?php

declare(strict_types=1);

namespace kostamax27\debugdraw;

use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapePayload;
use pocketmine\network\mcpe\protocol\types\shape\PrimitiveShapeType;

/**
 * Style shortcuts and the packet data builder shared by all shape types.
 */
trait ShapeTrait{
	abstract public function withStyle(ShapeStyle $style) : static;

	public function withScale(?float $scale) : static{
		return $this->withStyle($this->style->withScale($scale));
	}

	public function withRotation(?Vector3 $rotation) : static{
		return $this->withStyle($this->style->withRotation($rotation));
	}

	public function withDuration(?float $duration) : static{
		return $this->withStyle($this->style->withDuration($duration));
	}

	public function withMaxRenderDistance(?float $distance) : static{
		return $this->withStyle($this->style->withMaxRenderDistance($distance));
	}

	public function withColor(?Color $color) : static{
		return $this->withStyle($this->style->withColor($color));
	}

	/**
	 * @param DimensionIds::*|null $dimension_id
	 */
	public function withDimension(?int $dimension_id) : static{
		return $this->withStyle($this->style->withDimension($dimension_id));
	}

	/**
	 * @param positive-int|null $entity_runtime_id
	 */
	public function attachedTo(?int $entity_runtime_id) : static{
		return $this->withStyle($this->style->attachedTo($entity_runtime_id));
	}

	/**
	 * @param positive-int $network_id
	 */
	private function createPacketData(int $network_id, PrimitiveShapeType $type, PrimitiveShapePayload $payload) : PacketShapeData{
		$style = $this->style;
		return new PacketShapeData(
			$network_id, $type, $this->location, $style->scale ?? 1.0, $style->rotation,
			$style->duration, $style->max_render_distance, $style->color ?? new Color(255, 255, 255), $style->dimension_id ?? DimensionIds::OVERWORLD,
			$style->attached_entity_id, $payload
		);
	}
}
