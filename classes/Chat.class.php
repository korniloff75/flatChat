<?php
require_once __DIR__ . "/../Helpers.trait.php";

class Chat
{
	use Helpers;

	const
		DBPATHNAME= \DBFILE,
		ARH_PATHNAME= \DR.'/db',
		DELIM= "<~>",
		MAX_LINES= 200,
		DELTA_LINES= 100;

	static
		$log;

	public
		$dbPathname,
		$file,
		$lastMod,
		$useStartIndex= true,
		$out=[];

	private
		$exit= false;

	protected
		$data=[];

	public function __construct($dbPathname=null)
	{
		$this->dbPathname= $dbPathname ?? self::DBPATHNAME;

		$this->_controller();

		tolog(__METHOD__,null,['data'=>$this->data]);

		// if(!$this->mode || $this->name){
			new State($this->data);
		// }

		if ( ($this->lastMod = filemtime( $this->dbPathname )) === false ) $this->lastMod = 0;

		if($this->mode === 'post')
			$this->_newPost();
		// *Update list
		if ( $this->mode == "list" ) {
			$this->exit = true;

			// $rlm = preg_match( "~^\\d+$~u", @$_POST["lastMod"] ) ? (int)$_POST["lastMod"] : 0;
			$rlm = (int)filter_var($_POST["lastMod"], FILTER_SANITIZE_NUMBER_INT) ?? 0;

			if ( $rlm === $this->lastMod ) echo $this->Out( "NONMODIFIED" );
			else echo $this->Out( "OK", true );
		}

		if ( $this->exit ) exit( 0 );

	}//__construct


	function __get($n)
	{
		return $this->$n ?? $this->data[$n];
	}

	public function getJsonData()
	:string
	{
		return json_encode($this->data,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	}


	protected function _defineUID($name, $IP)
	{
		return $name . substr($IP, 0, strrpos($this->IP, '.')+1);
	}


	private function _controller()
	{
		// if($cookieName = (@$_COOKIE["userName"] ?? null))
		// 	$cookieName = self::cleanName( $cookieName );

		if($chatUser = json_decode(@$_COOKIE["chatUser"] ?? null, 1)){
			$this->data= array_merge($this->data, $chatUser);
		}
		else{
			$this->data['IP']= self::realIP();
		}

		$this->data['name'] = $this->name? $this->name: self::cleanName(@$_REQUEST["name"]) ?? null;
		$this->data['UID']= $this->_defineUID($this->name, $this->IP);

		tolog(__METHOD__,null,['$chatUser'=>$chatUser]);

		$this->data['ts'] = filter_var(@$_POST["ts"]);
		// if(!$this->name) $this->data['name']= $cookieName;
		$this->data['text'] = self::cleanText(@$_POST["text"] ?? null);


		$cook= json_encode([
			'name'=> $this->name,
			'IP'=> $this->IP,
			'UID'=> $this->UID,
		], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

		switch( @$_POST["mode"] ) {
			case "post":
				$mode = "post";
			break;

			case "list":
				$mode = "list";
			break;
		}

		$this->data['mode'] = $mode ?? null;

		if ( $cook !== @$_COOKIE["chatUser"] ) setcookie( "chatUser", $cook, mktime( 0, 0, 0, 12, 31, 3000 ), \COOKIEPATH, '', false, true );
	}


	private function _newPost()
	{
		if ( !$this->name || !$this->text ) {
			header( 'HTTP/1.1 400 Bad Request' );
			exit( 0 );
		}

		$this->data['myUID']= $this->_defineUID($this->name, $this->IP);

		$this->exit = true;

		// *Uploads
		Uploads::$allow = ['jpg','jpeg','png','gif'];
		Uploads::$pathname = \DR.'/files_B';
		$upload = new Uploads(null, 'attach');

		tolog('Uploads',null,['$upload'=>$upload]);

		// *Write
		// file_put_contents( $this->dbPathname, $this->Template(), LOCK_EX|FILE_APPEND );
		$this->_save($upload->loaded);

		$this->mode = "list";

		$this->lastMod = filemtime( $this->dbPathname );
	}


	private function _save($files= [])
	{
		array_walk($files, function(&$f){
			$f= self::getPathFromRoot($f);
		});

		$data= [$this->IP,$this->ts,$this->name,$this->text,json_encode($files,  JSON_UNESCAPED_SLASHES)];

		// *Write
		file_put_contents( $this->dbPathname, implode(self::DELIM, $data) . PHP_EOL, LOCK_EX|FILE_APPEND );
	}


	public function getHTML()
	{
		$this->Out(null, true);
		return $this->out['html'];
	}



	/**
	 * *Разбиваем базу, добавляя лишнее в архив self::ARH_PATHNAME/*(ts)
	 */
	private function _checkDB()
	{
		$file= &$this->file;
		$count= count($file= file($this->dbPathname, FILE_SKIP_EMPTY_LINES));

		if(!is_dir(self::ARH_PATHNAME)){
			mkdir(self::ARH_PATHNAME);
		}

		if(
			!file_exists($this->dbPathname)
			// *Archives
			|| !$this->useStartIndex
			|| ($count - self::MAX_LINES - self::DELTA_LINES < 0)
		){
			tolog(__METHOD__ . ": База имеет допустимый размер - $count строк.",null,['$count'=>$count]);
		}
		// *Файл превышает допустимый размер
		else{
			$newFile= array_splice($file, -self::MAX_LINES);

			if(
				// *Обрезаем базу
				file_put_contents( $this->dbPathname, $newFile, LOCK_EX )
				// *Добавляем в архив
				&& file_put_contents( self::ARH_PATHNAME.'/'.time(), $file, LOCK_EX )
			){
				State::$db->set(['startIndex'=>State::$db->get('startIndex') + ($count - self::MAX_LINES)]);
			}

		}

		return $this;
	}


	public function Out( $status = null, $is_modified = false )
	:string
	{
		$out= &$this->out;
		$out['html']= ( $status !== null ) ? "{$status}:{$this->lastMod}\n": '';

		if ( $is_modified ) {
			$out['html'].= $this->_parse();
		} //$is_modified

		// tolog(__METHOD__,null,['$chat2'=>$chat]);

		tolog(__METHOD__,null,['$out'=>$out]);
		// var_dump($out);

		$out['state']= State::$db->get();
		// $out['Chat']= $this->getData();
		$out['Chat']= $this->data;

		return json_encode($out, JSON_UNESCAPED_UNICODE);
	}


	private function _parse()
	:string
	{
		ob_start();

		if($chat= $this->_checkDB()->file){
			array_walk($chat, function(&$v,$n){
				// *Разбираем построчно
				$v= explode(self::DELIM, $v);
				// *ts -> Date
				$v[1]= date('Y-m-d H:i', $v[1]);
				// $v= $this->_renderPost($n,$v);
				echo $this->_renderPost(++$n,$v);
			});
		}

		// tolog(__METHOD__,null,['$chat'=>$chat,]);

		return ob_get_clean();
	}


	private function _renderPost($n,&$i)
	{
		// *Последовательность данных
		list($IP,$ts,$name,$text,$files)= $i;
		$UID= $this->_defineUID($name, $IP);
		if($this->useStartIndex){
			$n+= State::$db->get('startIndex');
		}

		// *Ссылки
		$text= preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-\\#]+)\x07iu", [__CLASS__,"makeURL"], $text );

		// *Цитировать
		$cite= $this->useStartIndex? '<div class="cite">Цитировать</div>':'';

		$t= "<div class=\"msg\" id=\"msg_{$n}\" data-uid='{$UID}'><div class=\"info\" data-ip='{$IP}'><div><b>$n</b>. <span class=\"state\"></span><span class=\"name\">$name"
		. '</span><span class="misc"><span class="date">' . $ts . "</span></span></div>$cite</div>"
		. "<div class='post'>{$text}</div>";

		// *BB-codes
		$t= preg_replace([
			"~\\[cite\\](.+?)\\[/cite\\]~u",
			"~\\[([bi])\\](.+?)\\[/\\1\\]~u",
			"~\\[(u)\\](.+?)\\[/\\1\\]~u",
			"~\\[(s)\\](.+?)\\[/\\1\\]~u",
		], [
			"<div class='cite_disp'>$1</div>",
			"<$1>$2</$1>",
			"<ins>$2</ins>",
			"<del>$2</del>",
		], $t);

		if($files= json_decode($files, 1)){
			$t.= '<div class="imgs">';
			foreach($files as $f){
				// $f= self::getPathFromRoot($f);
				$t.= "<img src='/assets/placeholder.svg' data-src='$f' draggable='false' />";
			}
			$t.= '</div>';
		}

		$t.= "</div>\n\n";
		return $t;
	}


	static function makeURL( $matches ) {
		return '<a href="' . ( mb_strpos( $matches[1], "://" ) === false ? "http://" : "" ) . $matches[1] . '" target="_blank">' . $matches[1] . '</a>';
	}


	// *Обработка имени
	static function cleanName( $str ) {
		$str = filter_var(trim( $str ), FILTER_SANITIZE_STRING);
		$str = preg_replace( "~[^_0-9a-zа-яё\-\$]~iu", "", $str );
		return mb_substr( $str, 0, MAXUSERNAMELEN );
	}


	// *Обработка поста
	static function cleanText( $str ) {
		$str = filter_var(trim( $str ), FILTER_SANITIZE_STRING);
		$str = preg_replace( ["~\r~u", "~([\s\n]){5,}~u", "~\n~u"], ["", "$1$1$1$1", "<br />"], $str );
		return mb_substr( $str, 0, MAXUSERTEXTLEN );;
	}


	// *Архив
	function getArhive()
	{
		foreach(new FilesystemIterator(self::ARH_PATHNAME) as $fi){
			echo "<a href='/Archive.php?f=". self::getPathFromRoot($fi->getPathname()) ."'>". date("Y-m-d", $fi->getFilename()) ."</a><br>";
		}
	}
} // Chat