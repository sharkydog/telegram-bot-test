<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Request;

class PingCommand extends UserCommand {
	protected $name = 'ping';
	
	public function execute() {
		return Request::sendMessage([
			'chat_id' => $this->getMessage()->getChat()->getId(),
			'text' => 'pong'
		]);
	}
}
