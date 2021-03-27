<?php
require_once \DR . "/core/Helpers.trait.php";

class Chat
{
	use Helpers;

	const
		DBPATHNAME= \DR."/chat.db",
		ARH_PATHNAME= \DR.'/db',
		FILES_DIR= '/files_B',
		DELIM= "<~>",
		MAX_LINES= 200,
		// MAX_LINES= 5,
		DELTA_LINES= 100,
		MAXUSERTEXTLEN= 1024,
		MAXUSERNAMELEN= 20,
		TEMPLATE_DEFAULT= '_default_';

	static
		// *Отладка
		$dev= true,
		$log,
		// *Порядок данных
		$indexes= ['IP','ts','name','text','files','appeals'];

	public
		$dbPathname,
		$file,
		$lastMod, //*Последнее изменение $file
		$useStartIndex= true, // *Увеличивать номер поста
		$templatePath,
		$templateDir,
		$out=[];

	private
		$exit= false;

	protected
		$uState=[], // *Данные пользователя
		$State, // *Общие данные
		$successPolling, //* Flag
		$renderMods=[]; //*Элементы шаблона


	public function __construct($dbPathname=null)
	{
		$this->dbPathname= $dbPathname ?? self::DBPATHNAME;

		$this->_setUState()
			->_controller();

		tolog(__METHOD__,null,['uState'=>$this->uState]);

		if ( ($this->lastMod = filemtime( $this->dbPathname )) === false ) $this->lastMod = 0;

		if($this->mode === 'status'){
			// $this->uState['on'] = false;
			// $this->State->users= [$this->UID=>$this->uState];
			// echo $this->Out( "OK", true );
			// $this->exit = true;
			die;
		}

		if($this->mode === 'post'){
			$this->_newPost();
			echo $this->Out( "OK", true );
			$this->exit = true;
			die;
		}

		if ( in_array($this->mode, ["set","remove"], true) ) {
			$this->exit = true;
		}

		// *Update list
		if ( $this->mode === "list" ) {
			// die;
			$this->exit = true;

			$rlm = (int)filter_var($_REQUEST["lastMod"], FILTER_SANITIZE_NUMBER_INT) ?? 0;

			/* if ( $rlm === $this->lastMod ) echo $this->Out( "NONMODIFIED" );
			else echo $this->Out( "OK", true ); */

			// echo 123;
			$this->_pollingServer($rlm);
		}

		tolog(__METHOD__,null,['$this->mode'=>$this->mode]);

		if ( $this->exit ) exit( 0 );

	}//__construct


	function __get($n)
	{
		return $this->$n ?? $this->uState[$n] ?? null;
	}

	public function getJsonUState()
	:string
	{
		tolog(__METHOD__,null,['$this->uState'=>$this->uState]);
		return json_encode($this->uState,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	}


	static function defineUID($name, $IP)
	{
		// tolog(__METHOD__,null,[$name,$IP,($name . substr($IP, 0, strrpos($IP, '.')+1))]);
		return $name . substr($IP, 0, strrpos($IP, '.')+1);
	}


	private function _setUState()
	{
		// *fix 4 polling
		$_SESSION = $_SESSION ?? [];

		// *Получаем данные из fetch
		$inp_data= json_decode(
			file_get_contents('php://input'),1
		) ?? [];
		tolog(__METHOD__,null,['$inp_data'=>$inp_data]);

		// *Собираем все входящие в $_REQUEST
		$_REQUEST= array_merge($_REQUEST, $inp_data);


		if($chatUser = json_decode(@$_COOKIE["chatUser"] ?? null, 1)){
			$this->uState= array_merge($this->uState, $chatUser);
		}
		else{
			$this->uState['IP']= self::realIP();
		}

		tolog(__METHOD__,null,['$_SESSION'=>$_SESSION, ]);

		// $this->uState['ts'] = filter_var(@$_REQUEST["ts"]);

		$this->uState['on'] = true;

		if(isset($_REQUEST['chatUser'])){
			$this->uState= array_replace($this->uState, json_decode($_REQUEST['chatUser'],1));
			tolog(__METHOD__,null,['chatUser'=>$_REQUEST['chatUser'] ]);
			// $this->uState['on'] = $_REQUEST['chatUser']['on'];
		}

		$this->uState['name'] = $this->name? $this->name: $_SESSION['user']['name'] ?? self::cleanName(@$_REQUEST["name"]) ?? null;
		$this->uState['UID']= self::defineUID($this->name, $this->IP);

		$this->uState['ts'] = time();

		$this->uState['text'] = self::cleanText(@$_REQUEST["text"] ?? null);


		// *Записали и обновили $this->uState
		tolog(__METHOD__,null,['uState before UPD'=>$this->uState]);
		$this->State= new State($this->uState);

		$this->uState= $this->State->users[$this->UID];
		tolog(__METHOD__,null,['uState after UPD'=>$this->uState]);

		// if(POLLING) file_put_contents('test1', __METHOD__. json_encode($this->uState, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

		return $this;
	}


	protected function _controller()
	{
		$this->uState['mode']= @$_REQUEST["mode"];

		foreach($_REQUEST as $cmd=>&$val){

			if(method_exists(__CLASS__, "c_$cmd")){
				$val= filter_var($val, FILTER_SANITIZE_STRING);
				tolog(__METHOD__,null,['$cmd'=>$cmd, '$val'=>$val]);
				$this->{"c_$cmd"}($val);
			}
		}

		$cook= json_encode([
			'name'=> $this->name,
			'IP'=> $this->IP,
			'UID'=> $this->UID,
		], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);


		if ( $cook !== @$_COOKIE["chatUser"] ) setcookie( "chatUser", $cook, mktime( 0, 0, 0, 12, 31, 3000 ), \COOKIEPATH, '', false, true );
	}


	/**
	 * 
	 */
	private function _newPost()
	{
		if ( !$this->name || !$this->text && empty($_FILES) ) {
			header( 'HTTP/1.1 400 Bad Request' );
			die( "Полученные данные не прошли серверную валидацию." );
		}

		$this->uState['myUID']= self::defineUID($this->name, $this->IP);

		$this->exit = true;

		// *Uploads
		Uploads::$allow = ['jpg','jpeg','png','gif'];
		Uploads::$pathname = \DR.self::FILES_DIR;
		$upload = new Uploads(null, 'attach');

		tolog('Uploads',null,['$upload'=>$upload]);

		// *Write
		$this->_save($upload->loaded);

		// $this->mode = "list";

		$this->lastMod = filemtime( $this->dbPathname );
	}


	private function _save($files= [])
	{
		array_walk($files, function(&$f){
			$f= self::getPathFromRoot($f);
		});

		$data= [$this->IP,$this->ts,$this->name,$this->text,json_encode($files,  JSON_UNESCAPED_SLASHES),filter_var(@$_REQUEST['appeals'])];

		// *Write
		file_put_contents( $this->dbPathname, implode(self::DELIM, $data) . PHP_EOL, LOCK_EX|FILE_APPEND );
	}


	/**
	 * static output content
	 */
	public function getHTMLContent()
	:string
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

		self::createDir(self::ARH_PATHNAME, 0776);

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
				$this->State->startIndex= ($this->State->startIndex ?? 0) + ($count - self::MAX_LINES);
				$file= $newFile;
			}

		}

		return $this;
	}


	public function Out( $status = null, $is_modified = false )
	:string
	{
		if(self::$dev){
			header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
			header('Pragma: no-cache'); // HTTP 1.0.
			header('Expires: 0'); // Proxies.
		}

		$out= &$this->out;
		$out['html']= !is_null($status)? "{$status}:{$this->lastMod}\n": '';

		if ( $is_modified ) {
			$out['html'].= $this->_parse();
		} //$is_modified

		// tolog(__METHOD__,null,['$chat2'=>$chat]);

		tolog(__METHOD__,null,['$out'=>$out]);
		// var_dump($out);

		$out['state']= $this->State->get();
		$out['Chat']= $this->uState;
		$out['is_https']= self::is('https');

		$this->successPolling = true;

		return json_encode($out, JSON_UNESCAPED_UNICODE);
	}


	private function _parse()
	:?string
	{
		if(!$chat= $this->_checkDB()->file) return null;
		ob_start();

		$render= is_adm()? '_renderAdmPost': '_renderPost';

		foreach($chat as $n=>&$v){
			// *Разбираем построчно
			$v= explode(self::DELIM, trim($v));

			// *fix old posts
			$indexes= array_slice(self::$indexes, 0, count($v));
			// tolog(__METHOD__,null,['$indexes'=>$indexes,$v]);
			$v= array_combine($indexes, $v);

			// *ts -> Date
			$v['ts']= date('Y-m-d H:i', $v['ts']);

			echo $this->{$render}(++$n,$v);
		};

		// tolog(__METHOD__,null,['$chat'=>$chat,]);

		return ob_get_clean();
	}


	// * ADMIN

	/**
	 * Отдаём пост по $num
	 * @param {} num - Номер поста из клиента
	 * todo при сохранении сжираются переводы строк.
	 */
	public function c_getPost($num)
	{
		$num-= $this->State->startIndex + 1;

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
		$file= &$this->_checkDB()->file;
		$num= filter_var($_REQUEST['num'], FILTER_SANITIZE_NUMBER_INT);
		$num-= $this->State->startIndex + 1;

		$data= array_combine(self::$indexes, explode(self::DELIM, $file[$num]));

		$data['text']= self::cleanText($text);

		$file[$num]= implode(self::DELIM, $data);

		tolog(__METHOD__,null,['$num'=>$num,'$data'=>$data]);

		file_put_contents( $this->dbPathname, $file, LOCK_EX );

		$this->mode = "list";
	}


	/**
	 * *Закрепление поста
	 */
	protected function c_pinPost($num)
	{
		if(!is_adm()) return;
		if($this->mode === 'set'){
			$this->State->set(['pinned'=>(int) $num]);
			echo "Post $num pinned.";
		}
		elseif($this->mode === 'remove'){
			$this->State->remove('pinned');
			echo "Post $num unpinned.";
		}

	}


	/**
	 * !Удаление постов
	 * @param {int|array} $nums
	 */
	protected function c_removePost($nums)
	{
		if(!is_adm()) return;

		$nums= json_decode($nums, 1);
		$nums= is_array($nums)? $nums: [$nums];

		tolog(__METHOD__,null,['$nums'=>$nums]);

		$file= &$this->_checkDB()->file;

		foreach($nums as $num){
			$num-= $this->State->startIndex + 1;

			$data= array_combine(self::$indexes, explode(self::DELIM, $file[$num]));

			// *Удаляем прикрепления
			if(!empty($files= json_decode($data['files']))) foreach($files as $f){
				tolog(__METHOD__,null,['$f'=>\DR.'/'. $f]);
				unlink(\DR.'/'. $f);
			}

			unset($file[$num]);
			// tolog(__METHOD__,null,['$num'=>$num,'$data'=>$data]);
		}

		$file= array_values(array_filter($file));

		file_put_contents( $this->dbPathname, $file, LOCK_EX );

		$this->mode= 'list';
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

	}


	/**
	 * Исправлена работа с кириллицей (!!!)
	 * https://snipp.ru/php/problem-domdocument
	 */
	private function _renderAdmPost($n,&$i)
	{
		$t= $this->_renderPost($n,$i);

		if(!is_adm()) return $t;

		$doc = new DOMDocument("1.0","UTF-8");
		$doc->loadHTML("\xEF\xBB\xBF" . $t);

		$panel= $doc->createElement('div');
		$panel->setAttribute('class', 'adm');

		self::setDOMinnerHTML($panel,"<button class='pin' title='Закрепить'>📌</button><button class='edit' title='Редактировать'>✎</button><button class='del' title='Удалить'>❌</button>");

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
		// tolog(__METHOD__,null,[$i]);
		extract($i);

		$UID= self::defineUID($name, $IP);
		if($this->useStartIndex){
			$n+= $this->State->startIndex;
		}

		if(!isset($appeals)) $appeals= '';

		$pinned= empty($this->State->pinned) ? -1: $this->State->pinned;

		// *Ссылки
		$text= preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-\\#]+)\x07iu", [__CLASS__,"makeURL"], $text );

		// *Цитировать
		$cite= $this->useStartIndex? '<div class="cite btn">Цитировать</div>':'';

		$t= "<div class=\"msg "
		. ($pinned === $n? 'pinned':'')
		. "\" id=\"msg_{$n}\" data-uid='{$UID}' data-appeals='{$appeals}'><div class=\"info\" data-ip='{$IP}'><div><label class='select'><input type='checkbox'><b class='num'>$n</b></label> <!--<span class=\"state\"></span>--><span class=\"name\">$name"
		. '</span><span class="misc"><span class="date">' . $ts . "</span></span></div>$cite<div class='voice button' title='Озвучить текст'>📢🎧</div></div>"
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

		if($files= json_decode($files)){
			$t.= '<div class="imgs">';
			foreach($files as $f){
				// $f= self::getPathFromRoot($f);
				$t.= "<img src='./assets/placeholder.svg' data-src='/$f' data-test='".realpath("./$f")."' draggable='false' />";
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
		return mb_substr( $str, 0, self::MAXUSERNAMELEN );
	}


	// *Обработка поста
	static function cleanText( $str ) {
		$str = filter_var(trim( $str ), FILTER_SANITIZE_STRING);
		// $str = filter_var(trim( $str ), FILTER_SANITIZE_SPECIAL_CHARS);
		// $str= htmlspecialchars(trim( $str ));
		$str = preg_replace( ["~\r~u", "~([\s\n]){5,}~u", "~\n~u"], ["", "$1$1$1$1", "<br />"], $str );
		return mb_substr( $str, 0, self::MAXUSERTEXTLEN );
	}


	// todo
	// *Архив
	function getArhives()
	{
		if(is_dir(self::ARH_PATHNAME)) foreach(new FilesystemIterator(self::ARH_PATHNAME) as $fi){
			echo "<a href='./core/Archive.php?f=". self::getPathFromRoot($fi->getPathname()) ."'>". date("Y-m-d", $fi->getFilename()) ."</a> | ";
		}
	}


	// *Собираем шаблон
	private function _scanModsContent()
	{
		$def_dir= \DR.'/templates/' . self::TEMPLATE_DEFAULT;

		// *Перебираем элементы страницы
		foreach(['head','header','footer'] as $mod){
			$modPathname= "{$this->templatePath}/$mod.php";
			if(!file_exists($modPathname) && $this->templatePath !== $def_dir)
				$modPathname= "$def_dir/$mod.php";

			ob_start();
			include_once $modPathname;

			// *Подключаем базовые модули шаблона к пользовательским
			if(file_exists($coreMod= \DR."/core/$mod.php")){
				require_once $coreMod;
			}

			$this->renderMods[$mod]= ob_get_clean();
		}

		return $this->renderMods;
	}


	/**
	 * *Сборка выбранного шаблона
	 * optional @param {string} template - Название шаблона
	 */
	public function setTemplate($template=null)
	{
		$template= $this->uState['template'];

		tolog(__METHOD__,null,['$template'=>$template, '$this->uState'=>$this->uState]);

		$this->templatePath= $template ?? \DR.'/templates/' . self::TEMPLATE_DEFAULT;
		$this->templateDir= '/'. self::getPathFromRoot($this->templatePath);

		// tolog(__METHOD__,null,['$this->templatePath'=>$this->templatePath]);
		// trigger_error($this->templatePath);

		return $this->_scanModsContent();
	}

	// *Сохраняем выбранный шаблон
	protected function c_changeTemplate($template=null)
	{
		// tolog(__METHOD__,null,['$this->uState'=>$this->uState, '$this->UID'=>$this->UID]);

		if($template= self::fixSlashes($template)){
			$this->State->users= [$this->UID=>['template'=>$template]];
		}
	}


	/**
	 * Демон
	 */
	private function _pollingServer($rlm)
	{
		$exec_time = 0; //sec
		// set_time_limit(ini_get('max_execution_time') /2);
		// ignore_user_abort(true);
		set_time_limit($exec_time);
		// $start_time= time();

		tolog(__METHOD__,null,['$rlm'=>$rlm, '$this->lastMod'=>$this->lastMod, 'connection_status()'=>connection_status()]);

		// if(POLLING) file_put_contents('test1', __METHOD__. json_encode($this->uState, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

		// $this->State->set(['users'=>[$this->UID=>['test'=>true]]]);

		if(POLLING) file_put_contents('test1', __METHOD__.' - State - '. json_encode($this->State->get(), JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

		// main loop
		while (1) {
			$this->uState['ts'] = time();
			$this->uState['on'] = true;
			$this->State->users= [$this->UID=>$this->uState];
			// $this->State->save();
			// if no timestamp delivered via ajax or data.txt has been changed SINCE last ajax timestamp
			// $t= "$rlm === {$this->lastMod}\n";


			// *Обновление
			if ( $rlm !== $this->lastMod ) {

				echo $this->Out( "OK", true );

				if(!ob_end_flush()) flush();

				/* // *При обрыве соединения
				if(connection_status() != CONNECTION_NORMAL){
					$this->uState['on'] = false;
					$this->State->users= [$this->UID=>$this->uState];

					file_put_contents('test', __METHOD__.' - uState - '. json_encode($this->State->get(), JSON_UNESCAPED_UNICODE));
					// break;
				} */

				// file_put_contents('test',$t, FILE_APPEND);

				// leave this loop step
				break;

			} else {
				/* if((time() - $start_time) > $exec_time){
					echo $this->Out( "NONMODIFIED" );
					break;
				} */

				sleep( 1 );
				clearstatcache();
				$this->lastMod = filemtime( $this->dbPathname );
				continue;
			}

		}

		die;

	}


	function __destruct()
	{
		// if(POLLING) file_put_contents('test', __METHOD__.' - State - '. json_encode($this->State->db->get(), JSON_UNESCAPED_UNICODE));

		if(!$this->successPolling){
			echo $this->Out( "NONMODIFIED" );
		}
		// $this->State->users = [$this->UID=>['on'=>false]];
	}
} // Chat