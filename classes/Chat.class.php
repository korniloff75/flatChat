<?php
require_once __DIR__ . "/../Helpers.trait.php";

class Chat
{
	use Helpers;

	const
		DBPATHNAME= \DBFILE . '_test',
		DELIM= "<~>";

	static
		$log;

	public
		$lastMod,
		$files;

	private
		$exit= false,
		$data=[];

	public function __construct()
	{
		$this->_setData();

		// if(!$this->mode) $this->_read();

		tolog(__METHOD__,null,['data'=>$this->data]);

		if ( ($this->lastMod = filemtime( DBFILE )) === false ) $this->lastMod = 0;

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


	// todo
	function Template() {
		$t= '<div class="msg"><div class="info"><span class="name">' . $this->name . '</span><span class="misc"><span class="date">' . date( "d.m.Y H:i:s" ) . '</span> <span class="id">(' . $this->IP . ')</span></span></div>' . "<div>{$this->text}</div>";

		if($this->img)
			$t.= "<img src='' />";

		$t.= "</div>\n\n";
		return $t;
	}

	private function _setData()
	{
		if($cookieName = (@$_COOKIE["userName"] ?? null))
			$cookieName = Chat::cleanName( $cookieName );

		$this->data['name'] = filter_var(@$_POST["name"]) ?? null;
		$this->data['ts'] = filter_var(@$_POST["ts"]);
		if(!$this->name) $this->data['name']= $cookieName;
		$this->data['text'] = self::cleanText(@$_POST["text"] ?? null);

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

		// $id= self::realIP();
		$this->data['IP']= self::realIP();

		$this->exit = true;

		if ( $this->name != $this->cookieName ) setcookie( "userName", $this->name, mktime( 0, 0, 0, 12, 31, 3000 ), COOKIEPATH );

		// $text = preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-]+)\x07iu", "makeURL", $text );
		$this->text = preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-]+)\x07iu", ['Chat',"makeURL"], $this->text );


		// *Uploads
		Uploads::$allow = ['jpg','jpeg','png','gif'];
		$upload = new Uploads(null, 'attach');
		$this->files = $upload->loaded;

		tolog('Uploads',null,['$upload'=>$upload]);

		// *Write
		file_put_contents( DBFILE, $this->Template(), LOCK_EX|FILE_APPEND );

		$this->mode = "list";

		$this->lastMod = filemtime( DBFILE );

		$this->_save();
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

			// *Читаем CHATTRIM байт с конца файла
			if ( CHATTRIM && filesize(self::DBPATHNAME) > CHATTRIM ) {
				$f = fopen( self::DBPATHNAME, "r" );
				fseek( $f, -CHATTRIM, SEEK_END );
				$chat = fread( $f, CHATTRIM );
				fclose( $f );
				// $p =  mb_strpos( $chat, '<div class="msg"' );
				$p =  mb_strpos( $chat, PHP_EOL );
				if ( $p !== false ) {
					$chat = mb_substr( $chat, $p );
					$chat = array_filter(explode(PHP_EOL, $chat));
				}
			}
			// *Читаем весь файл
			// else $chat = file_get_contents( self::DBPATHNAME );
			else $chat = file(self::DBPATHNAME, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
		}

		tolog(__METHOD__,null,['$chat'=>$chat]);

		// return $chat;
		return $this->_read($chat);
	}


	// todo
	private function _read(?array $chat)
	{
		ob_start();

		if($chat){
			array_walk($chat, function(&$v,$n)use($len){
				// *Разбираем построчно
				$v= explode(self::DELIM, $v);
				// *ts -> Date
				$v[1]= date('Y-m-j H:i', $v[1]);
				// $v= $this->_renderPost($n,$v);
				echo $this->_renderPost(++$n,$v);
			});
		}

		tolog(__METHOD__,null,['$chat'=>$chat,]);

		return ob_get_clean();
	}


	private function _renderPost($n,&$i)
	{
		// *Последовательность данных
		list($IP,$ts,$name,$text,$files)= $i;

		$t= '<div class="msg"><div class="info"><span class="name">' . "<b>{$n}</b>. $name" . '</span><span class="misc"><span class="date">' . $ts . '</span> <span class="id">(' . $IP . ')</span></span></div>' . "<div>{$text}</div>";

		if($files= json_decode($files, 1)){
			foreach($files as $f){
				$t.= "<div><img src='$f' /></div>";
			}
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
		$str = trim( $str );
		$str = preg_replace( "~\r~u", "", $str );
		$str = preg_replace( "\x07[^ \t\n!\"#$%&'()*+,\\-./:;<=>?@\\[\\]^_`{|}~0-9a-zа-яё]\x07iu", "", $str );
		$str = preg_replace( ["~&~u","~<~u","~>~u"], ["&amp;","&lt;","&gt;"], $str );
		$str = mb_substr( $str, 0, MAXUSERTEXTLEN );
		$str = preg_replace( ["~(\n){5,}~u", "~\n~u"], ["$1$1$1$1", "<br />"], $str );

		return $str;
	}
} // Chat