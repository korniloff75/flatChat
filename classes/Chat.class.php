<?php
require_once __DIR__ . "/../Helpers.trait.php";

class Chat
{
	use Helpers;

	const
		DBPATHNAME= \DBFILE,
		DELIM= "<~>";

	static
		$log;

	public
		$lastMod,
		$files;

	private
		$exit= false;

	protected
		$data=[];

	public function __construct()
	{
		$this->_setData();

		tolog(__METHOD__,null,['data'=>$this->data]);

		// new State($this->data);

		if ( ($this->lastMod = filemtime( self::DBPATHNAME )) === false ) $this->lastMod = 0;

		if($this->mode === 'post')
			$this->_newPost();
		// *Update list
		if ( $this->mode == "list" ) {
			$this->exit = true;

			$rlm = preg_match( "~^\\d+$~u", @$_POST["lastMod"] ) ? (int)$_POST["lastMod"] : 0;

			if ( $rlm == $this->lastMod ) self::Out( "NONMODIFIED", "" );
			else echo self::Out( "OK", null );
		}

		if ( $this->exit ) exit( 0 );

	}//__construct


	function __get($n)
	{
		return $this->$n ?? $this->data[$n];
	}

	function getData()
	{
		return $this->data;
	}


	private function _setData()
	{
		if($cookieName = (@$_COOKIE["userName"] ?? null))
			$cookieName = Chat::cleanName( $cookieName );

		$this->data['name'] = filter_var(@$_POST["name"]) ?? null;
		$this->data['ts'] = filter_var(@$_POST["ts"]);
		if(!$this->name) $this->data['name']= $cookieName;
		$this->data['text'] = self::cleanText(@$_POST["text"] ?? null);
		$this->data['IP']= self::realIP();
		$this->data['UID']= $this->name . substr($this->IP, 0, strrpos($this->IP, '.')+1);

		switch( @$_POST["mode"] ) {
			case "post":
				$mode = "post";
			break;

			case "list":
				$mode = "list";
			break;
		}

		$this->data['mode'] = $mode ?? null;

		if ( $this->name !== $cookieName ) setcookie( "userName", $this->name, mktime( 0, 0, 0, 12, 31, 3000 ), \COOKIEPATH );
	}


	private function _newPost()
	{
		if ( !$this->name || !$this->text ) {
			header( 'HTTP/1.1 400 Bad Request' );
			exit( 0 );
		}

		// $this->data['IP']= self::realIP();

		$this->exit = true;

		if ( $this->name != $this->cookieName ) setcookie( "userName", $this->name, mktime( 0, 0, 0, 12, 31, 3000 ), COOKIEPATH );

		// $this->text = preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-\\#]+)\x07iu", [__CLASS__,"makeURL"], $this->text );

		// *Uploads
		Uploads::$allow = ['jpg','jpeg','png','gif'];
		Uploads::$pathname = \DR.'/files_B';
		$upload = new Uploads(null, 'attach');
		$this->files = $upload->loaded;

		tolog('Uploads',null,['$upload'=>$upload]);

		// *Write
		// file_put_contents( self::DBPATHNAME, $this->Template(), LOCK_EX|FILE_APPEND );
		$this->_save();

		$this->mode = "list";

		$this->lastMod = filemtime( self::DBPATHNAME );
	}


	private function _save()
	{
		$data= [$this->IP,$this->ts,$this->name,$this->text,json_encode($this->files,  JSON_UNESCAPED_SLASHES)];

		// *Write
		file_put_contents( self::DBPATHNAME, implode(self::DELIM, $data) . PHP_EOL, LOCK_EX|FILE_APPEND );
	}


	public function Out( $status = null, $chat = null )
	:string
	{
		if ( $status !== null ) {
			echo "{$status}:{$this->lastMod}\n";
		}

		if ( $chat === null ) {
			if(!file_exists(self::DBPATHNAME)){
				$chat= [];
			}
			// *Читаем CHATTRIM байт с конца файла
			elseif ( CHATTRIM && filesize(self::DBPATHNAME) > CHATTRIM ) {
				$chat= self::rfileByte(self::DBPATHNAME, CHATTRIM);
				// $chat= self::rfile(self::DBPATHNAME, 10);

				// tolog(__METHOD__,null,['$chat1'=>$chat]);

			}
			// *Читаем весь файл
			else $chat = file(self::DBPATHNAME, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		}

		tolog(__METHOD__,null,['$chat2'=>$chat]);

		// return $chat;
		return $this->_parse($chat);
	}


	// todo
	private function _parse($chat)
	{
		ob_start();

		if($chat){
			array_walk($chat, function(&$v,$n){
				// *Разбираем построчно
				$v= explode(self::DELIM, $v);
				// *ts -> Date
				$v[1]= date('Y-m-j H:i', $v[1]);
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

		// *Ссылки
		$text= preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-\\#]+)\x07iu", [__CLASS__,"makeURL"], $text );

		$t= '<div class="msg" id="msg_'.$n.'"><div class="info"><div><b>' .$n. '</b>. <span class="name">' . "$name" . '</span><span class="misc"><span class="date">' . $ts . '</span> (<span class="ip">' . $IP . '</span>)</span></div><div class="cite">Цитировать</div></div>' . "<div class='post'>{$text}</div>";

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
				$f= self::getPathFromRoot($f);
				$t.= "<img src='$f' />";
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
		$str = trim( $str );
		$str = preg_replace( "~[^ 0-9a-zа-яё]~iu", "", $str );
		$str = mb_substr( $str, 0, MAXUSERNAMELEN );
		return $str;
	}


	// *Обработка поста
	static function cleanText( $str ) {
		$str = filter_var(trim( $str ));
		$str = preg_replace( "~\r~u", "", $str );
		// Глушит Юникод
		// $str = preg_replace( "\x07[^ \t\n!\"#$%&'()*+,\\-./:;<=>?@\\[\\]^_`{|}~0-9a-zа-яё]\x07iu", "", $str );
		$str = preg_replace( ["~&~u","~<~u","~>~u"], ["&amp;","&lt;","&gt;"], $str );
		$str = mb_substr( $str, 0, MAXUSERTEXTLEN );
		$str = preg_replace( ["~(\n){5,}~u", "~\n~u"], ["$1$1$1$1", "<br />"], $str );

		return $str;
	}
} // Chat