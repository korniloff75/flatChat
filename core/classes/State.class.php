<?php

class State extends DbJSON
{
	const
		EXPIRES= 24*3600,
		// EXPIRES= 1*3600,
		BASE_PATHNAME= \DR.'/state.json';

	public $test = 1;

	/*
	function __set($n,$v)
	{
		return $this->db->set([$n=>$v]);
	}
	*/

	public function __construct(array $uState)
	{
		// *Последнее обращение к серверу
		// $uState['ts']= time();

		$UID= $uState['UID'];
		// unset($uState['UID']);


		parent::__construct(self::BASE_PATHNAME);

		$this->set(['users'=>[$UID=>$uState]]);
			// ->(['users'=>[$UID=>$uState]]);

		tolog(__METHOD__,null,[$this->users]);

		if(!isset($this->startIndex)) $this->set(['startIndex'=>0]);
	}


	function __destruct()
	{
		// tolog(__METHOD__,null,[$this->users]);
		// *Чистим старых пользователей
		$now= time();
		$change=0;
		foreach(($users= $this->get('users')) as $uid=>$user){
			if(
				!empty($uid)
				&& !empty($user['name'])
				&& ($now - $user['ts']) < self::EXPIRES
				// && $user['name']
			) continue;

			unset($users[$uid]);
			$change=1;
		}

		if($change){
			$this->clear('users')
				->set(['users'=>array_filter($users)]);
		}

		tolog(__METHOD__,null,[$this->users]);

		// *check changes
		if(
			!$this->changed
		) return;

		// $this->save();

		parent::__destruct();
	}
}