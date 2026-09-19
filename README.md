# DebugDraw
Draw lines, boxes, spheres, arrows, text and other shapes on the client from a PocketMine-MP plugin.

## Usage
```php
$viewer = DebugDraw::viewer($player);

$box = $viewer->draw((new BoxShape(new Vector3(0, 64, 0), new Vector3(1, 1, 1)))->withColor(new Color(255, 0, 0)));
$line = $viewer->draw((new LineShape($player->getPosition(), new Vector3(0, 64, 0)))->withDuration(5.0));

$box->update($box->shape->withLocation(new Vector3(0, 65, 0)));
$box->update($box->shape->withBound(new Vector3(2, 2, 2)));

$viewer->removeAll([$line, $box]);
$viewer->clear();
```

`draw()` returns a `DrawnShape<T>` where `T` is the class you passed in, so `$box->shape` is a `BoxShape` and
`$box->update(new LineShape(...))` fails static analysis. `drawAll()`, `removeAll()` and `clear()` send one packet
for the whole list. The viewer is dropped when the player quits.

## Shapes
Namespace `kostamax27\debugdraw\shape`. Every class is `final readonly`, validates its arguments in the constructor and
takes an optional trailing `?ShapeStyle $style = null`.

| Class            | Constructor                                                                                                                                                                                                          |
|------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `LineShape`      | `(Vector3 $location, Vector3 $end)`                                                                                                                                                                                  |
| `BoxShape`       | `(Vector3 $location, Vector3 $bound)`                                                                                                                                                                                | min corner and size |
| `SphereShape`    | `(Vector3 $location, int $segments = 20)`                                                                                                                                                                            |
| `CircleShape`    | `(Vector3 $location, int $segments = 20)`                                                                                                                                                                            |
| `TextShape`      | `(Vector3 $location, string $text, bool $use_rotation = false, ?Color $background_color = null, float $line_gap_height = 0.0, bool $depth_test = true, bool $show_backface = true, bool $show_text_backface = true)` |
| `ArrowShape`     | `(Vector3 $location, Vector3 $end, ?float $head_length = null, ?float $head_radius = null, ?int $segments = null)`                                                                                                   |
| `CylinderShape`  | `(Vector3 $location, Vector2 $radius_x, Vector2 $radius_z, float $height, int $segments = 20)`                                                                                                                       |
| `PyramidShape`   | `(Vector3 $location, float $width, float $height, ?float $depth = null)`                                                                                                                                             |
| `EllipsoidShape` | `(Vector3 $location, Vector3 $radii, int $segments_per_axis = 20)`                                                                                                                                                   |
| `ConeShape`      | `(Vector3 $location, Vector2 $radii, float $height, int $segments = 20)`                                                                                                                                             |

Options shared by all shapes live in `ShapeStyle`: `scale` (default 1), `rotation`, `duration` (seconds, client
removes the shape itself), `max_render_distance`, `color` (default white), `dimension_id` (`DimensionIds::*`,
default overworld), `attached_entity_id` (runtime id). `location` is the centre of a shape, except for `LineShape` and `ArrowShape` where it is the start point and
`BoxShape` where it is the min corner. The
client hides a shape once the player is further from its `location` than its render distance; `max_render_distance`
can only lower that limit, not raise it, so a long line has to be split into segments to stay visible along its
whole length. Each shape has `withScale()`, `withRotation()`, `withDuration()`, `withMaxRenderDistance()`, `withColor()`,
`withDimension()`, `attachedTo()` and `withStyle()`, all returning a new instance of the same class.

## Broadcasting
```php
$handles = DebugDraw::broadcast($world->getPlayers(), new TextShape(new Vector3(0, 70, 0), "spawn")); // list<DrawnShape<TextShape>>
```

## Examples

### 1. Chunk borders (F3 + G)
Chunk borders as drawn by Minecraft: Java Edition (F3 + G, `ChunkBorderRenderer`). Red lines mark the corners of the
neighbouring chunks, yellow and cyan lines split the current chunk every 2 and 4 blocks, blue lines mark the chunk
corners and every subchunk, and the subchunk the player stands in is boxed. Bedrock has no line width, so the thick
lines are drawn like the thin ones.

```php
/**
 * @return non-empty-list<DrawnShape<covariant Shape>>
 */
public static function renderChunkBorder(Player $player) : array{
	$red = ShapeStyle::default()->withColor(new Color(255, 0, 0, 128));
	$yellow = ShapeStyle::default()->withColor(new Color(255, 255, 0));
	$cyan = ShapeStyle::default()->withColor(new Color(0, 155, 155));
	$blue = ShapeStyle::default()->withColor(new Color(64, 64, 255));

	$position = $player->getPosition();
	$x0 = ($position->getFloorX() >> Chunk::COORD_BIT_SIZE) << Chunk::COORD_BIT_SIZE;
	$y0 = ($position->getFloorY() >> Chunk::COORD_BIT_SIZE) << Chunk::COORD_BIT_SIZE;
	$z0 = ($position->getFloorZ() >> Chunk::COORD_BIT_SIZE) << Chunk::COORD_BIT_SIZE;

	$shapes = [];
	$vertical = static function(int $x, int $z, ShapeStyle $style) use(&$shapes) : void{
		for($y = World::Y_MIN; $y < World::Y_MAX; $y += 64){
			$shapes[] = new LineShape(new Vector3($x, $y, $z), new Vector3($x, min($y + 64, World::Y_MAX), $z), $style);
		}
	};
	for($x = -16; $x <= 32; $x += 16){
		for($z = -16; $z <= 32; $z += 16){
			$vertical($x0 + $x, $z0 + $z, $red);
		}
	}
	for($i = 2; $i < 16; $i += 2){
		$style = $i % 4 === 0 ? $cyan : $yellow;
		$vertical($x0 + $i, $z0, $style);
		$vertical($x0 + $i, $z0 + 16, $style);
		$vertical($x0, $z0 + $i, $style);
		$vertical($x0 + 16, $z0 + $i, $style);
	}
	for($y = World::Y_MIN; $y <= World::Y_MAX; $y += 2){
		$style = $y % 16 === 0 ? $blue : ($y % 8 === 0 ? $cyan : $yellow);
		$shapes[] = new LineShape(new Vector3($x0, $y, $z0), new Vector3($x0, $y, $z0 + 16), $style);
		$shapes[] = new LineShape(new Vector3($x0, $y, $z0 + 16), new Vector3($x0 + 16, $y, $z0 + 16), $style);
		$shapes[] = new LineShape(new Vector3($x0 + 16, $y, $z0 + 16), new Vector3($x0 + 16, $y, $z0), $style);
		$shapes[] = new LineShape(new Vector3($x0 + 16, $y, $z0), new Vector3($x0, $y, $z0), $style);
	}
	for($x = 0; $x <= 16; $x += 16){
		for($z = 0; $z <= 16; $z += 16){
			$vertical($x0 + $x, $z0 + $z, $blue);
		}
	}
	$shapes[] = new BoxShape(new Vector3($x0, $y0, $z0), new Vector3(16, 16, 16), $blue);
	return DebugDraw::viewer($player)->drawAll($shapes);
}
```

<details align="center">
	<summary>See demo</summary>

<img width="2560" height="1440" alt="image" src="https://github.com/user-attachments/assets/87efe315-666b-4190-a932-e1a6dccdcf21" />

</details>

### 2. Clock
An analog clock showing the server's local time. The face (ring, ticks, digits) is drawn once, the three hands are
updated in place every tick. `$facing` is the side the clock is looked at from: `Facing::UP` lays it on the ground
for a viewer above, `Facing::SOUTH` hangs it on a wall for a viewer standing south of it and looking north. Seen from
the other side it runs backwards, like a real clock would.

```php
final class Clock{
	/** @var non-empty-list<DrawnShape<LineShape|TextShape>> */
	private array $face;

	/** @var array{DrawnShape<LineShape>, DrawnShape<LineShape>, DrawnShape<LineShape>} hour, minute, second */
	private array $hands;

	private Vector3 $up; // 12 o'clock
	private Vector3 $right; // 3 o'clock
	private Vector3 $rotation; // pitch, yaw, roll of text facing the viewer

	/**
	 * @param Facing::UP|Facing::DOWN|Facing::NORTH|Facing::SOUTH|Facing::EAST|Facing::WEST $facing side the face is readable from
	 */
	public function __construct(
		readonly private ShapeViewer $viewer,
		readonly private Vector3 $center,
		readonly private float $radius,
		int $facing = Facing::UP
	){
		[$this->up, $this->right, $this->rotation] = match($facing){
			Facing::UP => [new Vector3(0, 0, -1), new Vector3(1, 0, 0), new Vector3(-90, 0, 0)],
			Facing::DOWN => [new Vector3(0, 0, -1), new Vector3(-1, 0, 0), new Vector3(90, 0, 0)],
			Facing::NORTH => [new Vector3(0, 1, 0), new Vector3(-1, 0, 0), new Vector3(0, 180, 0)],
			Facing::SOUTH => [new Vector3(0, 1, 0), new Vector3(1, 0, 0), new Vector3(0, 0, 0)],
			Facing::EAST => [new Vector3(0, 1, 0), new Vector3(0, 0, -1), new Vector3(0, 270, 0)],
			Facing::WEST => [new Vector3(0, 1, 0), new Vector3(0, 0, 1), new Vector3(0, 90, 0)],
			default => throw new InvalidArgumentException("Invalid facing {$facing}")
		};
		$white = ShapeStyle::default()->withColor(DyeColor::WHITE->getRgbValue());
		$shapes = [];
		for($i = 0; $i < 60; $i++){
			$shapes[] = new LineShape($this->point($i / 60, $radius), $this->point(($i + 1) / 60, $radius), $white);
		}
		for($i = 0; $i < 12; $i++){
			$shapes[] = new LineShape($this->point($i / 12, $radius * 0.9), $this->point($i / 12, $radius), $white);
		}
		for($hour = 1; $hour <= 12; $hour++){
			$shapes[] = new TextShape($this->point($hour / 12, $radius * 0.8), (string) $hour, use_rotation: true, show_text_backface: false, style: ShapeStyle::default()->withRotation($this->rotation));
		}
		$this->face = $viewer->drawAll($shapes);

		$red = ShapeStyle::default()->withColor(DyeColor::RED->getRgbValue());
		[$hour, $minute, $second] = $this->time();
		[$hour_hand, $minute_hand, $second_hand] = $viewer->drawAll([
			new LineShape($center, $this->point($hour, $radius * 0.5), $white),
			new LineShape($center, $this->point($minute, $radius * 0.8), $white),
			new LineShape($center, $this->point($second, $radius * 0.95), $red)
		]);
		$this->hands = [$hour_hand, $minute_hand, $second_hand];
	}

	public function tick() : void{
		[$hour, $minute, $second] = $this->time();
		[$hour_hand, $minute_hand, $second_hand] = $this->hands;
		$hour_hand->update($hour_hand->shape->withEnd($this->point($hour, $this->radius * 0.5)));
		$minute_hand->update($minute_hand->shape->withEnd($this->point($minute, $this->radius * 0.8)));
		$second_hand->update($second_hand->shape->withEnd($this->point($second, $this->radius * 0.95)));
	}

	public function remove() : void{
		$this->viewer->removeAll([...$this->face, ...$this->hands]);
	}

	/**
	 * @return array{float, float, float} hour, minute and second as a fraction of a full turn
	 */
	private function time() : array{
		$now = microtime(true);
		$second = (int) date("s", (int) $now) + fmod($now, 1.0);
		$minute = (int) date("i", (int) $now) + $second / 60;
		$hour = (int) date("h", (int) $now) + $minute / 60;
		return [$hour / 12, $minute / 60, $second / 60];
	}

	/**
	 * @param float $turn fraction of a full turn clockwise from 12 o'clock
	 */
	private function point(float $turn, float $distance) : Vector3{
		$angle = $turn * 2 * M_PI;
		return $this->center
			->addVector($this->up->multiply(cos($angle) * $distance))
			->addVector($this->right->multiply(sin($angle) * $distance));
	}
}
```

```php
$clock = new Clock(DebugDraw::viewer($player), $player->getPosition()->up(3), 2.0, Facing::SOUTH);
$this->getScheduler()->scheduleRepeatingTask(new ClosureTask($clock->tick(...)), 1);
```

<details align="center">
	<summary>See demo</summary>

https://github.com/user-attachments/assets/b5412735-0b7c-440e-a209-8d4795834733

</details>
