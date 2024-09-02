<?php

declare(strict_types=1);

namespace GamerZone7s\ChatColorizer;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener {

    private Config $playerColors;

    public function onEnable(): void {
        $this->getLogger()->info("ChatColorizer plugin enabled!");
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->playerColors = new Config($this->getDataFolder() . "playercolors.yml", Config::YAML, []);
    }

    public function onChat(PlayerChatEvent $event): void {
        $player = $event->getPlayer();
        $message = $event->getMessage();
        $playerName = $player->getName();

        // Check if player has a specific color set in the config
        if ($this->getConfig()->get("player_defaults")[$playerName] ?? false) {
            $colorCode = $this->getConfig()->get("player_defaults")[$playerName];
            $message = $colorCode . $message;
        } else {
            if ($this->playerColors->exists($playerName)) {
                $colorCode = $this->playerColors->get($playerName);
                $message = $colorCode . $message;
            } else {
                $colorCode = $this->getConfig()->get("default_color");
                $message = $colorCode . $message;
            }
        }

        $event->setMessage(TextFormat::colorize($message));
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "setcolor") {
            if (count($args) < 2) {
                $sender->sendMessage("Usage: /setcolor <playername> <colorcode>");
                return false;
            }

            $playerName = $args[0];
            $colorCode = $args[1];

            // Save color to player-specific config
            $this->playerColors->set($playerName, $colorCode);
            $this->playerColors->save();

            // Save color to main config as well
            $this->getConfig()->set("player_defaults.$playerName", $colorCode);
            $this->saveConfig();

            // Feedback
            $sender->sendMessage("Set chat color for $playerName to " . TextFormat::colorize($colorCode) . "$colorCode.");
            return true;
        }
        return false;
    }
}