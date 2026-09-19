<?php

declare(strict_types=1);

namespace kostamax27\debugdraw;

/**
 * A shape drawn for one player, obtained from {@see ShapeViewer::draw()}.
 *
 * @template TShape of Shape
 */
final class DrawnShape{
	/**
	 * @param positive-int $network_id
	 * @param TShape $shape
	 */
	public function __construct(
		readonly private ShapeViewer $viewer,
		readonly public int $network_id,
		public private(set) Shape $shape
	){}

	public function isDrawn() : bool{
		return $this->viewer->isDrawn($this);
	}

	/**
	 * Replaces the shape on the client, keeping the same network id.
	 *
	 * @param TShape $shape
	 */
	public function update(Shape $shape) : void{
		$this->viewer->replace($this, $shape);
		$this->shape = $shape;
	}

	public function remove() : void{
		$this->viewer->remove($this);
	}
}
