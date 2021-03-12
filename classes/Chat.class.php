<?php
require_once __DIR__ . "/../Helpers.trait.php";

class Chat
{
	use Helpers;

	const
		DBPATHNAME= \DBFILE,
		ARH_PATHNAME= \DR.'/db',
		FILES_DIR= '/files_B',
		DELIM= "<~>",
		MAX_LINES= 200,
		DELTA_LINES= 100;

	static
		$log,
		$indexes= ['IP','ts','name','text','files'];

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
			$rlm = (int)filter_var($_REQUEST["lastMod"], FILTER_SANITIZE_NUMBER_INT) ?? 0;

			if ( $rlm === $this->lastMod ) echo $this->Out( "NONMODIFIED" );
			else echo $this->Out( "OK", true );
		}
		else{
			// session_start();
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
		foreach($_REQUEST as $cmd=>&$val){
			$val= filter_var($val, FILTER_SANITIZE_STRING);

			if(method_exists(__CLASS__, "c_$cmd")){
				tolog(__METHOD__,null,['$cmd'=>$cmd, '$val'=>$val]);
				$this->{"c_$cmd"}($val);
			}
		}

		if($chatUser = json_decode(@$_COOKIE["chatUser"] ?? null, 1)){
			$this->data= array_merge($this->data, $chatUser);
		}
		else{
			$this->data['IP']= self::realIP();
		}

		$this->data['name'] = $this->name? $this->name: self::cleanName(@$_REQUEST["name"]) ?? null;
		$this->data['UID']= $this->_defineUID($this->name, $this->IP);

		tolog(__METHOD__,null,['$chatUser'=>$chatUser]);

		$this->data['ts'] = filter_var(@$_REQUEST["ts"]);
		// if(!$this->name) $this->data['name']= $cookieName;
		$this->data['text'] = self::cleanText(@$_REQUEST["text"] ?? null);

		$mode= &$this->data['mode'];

		$cook= json_encode([
			'name'=> $this->name,
			'IP'=> $this->IP,
			'UID'=> $this->UID,
		], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);

		switch( @$_REQUEST["mode"] ) {
			case "post":
				$mode = "post";
			break;

			case "list":
				$mode = "list";
			break;
		}

		// $this->data['mode'] = $mode ?? null;

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
		Uploads::$pathname = \DR.self::FILES_DIR;
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
		if($file= &$this->file) return $this;

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
			$render= is_adm()? '_renderAdmPost': '_renderPost';

			array_walk($chat, function(&$v,$n)use($render){
				// *Разбираем построчно
				$v= explode(self::DELIM, $v);
				// *ts -> Date
				$v[1]= date('Y-m-d H:i', $v[1]);

				echo $this->{$render}(++$n,$v);
			});
		}

		// tolog(__METHOD__,null,['$chat'=>$chat,]);

		return ob_get_clean();
	}


	// * ADMIN

	/**
	 * Отдаём пост по $num
	 * @param {} num - Номер поста из клиента
	 */
	public function c_getPost($num)
	{
		$state= new DbJSON(State::BASE_PATHNAME);
		$num-= $state->startIndex + 1;

		$post= array_combine(self::$indexes, explode(self::DELIM, $this->_checkDB()->file[$num]));
		echo json_encode($post, JSON_UNESCAPED_UNICODE);
		die;
	}


	/**
	 * Сохраняем редактирование постов
	 */
	protected function c_saveEdits($text)
	{
		if(!is_adm()) return;
		$state= new DbJSON(State::BASE_PATHNAME);
		$file= &$this->_checkDB()->file;
		$num= filter_var($_REQUEST['num'], FILTER_SANITIZE_NUMBER_INT);
		$num-= $state->startIndex + 1;

		$data= array_combine(self::$indexes, explode(self::DELIM, $file[$num]));

		$data['text']= self::cleanText($text);

		$file[$num]= implode(self::DELIM, $data);

		tolog(__METHOD__,null,['$num'=>$num,'$data'=>$data]);

		file_put_contents( $this->dbPathname, $file, LOCK_EX );

		$this->data['mode']= 'list';
	}

	/**
	 * !Удаление постов
	 */
	protected function c_removePost($num)
	{
		if(!is_adm()) return;
		$state= new DbJSON(State::BASE_PATHNAME);
		$file= &$this->_checkDB()->file;
		$num-= $state->startIndex + 1;

		$data= array_combine(self::$indexes, explode(self::DELIM, $file[$num]));

		// *Удаляем прикрепления
		if(!empty($files= json_decode($data['files']))) foreach($files as $f){
			tolog(__METHOD__,null,['$f'=>\DR.'/'. $f]);
			unlink(\DR.'/'. $f);
		}

		unset($file[$num]);
		$file= array_filter($file);

		tolog(__METHOD__,null,['$num'=>$num,'$data'=>$data]);

		file_put_contents( $this->dbPathname, $file, LOCK_EX );

		$this->data['mode']= 'list';
	}


	protected function c_logOut($a)
	{
		tolog(__METHOD__,null,['$_SESSION'=>$_SESSION]);
		$_SESSION= [];

		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(session_name(), '', time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
			);
		}

		session_destroy();
		echo "Сессия удалена";
		// header('Location: /');
		die;


		// $this->data['mode']= 'list';
	}


	/**
	 * Исправлена работа с кириллицей (!!!)
	 * https://snipp.ru/php/problem-domdocument
	 */
	private function _renderAdmPost($n,&$i)
	{
		$t= $this->_renderPost($n,$i);

		$doc = new DOMDocument("1.0","UTF-8");
		$doc->loadHTML("\xEF\xBB\xBF" . $t);

		$panel= $doc->createElement('div');
		$panel->setAttribute('class', 'adm');

		self::setDOMinnerHTML($panel,"<button class='edit' title='Редактировать'>✎</button><button class='del' title='Удалить'>❌</button>");

		$xpath = new DOMXpath($doc);

		// tolog(__METHOD__.' DOMXpath',null,['encoding'=>$doc->encoding, ]);

		$info= $xpath->query('//div[contains(@class, "info")]')->item(0);

		$info->appendChild($panel);

		$doc->normalize();

		return html_entity_decode($doc->saveHTML(), ENT_COMPAT);
	}

	// * /ADMIN


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

		$t= "<div class=\"msg\" id=\"msg_{$n}\" data-uid='{$UID}'><div class=\"info\" data-ip='{$IP}'><div><b class='num'>$n</b>. <span class=\"state\"></span><span class=\"name\">$name"
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