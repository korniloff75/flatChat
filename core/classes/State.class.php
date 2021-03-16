<?php

class State /* extends Chat */
{
	const
		EXPIRES= 24*3600,
		BASE_PATHNAME= \DR.'/state.json';

	public $db;

	public function __construct(array $uState)
	{
		// *Последнее обращение к серверу
		$uState['ts']= time();
		$UID= $uState['UID'];
		// unset($uState['UID']);

		$this->db= new DbJSON(self::BASE_PATHNAME);

		$this->db->set(['users'=>[$UID=>$uState]]);
	}


	function __destruct(){
		// *Чистим старых пользователей
		$now= time();
		$change=0;
		foreach(($users= $this->db->get('users')) as $uid=>$user){
			if(
				!empty($uid)
				&& $now - $user['ts'] < self::EXPIRES && $user['name']
			) continue;

			unset($users[$uid]);
			$change=1;
		}

		if($change){
			$this->db->set(['users'=>array_filter($users)]);
		}
	}
}