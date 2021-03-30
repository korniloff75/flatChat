<?php

class State /* extends Chat */
{
	const
		EXPIRES= 24*3600,
		// EXPIRES= 1*3600,
		BASE_PATHNAME= \DR.'/state.json';

	private $db;

	function __get($n)
	{
		if(method_exists($this->db, $n)){
			return $this->db->$n;
		}
		return $this->db->get($n);
	}

	function __set($n,$v)
	{
		return $this->db->set([$n=>$v]);
	}

	function get(?string $n=null)
	{
		return $this->__get($n);
	}

	public function __construct(array $uState)
	{
		// *Последнее обращение к серверу
		// $uState['ts']= time();

		$UID= $uState['UID'];
		// unset($uState['UID']);


		$this->db= new DbJSON(self::BASE_PATHNAME);

		$this->db->set(['users'=>[$UID=>$uState]]);
			// ->(['users'=>[$UID=>$uState]]);

		// *reset onlines
		/* foreach($this->db->users as $uid=>$uState){
			$this->db->set(['users'=>[$uid=>['on'=>false]]]);
			// $this->db->users[$uid]['on'] = false;
		} */

		tolog(__METHOD__,null,[$this->db->users]);

		if(!isset($this->db->startIndex)) $this->db->set(['startIndex'=>0]);
	}


	function save(){
		return $this->db->save();
	}

	function remove($ind){
		return $this->db->remove($ind);
	}


	function __destruct()
	{
		// *Чистим старых пользователей
		$now= time();
		$change=0;
		foreach(($users= $this->db->get('users')) as $uid=>$user){
			if(
				!empty($uid)
				&& ($now - $user['ts']) < self::EXPIRES
				// && $user['name']
			) continue;

			unset($users[$uid]);
			$change=1;
		}

		if($change){
			$this->db->clear('users')
				->set(['users'=>array_filter($users)]);
		}
	}
}