<?php

declare(strict_types=1);

namespace XanderID\WhaleAlert\Commands;

use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginOwned;
use pocketmine\utils\TextFormat;

use XanderID\WhaleAlert\WhaleAlert;

class WhaleAlertCommands extends Command implements PluginOwned {

	/** @var WhaleAlert $plugin */
    private $plugin;

    public function __construct(WhaleAlert $plugin) {
        $this->plugin = $plugin;
        parent::__construct("whalealert", "Toggle for On/Off WhaleAlert", "/whalealert", []);
        $this->setPermission("whalealert.toggle");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args) : bool {
    	if(!$sender instanceof Player) return false;
    
        if($this->getOwningPlugin()->setOn($sender->getName())){
        	$sender->sendMessage("§aWhaleAlert successfully Enabled");
        } else {
        	$sender->sendMessage("§cWhaleAlert successfully Disabled");
        }
        return true;
    }

    public function getOwningPlugin(): WhaleAlert{
        return $this->plugin;
    }    
}
