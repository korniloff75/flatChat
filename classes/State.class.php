<?php

class State extends Chat
{
	const EXPIRES= 24*3600;

	static $db;

	public function __construct(array $data)
	{
		// *Последнее обращение к серверу
		$data['ts']= time();
		$UID= $data['UID'];
		unset($data['UID']);

		self::$db= new DbJSON(\DR.'/state.json');

		self::$db->set(['users'=>[$UID=>$data]]);
	}


	// ?
	private function _filterUsers($u){
		return $now - $user['ts'] < self::EXPIRES && $user['name'];
	}


	function __destruct(){
		$now= time();
		$change=0;
		foreach(($users= self::$db->get('users')) as $uid=>$user){
			if($now - $user['ts'] < self::EXPIRES && $user['name']) continue;

			unset($users[$uid]);
			$change=1;
		}

		if($change){
			self::$db->set(['users'=>array_filter($users)]);
		}
	}
}