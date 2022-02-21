<?php

declare(strict_types=1);

namespace MulqiGaming64\WhaleAlert\Listener;

use MulqiGaming64\WhaleAlert\WhaleAlert;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use onebone\economyapi\event\money\PayMoneyEvent;

class EconomyAPI implements Listener {
	
	/** @var WhaleAlert $plugin */
	private $plugin;
	
	public function __construct(WhaleAlert $plugin) {
        $this->plugin = $plugin;
    }
    
    public function onJoin(PlayerJoinEvent $event){
    	$name = $event->getPlayer()->getName();
    	if(!isset($this->plugin->toggle[$name])){
    		$this->plugin->setOn($name);
    	}
    }
	
    /** @priority LOWEST */
    public function onPay(PayMoneyEvent $event){
    	if($event->isCancelled()) return;
    	if(!$this->plugin->isOn($event->getPayer())) return; // for toggle Payer on / off WhaleAlert
    
    	if($event->getAmount() >= $this->plugin->getMinimum()){
    		$this->plugin->broadcastMessage($event->getPayer(), $event->getTarget(), (int) $event->getAmount());
    	}
    }
}
