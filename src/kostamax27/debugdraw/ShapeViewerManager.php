<?php

declare(strict_types=1);

namespace kostamax27\debugdraw;

use InvalidArgumentException;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;

final class ShapeViewerManager{
	/** @var array<int, ShapeViewer> */
	private array $viewers = [];

	public function __construct(
		readonly private Plugin $plugin
	){
		Server::getInstance()->getPluginManager()->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event) : void{
			unset($this->viewers[$event->getPlayer()->getId()]);
		}, EventPriority::MONITOR, $plugin);
	}

	public function get(Player $player) : ShapeViewer{
		$player->isConnected() || throw new InvalidArgumentException("Player {$player->getName()} is not connected");
		return $this->viewers[$player->getId()] ??= new ShapeViewer($player, $this->plugin->getScheduler());
	}

	public function getNullable(Player $player) : ?ShapeViewer{
		return $this->viewers[$player->getId()] ?? null;
	}
}
