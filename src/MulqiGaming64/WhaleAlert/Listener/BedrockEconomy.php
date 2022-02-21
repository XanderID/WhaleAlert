<?php

declare(strict_types=1);

namespace MulqiGaming64\WhaleAlert\Listener;

use MulqiGaming64\WhaleAlert\WhaleAlert;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use cooldogedev\BedrockEconomy\event\TransactionSubmitEvent;
use cooldogedev\BedrockEconomy\transaction\Transaction;
use pocketmine\scheduler\ClosureTask;

class BedrockEconomy implements Listener {
	
	/** @var WhaleAlert $plugin */
	private $plugin;
	
	/** @var array $onTransaction */
	private $onTransaction = [];
	
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
    public function onPay(TransactionSubmitEvent $event){
    	if($event->isCancelled()) return;
   	 if(!$this->plugin->isOn($event->getAccount())) return; // for toggle Payer on / off WhaleAlert
    	
    	// why do I use a very convoluted method? because BedrockEconomy doesn't have Pay Event
    	$transaction = $event->getTransaction();
    
    	// Why i add Closure Task? to clear arrays that are no longer used
    	$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(
        	function() use($transaction){
        		$date = $transaction->getIssueDate();
            	if(isset($this->onTransaction[$date])){
            		// unset to reduce array data and avoid double Broadcast
            		unset($this->onTransaction[$date]);
            	}
            }
        ), 20 * 5);
        
    	if($transaction->getType() == Transaction::TRANSACTION_TYPE_DECREMENT){
    		$this->onTransaction[$transaction->getIssueDate()][0] = [
				"amount" => $transaction->getValue(), 
				"sender" => $event->getAccount()
			];
		}
		if($transaction->getType() == Transaction::TRANSACTION_TYPE_INCREMENT){
    		$this->onTransaction[$transaction->getIssueDate()][1] = [
				"amount" => $transaction->getValue(),
				"target" => $event->getAccount()
			];
			// why add sameTransaction here? because Pay's last transaction at BedrockEconomy was Adding Target
			$this->sameTransaction($transaction->getIssueDate());
		}
    }
    
    public function sameTransaction(int $date){
    	if(count($this->onTransaction[$date]) == 2){ // for pay transaction only
			$transaction = $this->onTransaction[$date];
			if($transaction[0]["amount"] == $transaction[1]["amount"]){ // For check if same transaction
				// Variable
				$amount = $transaction[0]["amount"];
				$sender = $transaction[0]["sender"];
				$target = $transaction[1]["target"];
				if($amount >= $this->plugin->getMinimum()){
    				$this->plugin->broadcastMessage($sender, $target, (int) $amount);
   		 	}
			}
    	}
    }
}
