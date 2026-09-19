<?php

declare(strict_types=1);

namespace kostamax27\debugdraw;

use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;

/**
 * A shape that can be drawn for a player. Implementations are immutable and
 * live in the shape namespace, one per primitive type.
 */
interface Shape{
	public Vector3 $location{ get; }

	public ShapeStyle $style{ get; }

	/**
	 * Returns a copy of this shape at $location.
	 */
	public function withLocation(Vector3 $location) : static;

	/**
	 * Returns a copy of this shape with $style.
	 */
	public function withStyle(ShapeStyle $style) : static;

	/**
	 * @param positive-int $network_id
	 */
	public function toPacketData(int $network_id) : PacketShapeData;
}
