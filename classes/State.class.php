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
}