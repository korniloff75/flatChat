<?php
/*
.......................................................
.                     Мини-Чат                        .
.                версия от 02.07.2020                 .
.						UTF-8                         .
.                                                     .
.                  (C) By Protocoder                  .
.           https://protocoder.ru/minichat                   .
.                                                     .
. распространяется по лицензии Creative Commons BY-NC .
.   http://creativecommons.org/licenses/by-nc/3.0/    .
.......................................................
*/

//sleep( 3 ); //Я точно забуду удалить эту отладачную строку...


/* ini_set('error_reporting', -1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

mb_internal_encoding( "UTF-8" );
mb_http_output( "UTF-8" );
mb_http_input( "UTF-8" );
mb_language( "uni" );
mb_regex_encoding( "UTF-8" );
ob_start( "mb_output_handler" );

setlocale( LC_ALL, array( 'ru_RU.UTF-8', 'ru_RU.UTF8', 'ru_RU.65001' ), array( 'rus_RUS.UTF-8', 'rus_RUS.UTF8', 'rus_RUS.65001' ), array( 'Russian_Russia.UTF-8', 'Russian_Russia.UTF8', 'Russian_Russia.65001' ) );
setlocale( LC_NUMERIC, 'C' ); //in float number deilmiter = "."


//Боремся с включенными magic quotes
if ( function_exists( "get_magic_quotes_gpc" ) && get_magic_quotes_gpc() === 1 ) {
	$_COOKIE = array_map( "stripslashes", $_COOKIE );
	$_POST = array_map( "stripslashes", $_POST );
}

define( "DBFILE", realpath( str_replace( '\\', '/', __DIR__ ) ) . "/chat.db" ); //Путь и имя файла с чатом
define( "REFRESHTIME", 7 * 1000 ); //Клиентская задержка опроса сервера
define( "HEADER", "Chat" ); //Заголовок

define( "COOKIEPATH", "/" );

define( "CHATTRIM", 10 * 1024 ); //Максимальная длина пересылаемого куска чата, 0 - без ограничений

define( "MAXUSERNAMELEN", 64 ); //Максимальная длина имени пользователя
define( "MAXUSERTEXTLEN", 1024 ); //Максимальная длина сообщения пользователя

// define( "DR", $_SERVER['DOCUMENT_ROOT'] );


require_once __DIR__ . "/Helpers.trait.php";

class Chat
{
	use Helpers;

	static $log;

	public function __construct()
	{
		global $log;

		spl_autoload_register([__CLASS__,'_autoloader']);

		// *Логгируем загрузку страницы
		if(empty($_POST["mode"]) || $_POST["mode"] === 'post'){
			self::$log= $log = new Logger('my.log', DR);
			$log->add(__METHOD__,null,['$_POST'=>$_POST, '$_POST["mode"]'=>$_POST["mode"]]);
			$log->add(__METHOD__,null,['$_FILES'=>$_FILES]);
		}

	}

	// *
	function _autoloader($class)
	{
		if(file_exists($path= DR."/classes/$class.class.php")){
			require_once $path;
		}
	}

	static function makeURL( $matches ) {
		return '<a href="' . ( mb_strpos( $matches[1], "://" ) === false ? "http://" : "" ) . $matches[1] . '" target="_blank">' . $matches[1] . '</a>';
	}

	static function Out( $status = null, $chat = null ) {
		if ( $status !== null ) {
			if ( !($lastMod = filemtime( DBFILE )) ) $lastMod = 0;
			echo( "{$status}:$lastMod\n" );
		}

		if ( $chat === null ) {

			// *Читаем CHATTRIM байт с конца файла
			if ( CHATTRIM ) {
				$f = fopen( DBFILE, "r" );
				fseek( $f, -CHATTRIM, SEEK_END );
				$chat = fread( $f, CHATTRIM );
				fclose( $f );
				$p =  mb_strpos( $chat, '<div class="msg"' );
				if ( $p !== false ) {
					$chat = mb_substr( $chat, $p );
				}
			}
			// *Читаем весь файл
			else $chat = file_get_contents( DBFILE );
		}

		echo( $chat );
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

$Chat= new Chat;

/* function chatOut( $status = null, $chat = null ) {
	if ( $status !== null ) {
		if ( !($lastMod = filemtime( DBFILE )) ) $lastMod = 0;
		echo( "{$status}:$lastMod\n" );
	}

	if ( $chat === null ) {

		// *Читаем CHATTRIM байт с конца файла
		if ( CHATTRIM ) {
			$f = fopen( DBFILE, "r" );
			fseek( $f, -CHATTRIM, SEEK_END );
			$chat = fread( $f, CHATTRIM );
			fclose( $f );
			$p =  mb_strpos( $chat, '<div class="msg"' );
			if ( $p !== false ) {
				$chat = mb_substr( $chat, $p );
			}
		}
		// *Читаем весь файл
		else $chat = file_get_contents( DBFILE );
	}

	echo( $chat );
} */


$exit = false;

$name = @$_POST["name"] ?? null;

$text = @$_POST["text"] ?? null;

$mode = null;
switch( @$_POST["mode"] ) {
	case "post":
		$mode = "post";
	break;

	case "list":
		$mode = "list";
	break;
}

$cookieName = @$_COOKIE["userName"] ? $_COOKIE["userName"] : null;
if ( $cookieName ) $cookieName = Chat::cleanName( $cookieName );

if ( !$name ) $name = $cookieName;
if ( $text ) $text = Chat::cleanText( $text );


// *New post
if ( $mode == "post" ) {
	if ( !$name || !$text ) {
		header( 'HTTP/1.1 400 Bad Request' );
		exit( 0 );
	}

	if ( !@empty( $_SERVER[ "HTTP_CLIENT_IP" ] ) ) $id = $_SERVER[ "HTTP_CLIENT_IP" ];
	elseif ( !@empty( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) $id = $_SERVER["HTTP_X_FORWARDED_FOR"];
	else $id = @$_SERVER["REMOTE_ADDR"];

	$exit = true;

	if ( $name != $cookieName ) setcookie( "userName", $name, mktime( 0, 0, 0, 12, 31, 3000 ), COOKIEPATH );

	// $text = preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-]+)\x07iu", "makeURL", $text );
	$text = preg_replace_callback( "\x07((?:[a-z]+://(?:www\\.)?)[_.+!*'(),/:@~=?&$%a-z0-9\\-]+)\x07iu", ['Chat',"makeURL"], $text );

	$msg = '<div class="msg"><div class="info"><span class="name">' . $name . '</span><span class="misc"><span class="date">' . date( "d.m.Y H:i:s" ) . '</span> <span class="id">(' . $id . ')</span></span></div>' . $text . '</div>' . "\n\n";

	file_put_contents( DBFILE, $msg, LOCK_EX|FILE_APPEND );

	// *Uploads
	Uploads::$allow = ['jpg','jpeg','png','gif'];
	$upload = new Uploads(null, 'attach');

	$log->add('Uploads',null,['$upload'=>$upload]);

	$mode = "list";
	$lastMod = filemtime( DBFILE );
}

// *Update list
if ( $mode == "list" ) {
	$exit = true;

	$rlm = preg_match( "~^\\d+$~u", @$_POST["lastMod"] ) ? (int)$_POST["lastMod"] : 0;

	if ( !($lastMod = filemtime( DBFILE )) ) $lastMod = 0;

	if ( $rlm == $lastMod ) Chat::Out( "NONMODIFIED", "" );
	else Chat::Out( "OK", null );
}

if ( $exit ) exit( 0 );

$lastMod = filemtime( DBFILE );
if ( $lastMod === false ) $lastMod = 0;

?>
<!DOCTYPE html>
<html lang="ru">
	<head>
		<title><?=HEADER?></title>
		<meta charset="utf-8" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="robots" content="noindex, nofollow">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		<link rel="stylesheet" href="style.css">

	</head>

	<body>
		<div id="wrapper">
			<h1><?= HEADER ?></h1>

			<div id="msgsDialog" class="block">
				<div id="msgsContent">
					<?php Chat::Out(); ?>
				</div>

				<label class="options first"><input id="autoScroll" type="checkbox" checked="checked" /> прокручивать вниз</label>
				<label class="options"><input id="playSound" type="checkbox" checked="checked" /> звук</label>
				<label class="options"><input id="autoHeight" type="checkbox" checked="checked" /> авторазмер ввода</label>

				<div class="ct"></div>
				<div class="cb"></div>
			</div>
			<br />
			<br />
			<form action="" method="post" id="sendForm">
				<div id="sendDialog" class="block2">
					<input type="text" name="name" value="<?php echo( $name ); ?>" maxlength="<?=\MAXUSERNAMELEN?>" placeholder="Имя" />
					<textarea name="text" placeholder="Текст" style="margin-top: 0.5em;" maxlength="<?=\MAXUSERTEXTLEN?>" required></textarea>
					<div>
						<input type="file" name="attach" id="attach">
					</div>
					<input type="submit" value="отправить" class="button" title="ctrl + enter" id="submit"/>
					<div class="ad"></div>
				</div>
			</form>
		</div>


		<script type="text/javascript">
			const REFRESHTIME= <?=\REFRESHTIME?>;
			let lastMod= <?=$lastMod?>;
		</script>
		<script src="script.js" defer></script>
	</body>
</html>
