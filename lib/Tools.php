<?php

class Tools {
	public static function isOnline($wait = 1) {
		return self::isOnlineIp('8.8.8.8', $wait);
	}
	public static function isOnlineIp($ip, $wait = 1) {
		if(!($wait = (int)$wait)) $wait = 1;
		exec('ping -c 1 -w '.$wait.' '.$ip, $o, $r);
		return !$r;
	}
}
