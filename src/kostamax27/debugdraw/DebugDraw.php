<?php

declare(strict_types=1);

namespace kostamax27\debugdraw;

use BadMethodCallException;
use InvalidArgumentException;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use PrefixedLogger;
use ReflectionClass;

final class DebugDraw{
	private static ShapeViewerManager $manager;

	public static function isRegistered() : bool{
		return isset(self::$manager);
	}

	public static function register(Plugin $plugin) : void{
		isset(self::$manager) && throw new BadMethodCallException(__CLASS__ . " is already registered");
		self::$manager = new ShapeViewerManager($plugin);
	}

	private static function managerNotFound() : ShapeViewerManager{
		$class = new ReflectionClass(self::class);
		$logger = new PrefixedLogger(Server::getInstance()->getLogger(), $class->getShortName());
		$logger->warning("Property \$manager has not been initialized.");
		$logger->warning("This means you likely forgot to register this library on plugin enable.");
		$logger->warning("You can address this error by updating your plugin's onEnable method as below:");
		$logger->warning("```");
		$logger->warning("use {$class->name};");
		$logger->warning("protected function onEnable() : void{");
		$logger->warning("    if(!{$class->getShortName()}::isRegistered()){");
		$logger->warning("        {$class->getShortName()}::register(\$this);");
		$logger->warning("    }");
		$logger->warning("}");
		$logger->warning("```");
		throw new InvalidArgumentException("Could not find shape viewer manager");
	}

	/**
	 * Returns the viewer for $player, creating one on first use.
	 */
	public static function viewer(Player $player) : ShapeViewer{
		return (self::$manager ?? self::managerNotFound())->get($player);
	}

	/**
	 * Draws $shape for every player. Returns one handle per player.
	 *
	 * @template TShape of Shape
	 * @param iterable<Player> $players
	 * @param TShape $shape
	 * @return list<DrawnShape<TShape>>
	 */
	public static function broadcast(iterable $players, Shape $shape) : array{
		$manager = self::$manager ?? self::managerNotFound();
		$drawn = [];
		foreach($players as $player){
			$drawn[] = $manager->get($player)->draw($shape);
		}
		return $drawn;
	}

	/**
	 * Removes every shape drawn for $player.
	 */
	public static function clear(Player $player) : void{
		(self::$manager ?? self::managerNotFound())->getNullable($player)?->clear();
	}
}
