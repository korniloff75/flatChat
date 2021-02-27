<?php

class State extends Chat
{
	static $db;

	public function __construct(array $data)
	{
		self::$db= new DbJSON('./state.json');

		self::$db->set([]);
	}
}