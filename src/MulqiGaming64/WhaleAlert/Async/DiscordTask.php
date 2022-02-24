<?php

namespace MulqiGaming64\WhaleAlert\Async;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use MulqiGaming64\WhaleAlert\Async;

class DiscordTask extends AsyncTask {
	
	/** @var string $baseJson */
	private $baseJson;
	
	/** @var string $webhook*/
	private $webhook;
   
    /**
    * DiscordTask constructor.
    * @param string $baseJson
    * @param string $webhook
    */
   public function __construct($baseJson, $webhook){
       $this->baseJson = $baseJson;
       $this->webhook = $webhook;
   }
   
   public function onRun(): void{
   	 // Preparing Curl
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $this->webhook);
        curl_setopt($curl, CURLOPT_POSTFIELDS, base64_decode($this->baseJson));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);

		// Decode response from curl
        $responsejson = json_decode($response, true);
		
		// for undefined Error
        if(curl_error($curl) !== ""){
            $this->setResult(["error" => "Unknow error!"], true);
            return;
        }

        if(curl_getinfo($curl, CURLINFO_HTTP_CODE) !== 204){ // Check for Webhook Callback Error
        	if(isset($responsejson['message'])){
            	$this->setResult(["error" => $responsejson['message']], true);
         	   return;
         	}
         	$this->setResult(["error" => "Unknow error!"], true);
         	return;
        }
        $this->setResult(["success" => true], true);
   }
   
   public function onCompletion(): void{
   	// Only for Error Log
   	$data = $this->getResult();
   	if(isset($data["error"])){
			$plugin = Server::getInstance()->getPluginManager()->getPlugin("WhaleAlert");
      	  if($plugin === null){
           	 return;
      	  }
      	 $plugin->discord = false;
           $plugin->getLogger()->warning($data["error"] . ", In Discord! Disabling Discord");
           return;
      }
   }
}