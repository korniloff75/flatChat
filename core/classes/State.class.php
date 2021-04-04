<?php

class State extends DbJSON
{
	const
		EXPIRES= 24*3600,
		// EXPIRES= 1*3600,
		BASE_PATHNAME= \DR.'/state.json';

	public $test = \Chat::DEV;


	public function __construct(array $uState)
	{
		$UID= $uState['UID'];
		// unset($uState['UID']);

		parent::__construct(self::BASE_PATHNAME);

		/* // *Save orig state before mutations
		$orig_uState= $this->users[$UID];

		// *Restore main state fields ?
		$freezed = [
			'ban'=> $orig_uState['ban'] ?? false,
		];

		$uState = array_replace($uState, $freezed);

		$this->set(['users'=>[$UID=>$uState]]); */

		// tolog(__METHOD__,null,['$this->users'=>$this->users, '$freezed'=>$freezed, '$uState'=>$this->users[$UID]]);

		if(!isset($this->startIndex)) $this->set(['startIndex'=>0]);
	}


	// *Обновление в том же экземпляре
	function update(array $uState)
	{
		$this->__construct($uState);
		return $this;
	}


	function __destruct()
	{
		// tolog(__METHOD__,null,[$this->users]);
		// *Чистим ошибочные записи
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