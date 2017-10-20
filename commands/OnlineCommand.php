<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class OnlineCommand extends UserCommand {
	protected $name = 'online';
	
	protected $names = [
		'pc' => 'MyPC'
	];
	protected $addr = [
		'pc' => '192.168.0.2'
	];
	
	public function execute() {
		$msg = $this->getMessage();
		$chat = $msg->getChat()->getId();
		$text = trim($this->getMessage()->getText(true));
		
		$reply = [];
		
		if($text) {
			if(!isset($this->names[$text])) {
				return Request::sendMessage([
					'chat_id' => $chat,
					'text' => 'Not found "'.$text.'"'
				]);
			}
			$keys = [$text];
		} else {
			$keys = array_keys($this->names);
		}
		
		foreach($keys as $key) {
			$reply[] = $this->names[$key].': '.(\Tools::isOnlineIp($this->addr[$key]) ? 'Online' : 'Offline');
		}
		
		return Request::sendMessage([
			'chat_id' => $chat,
			'text' => implode("\n",$reply)
		]);
	}
}
