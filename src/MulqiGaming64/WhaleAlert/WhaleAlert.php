<?php

declare(strict_types=1);

namespace MulqiGaming64\WhaleAlert;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;

use MulqiGaming64\WhaleAlert\Listener\EconomyAPI;
use MulqiGaming64\WhaleAlert\Listener\BedrockEconomy;

use MulqiGaming64\WhaleAlert\Commands\WhaleAlertCommands;

class WhaleAlert extends PluginBase {
	
	public const availableEconomy = ["BedrockEconomy", "EconomyAPI"];
	
	/** @var string $ecoType */
	public $ecoType = "";
	
	/** @var array $toggle */
	public $toggle = [];

    public function onEnable(): void {
        $this->saveDefaultConfig();
        
        $economy = $this->getEconomyType();
        $this->ecoType = $economy;
        if($economy == "BedrockEconomy"){
      	  $this->getServer()->getPluginManager()->registerEvents(new BedrockEconomy($this), $this);
        } elseif($economy == "EconomyAPI"){
        	$this->getServer()->getPluginManager()->registerEvents(new EconomyAPI($this), $this);
		}
		
		$this->getServer()->getCommandMap()->register("WhaleAlert", new WhaleAlertCommands($this));
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
    }
}
