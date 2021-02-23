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

define( "DR", $_SERVER['DOCUMENT_ROOT'] );

function _autoloader($class)
{
	include_once DR."/classes/$class.class.php";
}

spl_autoload_register('_autoloader');

// *Логгируем загрузку страницы
function tolog()
{
	global $log;

	if(empty($_POST["mode"]) || $_POST["mode"] === 'post'){
		$log = $log ?? new Logger('my.log', DR);
		call_user_func_array([$log,'add'], func_get_args());
	}
}


tolog(__LINE__,null,['$_POST'=>$_POST, '$_POST["mode"]'=>@$_POST["mode"],'$_FILES'=>$_FILES]);
tolog(__LINE__,null,['$_FILES'=>$_FILES]);


$Chat= new Chat;


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
					<?=$Chat->Out()?>
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
					<input type="text" name="name" value="<?=$Chat->name?>" maxlength="<?=\MAXUSERNAMELEN?>" placeholder="Имя" />
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
			let lastMod= <?=$Chat->lastMod?>;
		</script>
		<script src="script.js" defer></script>
	</body>
</html>
