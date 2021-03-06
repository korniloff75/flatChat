<?php

class State extends Chat
{
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


	function __destruct(){
		$now= time();
		$change=0;
		foreach(($users= self::$db->get('users')) as $uid=>$user){
			if($now - $user['ts'] < 24*3600 && $user['name']) continue;

			unset($users[$uid]);
			$change=1;
		}

		if($change){
			self::$db->replace(['users'=>array_filter($users)]);
		}
	}
}