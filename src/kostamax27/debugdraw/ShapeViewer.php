<?php

declare(strict_types=1);

namespace kostamax27\debugdraw;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\PrimitiveShapesPacket;
use pocketmine\network\mcpe\protocol\types\DimensionIds;
use pocketmine\network\mcpe\protocol\types\shape\PacketShapeData;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\TaskScheduler;
use function array_values;
use function ceil;
use function count;

/**
 * Draws shapes for one player. Obtain one from {@see DebugDraw::viewer()}.
 */
final class ShapeViewer{
	/** @var positive-int */
	private int $next_network_id = 1;

	/** @var array<positive-int, DrawnShape<covariant Shape>> */
	private array $shapes = [];

	public function __construct(
		readonly public Player $player,
		readonly private TaskScheduler $scheduler
	){}

	/**
	 * Draws a shape and returns a handle to update or remove it later.
	 *
	 * @template TShape of Shape
	 * @param TShape $shape
	 * @return DrawnShape<TShape>
	 */
	public function draw(Shape $shape) : DrawnShape{
		return $this->drawAll([$shape])[0];
	}

	/**
	 * Draws all shapes in a single packet.
	 *
	 * @template TShape of Shape
	 * @param non-empty-list<TShape> $shapes
	 * @return non-empty-list<DrawnShape<TShape>>
	 */
	public function drawAll(array $shapes) : array{
		$drawn = [];
		$data = [];
		foreach($shapes as $shape){
			$network_id = $this->next_network_id++;
			$drawn[] = $this->shapes[$network_id] = new DrawnShape($this, $network_id, $shape);
			$data[] = $shape->toPacketData($network_id);
			$this->scheduleExpiry($network_id, $shape);
		}
		$this->send($data);
		return $drawn;
	}

	/**
	 * @param DrawnShape<covariant Shape> $drawn
	 */
	public function isDrawn(DrawnShape $drawn) : bool{
		return ($this->shapes[$drawn->network_id] ?? null) === $drawn;
	}

	/**
	 * Sends $shape in place of $drawn under the same network id. Use
	 * {@see DrawnShape::update()} to also update the handle.
	 *
	 * @template TShape of Shape
	 * @param DrawnShape<TShape> $drawn
	 * @param TShape $shape
	 */
	public function replace(DrawnShape $drawn, Shape $shape) : void{
		$this->isDrawn($drawn) || throw new InvalidArgumentException("Shape #{$drawn->network_id} is not drawn for {$this->player->getName()}");
		$this->send([$shape->toPacketData($drawn->network_id)]);
		$this->scheduleExpiry($drawn->network_id, $shape);
	}

	/**
	 * @param DrawnShape<covariant Shape> $drawn
	 */
	public function remove(DrawnShape $drawn) : void{
		$this->removeAll([$drawn]);
	}

	/**
	 * Removes all shapes in a single packet. Shapes no longer drawn are ignored.
	 *
	 * @param list<DrawnShape<covariant Shape>> $drawn
	 */
	public function removeAll(array $drawn) : void{
		$data = [];
		foreach($drawn as $shape){
			if(!$this->isDrawn($shape)){
				continue;
			}
			unset($this->shapes[$shape->network_id]);
			$data[] = PacketShapeData::remove($shape->network_id, $shape->shape->style->dimension_id ?? DimensionIds::OVERWORLD);
		}
		$this->send($data);
	}

	/**
	 * Removes every shape drawn by this viewer in a single packet.
	 */
	public function clear() : void{
		$this->removeAll(array_values($this->shapes));
	}

	/**
	 * @return list<DrawnShape<covariant Shape>>
	 */
	public function getDrawn() : array{
		return array_values($this->shapes);
	}

	/**
	 * @param list<PacketShapeData> $data
	 */
	private function send(array $data) : void{
		if(count($data) === 0 || !$this->player->isConnected()){
			return;
		}
		$this->player->getNetworkSession()->sendDataPacket(PrimitiveShapesPacket::create($data));
	}

	/**
	 * @param positive-int $network_id
	 */
	private function scheduleExpiry(int $network_id, Shape $shape) : void{
		if($shape->style->duration === null){
			return;
		}
		$this->scheduler->scheduleDelayedTask(new ClosureTask(function() use($network_id, $shape) : void{
			if(isset($this->shapes[$network_id]) && $this->shapes[$network_id]->shape === $shape){ // not replaced since
				unset($this->shapes[$network_id]);
			}
		}), (int) ceil($shape->style->duration * 20));
	}
}
