<?php

namespace Longman\TelegramBot\Commands\UserCommands;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\InlineKeyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Conversation;

class SelectCommand extends UserCommand {
	protected $name = 'select';
	
	public function execute() {
		$inline_keyboard = new InlineKeyboard(
			[
				['text' => 'Option A', 'callback_data' => 'op:A'],
				['text' => 'Option B', 'callback_data' => 'op:B'],
				['text' => 'Option C', 'callback_data' => 'op:C']
			],
			[
				['text' => 'Option D', 'callback_data' => 'op:D'],
				['text' => 'Option E', 'callback_data' => 'op:E']
			],
			[
				['text' => 'Submit', 'callback_data' => 'submit'],
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
				'text' => 'Select options',
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
		
		if(empty($conv->notes['selected'])) $conv->notes['selected'] = [];
		
		if(preg_match('/^([^:]+)(?:\:(.+))?/',$text,$match)) {
			if($match[1] == 'submit') {
				$options = array_keys($conv->notes['selected']);
				
				$conv->stop();
				
				return Request::editMessageText([
					'chat_id' => $chat,
					'message_id' => $query->getMessage()->getMessageId(),
					'text' => 'Selected: '.implode(', ',$options)
				]);
			}
			
			if($match[1] == 'op') {
				if(isset($conv->notes['selected'][$match[2]])) {
					unset($conv->notes['selected'][$match[2]]);
				} else {
					$conv->notes['selected'][$match[2]] = true;
				}
				
				foreach($inline_keyboard->getInlineKeyboard() as $row) {
					foreach($row as $button) {
						if(strpos($button->getCallbackData(), 'op:') !== 0) continue;
						if(isset($conv->notes['selected'][substr($button->getCallbackData(),3)])) {
							$button->setText('🗸'.$button->getText());
						}
					}
				}
			}
			else if($match[1] == 'clear') {
				$conv->notes['selected'] = [];
			}
			
			$conv->update();
			return Request::editMessageReplyMarkup([
				'chat_id' => $chat,
				'message_id' => $query->getMessage()->getMessageId(),
				'reply_markup' => $inline_keyboard
			]);
		}
		
		return Request::emptyResponse();
	}
}
