<?php
set_error_handler(function($errno, $errstr, $errfile, $errline) {
	file_put_contents(__DIR__.'/php-error.txt', print_r([
		'errno'=>$errno,
		'errstr'=>$errstr,
		'errfile'=>$errfile,
		'errline'=>$errline
	], true));
}, E_ALL);

require_once __DIR__.'/vendor/autoload.php';
use Longman\TelegramBot\Telegram;

$config = require(__DIR__.'/config.php');

try {
	$telegram = new Telegram($config['token'], $config['username']);
	
	if(isset($_GET['set']) || isset($_GET['del'])) {
		if(true) { // Protect this
			try {
				if(isset($_GET['set'])) {
					$url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
					$r = $telegram->setWebhook($url);
				}
				else if(isset($_GET['del'])) {
					$r = $telegram->deleteWebhook();
				}
				print "OK: ".$r->getDescription();
			} catch(Exception $e) {
				print $e->getMessage();
			}
		}
		exit;
	}
	
	$telegram->addCommandsPath(__DIR__.'/commands/');
	if(!empty($config['db'])) {
		$telegram->enableMySql($config['db']);
	}
	
	//Longman\TelegramBot\TelegramLog::initErrorLog(__DIR__.'/error.log');
	//Longman\TelegramBot\TelegramLog::initDebugLog(__DIR__.'/debug.log');
	//Longman\TelegramBot\TelegramLog::initUpdateLog(__DIR__.'/update.log');
	
	$telegram->handle();
} catch(Exception $e) {
	file_put_contents(__DIR__.'/exception.txt', $e->getMessage());
}
