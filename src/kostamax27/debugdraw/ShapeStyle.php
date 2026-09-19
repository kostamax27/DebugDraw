<?php

declare(strict_types=1);

namespace kostamax27\debugdraw;

use InvalidArgumentException;
use pocketmine\color\Color;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\DimensionIds;

/**
 * Options shared by all shape types. Every field is optional; the client
 * uses its own default when a field is null.
 */
final readonly class ShapeStyle{
	public static function default() : self{
		return new self(null, null, null, null, null, null, null);
	}

	/**
	 * @param float|null $scale null for 1.0
	 * @param Vector3|null $rotation euler angles in degrees
	 * @param float|null $duration seconds until the client removes the shape by itself
	 * @param float|null $max_render_distance blocks from $location beyond which the client hides the shape; capped by the client's render distance
	 * @param Color|null $color null for white
	 * @param DimensionIds::*|null $dimension_id null for the overworld
	 * @param positive-int|null $attached_entity_id entity runtime id; $location becomes relative to this entity
	 */
	public function __construct(
		public ?float $scale,
		public ?Vector3 $rotation,
		public ?float $duration,
		public ?float $max_render_distance,
		public ?Color $color,
		public ?int $dimension_id,
		public ?int $attached_entity_id
	){
		$scale === null || $scale > 0 || throw new InvalidArgumentException("Scale must be positive, got {$scale}");
		$duration === null || $duration > 0 || throw new InvalidArgumentException("Duration must be positive, got {$duration}");
		$max_render_distance === null || $max_render_distance > 0 || throw new InvalidArgumentException("Render distance must be positive, got {$max_render_distance}");
	}

	public function withScale(?float $scale) : self{
		return new self($scale, $this->rotation, $this->duration, $this->max_render_distance, $this->color, $this->dimension_id, $this->attached_entity_id);
	}

	public function withRotation(?Vector3 $rotation) : self{
		return new self($this->scale, $rotation, $this->duration, $this->max_render_distance, $this->color, $this->dimension_id, $this->attached_entity_id);
	}

	public function withDuration(?float $duration) : self{
		return new self($this->scale, $this->rotation, $duration, $this->max_render_distance, $this->color, $this->dimension_id, $this->attached_entity_id);
	}

	public function withMaxRenderDistance(?float $distance) : self{
		return new self($this->scale, $this->rotation, $this->duration, $distance, $this->color, $this->dimension_id, $this->attached_entity_id);
	}

	public function withColor(?Color $color) : self{
		return new self($this->scale, $this->rotation, $this->duration, $this->max_render_distance, $color, $this->dimension_id, $this->attached_entity_id);
	}

	/**
	 * @param DimensionIds::*|null $dimension_id null for the overworld
	 */
	public function withDimension(?int $dimension_id) : self{
		return new self($this->scale, $this->rotation, $this->duration, $this->max_render_distance, $this->color, $dimension_id, $this->attached_entity_id);
	}

	/**
	 * @param positive-int|null $entity_runtime_id
	 */
	public function attachedTo(?int $entity_runtime_id) : self{
		return new self($this->scale, $this->rotation, $this->duration, $this->max_render_distance, $this->color, $this->dimension_id, $entity_runtime_id);
	}
}
