<?php
require_once __DIR__."/../Helpers.trait.php";

class Chat
{
	use Helpers;

	const
		// DEV= true,
		DEV= false,
		DBPATHNAME= \DR."/chat.db",
		ARH_PATHNAME= \DR.'/db',
		AUTH_PATHNAME= \DR.'/assets/adm.db.json',
		FILES_DIR= '/files_B',
		ADM= [
			'feedback'=>"<a href='//t.me/js_master_bot'>Telegram</a>",
		],
		DELIM= "<~>",
		MAX_LINES= 300,
		// MAX_LINES= 5,
		DELTA_LINES= 200,
		MAXUSERTEXTLEN= 1024,
		MAXUSERNAMELEN= 25,
		TEMPLATE_DEFAULT= '_default_';

	static
		$secretLen= 3,
		// *Порядок данных
		$indexes= ['IP','ts','name','text','files','appeals'],
		$templateModules= ['head','header','sendForm','footer','scrollNav'];

	public
		$dbPathname,
		$file,
		$lastMod, //*Последнее изменение $file
		$useStartIndex= true, // *Увеличивать номер поста
		$templatePath,
		$templateDir,
		$secret,
		$out=[];

	private
		$exit= false;

	protected
		$State, // *Общие данные
		$uState=[], // *Данные пользователя
		$successPolling, //? Flag
		$renderMods=[]; //*Элементы шаблона


	public function __construct($dbPathname=null)
	{
		$this->dbPathname= $dbPathname ?? self::DBPATHNAME;

		$this->Online= new DbJSON(\DR.'/online.json');

		$this->_setUState()
			->_controller();

		tolog(__METHOD__,null,['$this->mode'=>$this->mode, 'self::getPathFromRoot(\DR)'=>self::getPathFromRoot(\DR.'/')]);

		if ( ($this->lastMod = filemtime( $this->dbPathname )) === false ) $this->lastMod = 0;

		if($this->mode === 'post'){
			if($this->uState['ban']){
				header('Location: /' . self::getPathFromRoot(\DR.'/'));
				die;
			}
			$this->_auth()
				->_newPost();
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

			$this->_pollingServer($rlm);
		}


		if ( $this->exit ) exit( 0 );

		// *Выводим в шаблон
		$this->setTemplate();

	}//__construct


	function __get($n)
	{
		return $this->$n ?? $this->uState[$n] ?? null;
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


		if($chatUserCook = json_decode(@$_COOKIE["chatUser"] ?? null, 1)){
			$this->uState= array_merge($this->uState, $chatUserCook);
		}
		else{
			$this->uState['IP']= self::realIP();
		}

		tolog(__METHOD__,null,['$_SESSION'=>$_SESSION, ]);

		// $this->uState['ts'] = filter_var(@$_REQUEST["ts"]);

		if(
			$chatUser = json_decode(@$_REQUEST['chatUser'] ?? null,1)
		){
			$this->uState= array_replace($this->uState, $chatUser);
		}

		tolog(__METHOD__,null,['$chatUserCook'=>$chatUserCook, /* '$chatUser'=>$chatUser */]);

		$this->uState['name'] = $this->name? $this->name: $_SESSION['user']['name'] ?? self::cleanName(@$_REQUEST["name"]) ?? null;

		if( empty($this->uState['name']) ){
			if( self::is('ajax') ) throw new Exception("Пользователь без имени");

			$this->out['reject']= true;
			$this->out['html']= "<div>Чтобы получать обновления чата напишите свой первый пост!</div>";
		}

		// if( empty($this->uState['name']) && self::is('ajax') ) throw new Exception("Пользователь без имени");

		$this->uState['UID']= self::defineUID($this->name, $this->IP);

		$this->uState['ts'] = time();
		$this->Online->set([$this->UID=>['ts'=>$this->uState['ts']]]);

		// $this->uState['text'] = self::cleanText(@$_REQUEST["text"] ?? null);

		tolog(__METHOD__,null,['uState before UPD'=>$this->uState]);

		$this->State= new State($this->uState);

		tolog(__METHOD__,null,['uState after UPD'=>$this->uState]);

		// if(POLLING) file_put_contents('test1', __METHOD__. json_encode($this->uState, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

		return $this;
	}


	protected function _controller()
	{
		$this->uState['mode']= $_REQUEST["mode"] ?? null;

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
			'ban'=> $this->ban ?? false,
		], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);


		if ( $cook !== @$_COOKIE["chatUser"] ) setcookie( "chatUser", $cook, mktime( 0, 0, 0, 12, 31, 3000 ), \COOKIEPATH, '', false, true );
	}


	protected function _auth(){
		$base= new DbJSON(self::AUTH_PATHNAME);
		$rSecret= trim($_REQUEST['secret']);

		if(strlen($rSecret) >= self::$secretLen){
			$hash= md5($rSecret);
		}

		// tolog(__METHOD__ . ' before',null,['$this->name'=>$this->name, '$rSecret'=>$rSecret, '$base->{$this->name}'=>$base->{$this->name}, '$hash'=>$hash, 'empty($base->{$this->name})'=>empty($base->{$this->name})]);

		// note Reset secret
		// $base->remove($this->name);

		if(is_null($base->{$this->name})){
			$base->set([$this->name=>$hash]);
		}

		elseif($base->{$this->name} === $hash){
			$_SESSION['secret']= $rSecret;
		}
		else{
			// die('{"error":"Auth was FAIL"}');
			$_SESSION['secret']=null;
			$this->out['reject']=1;
			$this->out['html']="Вы пытаетесь войти под чужой учётной записью.";
			die($this->Out('OK'));
		}

		// tolog(__METHOD__,null,['$this->name'=>$this->name, '$rSecret'=>$rSecret, '$base->{$this->name}'=>$base->{$this->name}, '$hash'=>$hash]);

		return $this;
	}



	/**
	 *
	 */
	private function _newPost()
	{
		if (
			!$this->name
			|| empty($text = self::cleanText(@$_REQUEST["text"] ?? null))
			&& empty($_FILES)
		) {
			header( 'HTTP/1.1 400 Bad Request' );
			die( "Полученные данные не прошли серверную валидацию." );
		}

		// $this->uState['myUID']= self::defineUID($this->name, $this->IP);

		$this->exit = true;

		// *Uploads
		Uploads::$allow = ['jpg','jpeg','png','gif'];
		Uploads::$pathname = \DR.self::FILES_DIR;
		$upload = new Uploads(null, 'attach');

		if(count($upload->loaded)) tolog('Uploads',null,['$upload'=>$upload]);

		// *Write
		$this->_save($text, $upload->loaded);

		clearstatcache();
		$this->lastMod = filemtime( $this->dbPathname );

		die($this->Out( "OK", true ));
	}


	private function _save(?string $text, $files= [])
	{
		array_walk($files, function(&$f){
			$f= self::getPathFromRoot($f);
		});

		// $this->text= $text;
		$appeals= filter_var(@$_REQUEST['appeals']);

		foreach(self::$indexes as $ind){
			$i = $this->{$ind} ?? $$ind;
			$data[]= is_array($i)? json_encode($i, JSON_UNESCAPED_SLASHES): $i;
		}

		// *Write
		file_put_contents( $this->dbPathname, implode(self::DELIM, $data) . PHP_EOL, LOCK_EX|FILE_APPEND );
	}


	/**
	 * static output content
	 */
	public function getHTMLContent()
	:string
	{
		if(self::DEV){
			header('Cache-Control: no-cache, no-store, must-revalidate'); // HTTP 1.1.
			header('Pragma: no-cache'); // HTTP 1.0.
			header('Expires: 0'); // Proxies.
		}
		$this->Out(null, true);
		return $this->out['html'];
	}



	/**
	 * *Разбиваем базу, добавляя лишнее в архив self::ARH_PATHNAME/*(ts)
	 */
	private function _checkDB()
	{
		if($file= &$this->file) return $this;

		if($file= file($this->dbPathname, FILE_SKIP_EMPTY_LINES)){
			$count= count($file);
		}
		else tolog(__METHOD__,E_USER_ERROR,['$this->dbPathname'=>$this->dbPathname]);



		self::createDir(self::ARH_PATHNAME, 0776);
		self::createDir(self::ARH_PATHNAME.'/imgs', 0776);

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
			// *Обрезаем базу
			$newFile= array_splice($file, -self::MAX_LINES);

			// *remove imgs
			foreach($file as &$str){
				$str_arr= explode(self::DELIM, $str);
				// *fix old posts
				$indexes= array_slice(self::$indexes, 0, count($str_arr));

				extract($data= array_combine($indexes, $str_arr));
				// *Перемещаем прикрепления
				if(!empty($files= json_decode($files))) foreach($files as &$f){
					tolog(__METHOD__,null,['$f'=>\DR.'/'. $f]);
					$im_pathname= self::ARH_PATHNAME.'/imgs/' .basename($f);
					rename(\DR.'/'. $f, $im_pathname);
					$f= self::getPathFromRoot($im_pathname);
				}

				foreach($indexes as $p=>$ind){
					$str_arr[$p]= $$ind;
				}

				$str= implode(self::DELIM, $str_arr);
			}

			if(
				file_put_contents( $this->dbPathname, $newFile, LOCK_EX )
				// *Добавляем в архив
				&& file_put_contents( self::ARH_PATHNAME.'/'.time(), $file, LOCK_EX )
			){
				$this->State->set(['startIndex'=> $this->State->startIndex + ($count - self::MAX_LINES)]);

				$file = $newFile;
			}

		}

		return $this;
	}


	/**
	 *
	 */
	public function Out( $status = null, $is_modified = false )
	:string
	{
		$out= &$this->out;

		// *$out already exist
		/* if(!empty($out['html'])){
			tolog(__METHOD__,Logger::BACKTRACE,['$out'=>$out]);
			return json_encode($out, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
		} */

		$out['html'] = (!is_null($status)? "{$status}:{$this->lastMod}\n": '') . ($out['html'] ?? '');

		if ( empty($out['reject']) && $is_modified ) {
			$out['html'].= $this->_parse();
		} //$is_modified

		// tolog(__METHOD__,Logger::BACKTRACE,['$out'=>$out]);
		// var_dump($out);

		tolog(__METHOD__,null,['$this->uState'=>$this->uState]);

		// $this->State= $this->State ??

		$out= array_merge($out, [
			'online'=> $this->Online->get(),
			'state'=> $this->State->update()->get(),
			'UID'=> $this->UID,
			'lastMod'=> $this->lastMod,
			'is_https'=> self::is('https'),
		]);

		return json_encode($out, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
	}


	private function _parse()
	:?string
	{
		if(!$chat= $this->_checkDB()->file) return null;
		ob_start();

		$render= is_adm()? '_renderAdmPost': '_renderPost';

		tolog(__METHOD__,null,['is_adm()'=>is_adm(), '$render'=>$render]);

		foreach($chat as $n=>&$v){
			// *Разбираем построчно
			$v= explode(self::DELIM, trim($v));

			// *fix old posts
			$indexes= array_slice(self::$indexes, 0, count($v));
			// tolog(__METHOD__,null,['$indexes'=>$indexes,$v]);
			$v= array_combine($indexes, $v);

			$v['UID']= self::defineUID($v['name'], $v['IP']);

			$v['online']= $this->Online->{$v['UID']}['ts'] ?? 0;
			// *ts -> Date
			$v['date']= date('Y-m-d H:i', $v['ts']);

			echo $this->{$render}(++$n,$v);
		};

		// tolog(__METHOD__,null,['$chat'=>$chat,]);

		tolog(__METHOD__,null,['buf'=>ob_get_contents()]);

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
			$this->State->set(['pinned' => (int) $num]);
			echo "Post $num pinned.";
		}
		elseif($this->mode === 'remove'){
			$this->State->set(['pinned' => -1]);
			echo "Post $num unpinned.";
		}

		$this->State->save();
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


	/**
	 * *Блокировка (БАН)
	 */
	protected function c_banUser($uid)
	{
		// tolog(__METHOD__,null,['$_SESSION'=>$_SESSION]);

		if(!is_adm()) return;

		$bool= filter_var($_REQUEST['bool'], FILTER_VALIDATE_BOOLEAN);

		$this->State->set(['users'=> [$uid=>['ban'=>$bool?true:false]]]);
		$this->State->save();

		tolog(__METHOD__,null,['banned user state'=>$this->State->users[$uid]]);

		die($this->Out( "OK", true ));
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

		$adm= "<button class='pin' title='" . ($this->State->pinned === $n? 'Открепить': 'Закрепить') . "'>📌</button><button class='edit' title='Редактировать'>✎</button><button class='del' title='Удалить'>❌</button>";

		// var_dump($i, self::defineUID($i['name'], $i['IP']));

		$uid= self::defineUID($i['name'], $i['IP']);
		$banned= $this->State->users[$uid]['ban'] ?? 0;

		if($uid !== $this->UID){
			$adm.= "<button class='ban'>". ($banned? 'UnBan':'Ban') ."</button>";
		}

		self::setDOMinnerHTML($panel,$adm);

		$xpath = new DOMXpath($doc);

		// tolog(__METHOD__.' DOMXpath',null,['encoding'=>$doc->encoding, ]);

		$info= $xpath->query('//div[contains(@class, "info")]')->item(0);

		$info->appendChild($panel);

		$doc->normalize();

		$body= $xpath->query('//body')->item(0);

		// $t= html_entity_decode(self::getDOMinnerHTML($body), ENT_COMPAT);
		$t= self::getDOMinnerHTML($body);

		return $t;
	}

	// * /ADMIN


	private function _renderPost($n,&$i)
	{
		// tolog(__METHOD__,null,[$i]);
		extract($i);

		if($this->useStartIndex){
			$n+= $this->State->startIndex;
		}

		if(!isset($appeals)) $appeals= '';

		$pinned= $this->State->pinned === $n;
		$banned= $this->State->users[$UID]['ban'] ?? 0;

		// *Ссылки
		$text= preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-\\#]+)\x07iu", [__CLASS__,"makeURL"], $text );

		// *Цитировать
		$cite= $this->useStartIndex? '<div class="cite btn">Цитировать</div>':'';

		$is_online= (time() - $online) < \REFRESHTIME;
		$nameClass= 'name' . ($is_online? ' on':'');

		// tolog(__METHOD__,null,['$nameClass'=>$nameClass, (time() - $online), \REFRESHTIME]);

		// var_dump(time(), $online, (time() - $online), \REFRESHTIME);

		$t = "<div class=\"msg "
		. ($pinned? 'pinned ':'')
		. ($banned? 'banned ':'')
		. "\" id=\"msg_{$n}\" data-uid='{$UID}' data-appeals='{$appeals}'><div class=\"info\" data-ip='{$IP}'><div><label class='select'><input type='checkbox'><b class='num'>$n</b></label> <!--<span class=\"state\"></span>--><span class='{$nameClass}'>$name"
		. '</span><span class="misc"><span class="date">' . $date . "</span></span></div>$cite<div class='voice button' title='Озвучить текст'>📢🎧</div></div>"
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
			if($fi->isDir()) continue;

			echo "<a href='./core/Archive.php?f={$fi->getFilename()}'>". date("Y-m-d", $fi->getFilename()) ."</a> | ";
		}
	}


	// *Собираем шаблон
	private function _scanModsContent()
	{
		$defTpl_path= \DR.'/templates/' . self::TEMPLATE_DEFAULT;

		// *Перебираем элементы страницы
		foreach(self::$templateModules as $modName){
			$modPathname= "{$this->templatePath}/$modName.php";
			if(!file_exists($modPathname) && $this->templatePath !== $defTpl_path)
				$modPathname= "$defTpl_path/$modName.php";

			ob_start();
			if(file_exists($modPathname)) include_once $modPathname;

			// *Подключаем базовые модули шаблона к пользовательским
			if(file_exists($coreMod= \DR."/core/$modName.php")){
				require_once $coreMod;
			}

			$this->renderMods[$modName]= ob_get_clean();
		}

		// tolog(__METHOD__,null,['$this->renderMods'=>$this->renderMods]);

		return $this->renderMods;
	}


	public function getTemplateModule(string $modName)
	{
		if(empty($this->renderMods)){
			$this->setTemplate();
		}
		return $this->renderMods[$modName] ?? '';
	}


	/**
	 * *Сборка выбранного шаблона
	 * optional @param {string} template - Название шаблона
	 */
	public function setTemplate($template=null)
	{
		$template= $this->uState['template'];

		$this->templatePath= \DR.'/templates/' . ($template ?? self::TEMPLATE_DEFAULT);
		$this->templateDir= '/'. self::getPathFromRoot($this->templatePath);

		tolog(__METHOD__,null,['$template'=>$template, '$this->templatePath'=>$this->templatePath, '$this->templateDir'=>$this->templateDir]);
		// trigger_error($this->templatePath);

		return $this->_scanModsContent();
	}

	// *Сохраняем выбранный шаблон
	protected function c_changeTemplate($template=null)
	{
		// tolog(__METHOD__,null,['$this->uState'=>$this->uState, '$this->UID'=>$this->UID]);

		if($template){
			$this->uState['template'] = $template;
			$this->State->update($this->uState)->save();
			// $this->State= $this->State->save();

			tolog(__METHOD__,null,['$this->uState'=>$this->uState]);

			echo $this->Out( "NONMODIFIED" );
			flush();
		}
		die;
	}


	/**
	 * Демон
	 */
	private function _pollingServer($rlm)
	{
		if($this->uState['ban']) return;

		$exec_time = 0; //sec
		$counter = 0;
		$loop_time = 3; //sec

		// *Включает скрытое очищение вывода так, что мы видим данные как только они появляются.
		ob_implicit_flush();

		set_time_limit($exec_time);

		tolog(__METHOD__,null,['$rlm'=>$rlm, '$this->lastMod'=>$this->lastMod, 'connection_status()'=>connection_status()]);

		// if(POLLING) file_put_contents('test1', __METHOD__. json_encode($this->uState, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND);

		// main loop
		while (1) {
			++$counter;

			$this->Online= new DbJSON(\DR.'/online.json');

			$this->Online->set([$this->UID=>['ts'=>time()]]);

			// *Обновление
			if (
				$rlm !== $this->lastMod
				&& 'post' !== $this->mode
			) {

				echo $this->Out( "OK", true );

				// leave this loop step
				break;

			}
			// *Ограничиваем количество итераций
			elseif($counter >= 100){
				echo $this->Out( "NONMODIFIED" );
				break;
			}
			else {
				sleep( $loop_time );
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

		if($this->successPolling){
			// echo $this->Out( "NONMODIFIED" );
			// $this->State->users = [$this->UID=>['on'=>false]];
		}
	}
} // Chat