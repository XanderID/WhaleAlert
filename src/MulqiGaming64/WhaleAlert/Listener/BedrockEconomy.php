<?php

declare(strict_types=1);

namespace MulqiGaming64\WhaleAlert\Listener;

use MulqiGaming64\WhaleAlert\WhaleAlert;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use cooldogedev\BedrockEconomy\event\transaction\TransactionSubmitEvent;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use cooldogedev\BedrockEconomy\transaction\types\TransferTransaction;

class BedrockEconomy implements Listener {
	
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
    public function onTransaction(TransactionSubmitEvent $event){
    	if($event->isCancelled()) return;
    
    	$transaction = $event->getTransaction();
    	if($transaction instanceof TransferTransaction){ // Check for Only Pay Transaction
   		 if(!$this->plugin->isOn($transaction->getSender())) return; // for toggle Payer on / off WhaleAlert
    		if($transaction->getAmount() >= $this->plugin->getMinimum()){
    			$this->plugin->broadcastMessage($transaction->getSender(), $transaction->getReceiver(), (int) $transaction->getAmount());
    		}
    	}
    }
}
