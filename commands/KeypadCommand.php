<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;

class KeypadCommand extends UserCommand {
	protected $name = 'keypad';
	
	public function execute() {
		$inline_keyboard = new InlineKeyboard(
			[
				['text' => 'A', 'callback_data' => 'add:A'],
				['text' => 'B', 'callback_data' => 'add:B'],
				['text' => 'C', 'callback_data' => 'add:C'],
				['text' => 'D', 'callback_data' => 'add:D'],
				['text' => 'E', 'callback_data' => 'add:E']
			],
			[
				['text' => 'Submit', 'callback_data' => 'submit'],
				['text' => 'Back', 'callback_data' => 'back'],
				['text' => 'Clear', 'callback_data' => 'clear']
			]
		);
		
		if(!$this->getCallbackQuery()) {
			$user = $this->getMessage()->getFrom()->getId();
			$chat = $this->getMessage()->getChat()->getId();
			
			$conv = new Conversation($user, $chat);
			if($conv->exists()) $conv->stop();
			$conv = new Conversation($user, $chat, $this->getName());
			
			$res = Request::sendMessage([
				'chat_id' => $chat,
				'text' => 'Text: ',
				'reply_markup' => $inline_keyboard
			]);
			
			if($res->getResult() instanceOf Message) {
				$conv->notes['message_id'] = $res->getResult()->getMessageId();
			}
			$conv->update();
			
			return $res;
		}
		
		$query = $this->getCallbackQuery();
		$user = $query->getFrom()->getId();
		$chat = $query->getMessage()->getChat()->getId();
		$text = $query->getData();
		
		$conv = new Conversation($user, $chat);
		if(!$conv->exists() || $conv->notes['message_id']!=$query->getMessage()->getMessageId()) {
			return Request::emptyResponse();
		}
		
		if(empty($conv->notes['text'])) $conv->notes['text'] = '';
		
		if(preg_match('/^([^:]+)(?:\:(.+))?/',$text,$match)) {
			if($match[1] == 'submit') {
				$text = $conv->notes['text'];
				
				$conv->stop();
				
				return Request::editMessageText([
					'chat_id' => $chat,
					'message_id' => $query->getMessage()->getMessageId(),
					'text' => 'Entered: '.$text
				]);
			}
			
			if($match[1] == 'add') {
				$conv->notes['text'] .= $match[2];
			}
			else if($match[1] == 'back') {
				$conv->notes['text'] = mb_substr($conv->notes['text'],0,-1);
			}
			else if($match[1] == 'clear') {
				$conv->notes['text'] = '';
			}
			
			$conv->update();
			return Request::editMessageText([
				'chat_id' => $chat,
				'message_id' => $query->getMessage()->getMessageId(),
				'text' => 'Text: '.$conv->notes['text'],
				'reply_markup' => $inline_keyboard
			]);
		}
		
		return Request::emptyResponse();
	}
}
