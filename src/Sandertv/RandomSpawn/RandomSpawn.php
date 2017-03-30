<?php

namespace Sandertv\RandomSpawn;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\Position;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class RandomSpawn extends PluginBase implements Listener {
	
	public $locations;
	
	public function onEnable() {
		if(!is_dir($this->getDataFolder())) {
			mkdir($this->getDataFolder());
		}
		if(!is_file($this->getDataFolder() . "spawnLocations.yml")) {
			yaml_emit_file($this->getDataFolder() . "spawnLocations.yml", ["Spawns" => []]);
		}
		$this->locations = yaml_parse_file($this->getDataFolder() . "spawnLocations.yml");
	}
	
	public function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		if(empty($this->locations["Spawns"])) {
			return false;
		}
		$randomSpawn = $this->locations["Spawns"][array_rand($this->locations)];
		$randomPosition = new Position($randomSpawn["x"], $randomSpawn["y"], $randomSpawn["z"], $this->getServer()->getLevelByName($randomSpawn["level"]));
		$player->teleport($randomPosition);
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
		if(strtolower($command->getName()) === "randomspawnposition") {
			if(!$sender->hasPermission("randomspawn.setposition")) {
				$sender->sendMessage(TextFormat::RED . "You don't have permission to do that.");
				return true;
			}
			if(!isset($args[0]) || !is_numeric($args[0])) {
				$sender->sendMessage(TextFormat::RED . "You should provide an ID for your spawn.");
				return true;
			}
			if(isset($this->locations["Spawns"][$args[0]])) {
				$sender->sendMessage(TextFormat::GREEN . "Successfully modified the spawn with ID " . $args[0] . ".");
				$this->locations["Spawns"][$args[0]] = [
					"x" => $sender->x,
					"y" => $sender->y,
					"z" => $sender->z,
					"level" => $sender->getLevel()->getName()
				];
				return true;
			}
			$sender->sendMessage(TextFormat::GREEN . "Successfully added the spawn with ID " . $args[0] . ".");
			$this->locations["Spawns"][$args[0]] = [
				"x" => $sender->x,
				"y" => $sender->y,
				"z" => $sender->z,
				"level" => $sender->getLevel()->getName()
			];
		}
		return true;
	}
	
	public function onDisable() {
		yaml_emit_file($this->getDataFolder() . "spawnLocations.yml", $this->locations);
	}
}