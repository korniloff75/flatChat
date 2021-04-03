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

		// *Save orig state before mutations
		$orig_uState= $this->users[$UID];

		// $this->set(['users'=>[$UID=>$uState]]);
			// ->(['users'=>[$UID=>$uState]]);

		// *Restore main state fields
		$freezed = [
			'ban'=> $orig_uState['ban'] ?? false,
		];

		$uState = array_replace($uState, $freezed);

		$this->set(['users'=>[$UID=>$uState]]);

		tolog(__METHOD__,null,['$this->users'=>$this->users, '$freezed'=>$freezed, '$uState'=>$this->users[$UID]]);

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
				// && ($now - $user['ts']) < self::EXPIRES
			) {
				// tolog(__METHOD__,null,['exist $user'=>$user, '$uid'=>$uid]);
				continue;
			}

			tolog(__METHOD__,null,['removed $user'=>$user, '$uid'=>$uid]);

			unset($users[$uid]);
			$change=1;
		}

		if($change){
			$this->clear('users')
				->set(['users'=>array_filter($users)]);
		}

		// tolog(__METHOD__,null,['$this->users'=>$this->users]);

		parent::__destruct();
	}
}