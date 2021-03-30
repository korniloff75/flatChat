<?php
class Addons extends Chat
{
	const ADDONS_PATHNAME= \DR.'/Addons';

	protected $sts;

	public function __construct()
	{
		self::createDir(self::ADDONS_PATHNAME);
		$this->sts= new DbJSON(self::ADDONS_PATHNAME . '/sts.json');
	}




	public function includeItems()
	{

		foreach(new FilesystemIterator(self::ADDONS_PATHNAME) as $fi){
			if($fi->isFile()) return;


		}
	}
}