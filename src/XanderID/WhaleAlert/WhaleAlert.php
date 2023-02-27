<?php

declare(strict_types=1);

namespace XanderID\WhaleAlert;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use XanderID\WhaleAlert\Listener\EconomyAPI;
use XanderID\WhaleAlert\Listener\BedrockEconomy;

use XanderID\WhaleAlert\Async\DiscordTask;
use XanderID\WhaleAlert\Commands\WhaleAlertCommands;

class WhaleAlert extends PluginBase {
	
	public const availableEconomy = ["BedrockEconomy", "EconomyAPI"];
	
	/** @var string $ecoType */
	public $ecoType = "";
	
	/** @var array $toggle */
	public $toggle = [];
	
	/** @var bool $discord */
	public $discord = false;

    public function onEnable(): void {
        $this->saveDefaultConfig();
        
        $economy = $this->getEconomyType();
        $this->ecoType = $economy;
        if($economy == "BedrockEconomy"){
      	  $this->getServer()->getPluginManager()->registerEvents(new BedrockEconomy($this), $this);
        } elseif($economy == "EconomyAPI"){
        	$this->getServer()->getPluginManager()->registerEvents(new EconomyAPI($this), $this);
		}
		
		$this->checkDiscord();
		$this->getServer()->getCommandMap()->register("WhaleAlert", new WhaleAlertCommands($this));
    }
    
    public function checkDiscord(): bool{
    	$config = $this->getConfig()->getAll()["discord"]; // Get only Discord Config
    	if($config["enable"]){
    		// Check for Valid Discord Webhook Urls
			$validurl = ["https://discordapp.com/api/webhooks/", "https://discord.com/api/webhooks/"];
			// Url prefix in Config
			$url = substr($config["webhook"], 0, strlen($validurl[0]));
			$urls = substr($config["webhook"], 0, strlen($validurl[1]));
			if($url !== $validurl[0] && $urls !== $validurl[1]){
				$this->getLogger()->warning("Discord webhook url not valid! Disabling Discord");
				return false;
			}
			$this->discord = true;
    	}
    	return true;
    }
    
    public function setDiscord(bool $discord = false): void{
    	$this->discord = $discord;
    }
    
    /** @return null|string */
    public function getEconomyType(){
    	$economys = strtolower($this->getConfig()->get("economy"));
    	$economy = null;
    	$plugin = $this->getServer()->getPluginManager();
    
    	switch($economys){
    		case "bedrockeconomy":
    			if($plugin->getPlugin("BedrockEconomy") == null){
    				$plugin->disablePlugin($this);
    				$this->getLogger()->alert("Your Economy's plugin: BedrockEconomy, Not found Disabling Plugin!");
    				return null;
    			}
    			$economy = "BedrockEconomy";
    		break;
    		case "economyapi":
    			if($plugin->getPlugin("EconomyAPI") == null){
    				$plugin->disablePlugin($this);
    				$this->getLogger()->alert("Your Economy's plugin: EconomyAPI, Not found Disabling Plugin!");
    				return null;
    			}
    			$economy = "EconomyAPI";
    		break;
    		case "auto":
    			$found = false;
    			foreach(self::availableEconomy as $eco){
    				if($plugin->getPlugin($eco) !== null){
    					$economy = $eco;
    					$found = true;
    					break;
    				}
    			}
    			if(!$found){
    				$plugin->disablePlugin($this);
    				$this->getLogger()->alert("all economy plugins could not be found, Disabling Plugin!");
    				return null;
    			}
    		break;
    		default:
    			$this->getLogger()->info("No economy plugin Selected, Detecting");
    			$found = false;
    			foreach(self::availableEconomy as $eco){
    				if($plugin->getPlugin($eco) !== null){
    					$economy = $eco;
    					$found = true;
    					break;
    				}
    			}
    			if(!$found){
    				$plugin->disablePlugin($this);
    				$this->getLogger()->alert("all economy plugins could not be found, Disabling Plugin!");
    				return null;
    			}
    		break;
    	}
    	return $economy;
    }
    
    public function isOn(string $name): bool{
    	if(isset($this->toggle[$name])){
    		return $this->toggle[$name];
    	}
    	return false;
    }
    
    public function setOn(string $name): bool{
    	if($this->isOn($name)){
    		$this->toggle[$name] = false;
    		return false;
    	}
    	$this->toggle[$name] = true;
    	return true;
    }
   
    public function getMinimum(): int{
    	return $this->getConfig()->get("minimum");
    }
    
    public function broadcastMessage(string $name, string $target, int|float $amount): void{
    	$text = str_replace(
    		["{name}", "{target}", "{amount}", "{economy}", "{line}"],
    		[$name, $target, number_format($amount), $this->ecoType, "\n"],
    		$this->getConfig()->get("message")
    	);
    	$this->getServer()->broadcastMessage($text);
    	if($this->discord){
    		$this->sendToDiscord($this->removeColor($text));
    	}
    }
    
    public function removeColor(string $text): string{
    	$text = preg_replace("/§[a-z]/", "", $text);
    	$text = preg_replace("/§[0-9]/", "", $text);
    	return $text;
    }
    
    private function sendToDiscord(string $text): bool{
    	$config = $this->getConfig()->getAll()["discord"]; // Get only Discord Config
		// Preparing Webhook
    	$discord = ["username" => $config["name"]];
    
    	// Preparing Message Webhook
    	if($config["embeds"]["enable"]){
    		$color = hexdec($config["embeds"]["color"]); // Color for Embeds
    		$discord["content"] = "";
    		$discord["embeds"][] = [
				"title" => $config["embeds"]["title"],
    			"description" => $text,
    			"color" => $color,
    			"footer" => [
    				"text" => $config["embeds"]["footer"]
				]
			];
		} else {
			$discord["content"] = $text;
		}
		 
		// Send to Discord
		$baseJson = base64_encode(json_encode($discord));
		$this->getServer()->getAsyncPool()->submitTask(new DiscordTask($baseJson, $config["webhook"]));
		return true;
    }
}
