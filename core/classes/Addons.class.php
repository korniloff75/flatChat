<?php
class Addons extends Chat
{
	const ADDONS_PATHNAME= \DR.'/Addons';

	public function __construct()
	{
		self::createDir(self::ADDONS_PATHNAME);
	}




	public function listItems()
	{

	}
}