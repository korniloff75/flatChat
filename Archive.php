<?php
mb_internal_encoding( "UTF-8" );
mb_http_output( "UTF-8" );
mb_http_input( "UTF-8" );
mb_language( "uni" );
mb_regex_encoding( "UTF-8" );
ob_start( "mb_output_handler" );

date_default_timezone_set ('Europe/Moscow');

define( "DBFILE", realpath( str_replace( '\\', '/', __DIR__ ) ) . "/chat.db" ); //Путь и имя файла с чатом
define( "REFRESHTIME", 10 * 1000 ); //Клиентская задержка опроса сервера
define( "HEADER", "Chat" ); //Заголовок

define( "COOKIEPATH", "/" );

define( "CHATTRIM", 50 * 1024 ); //Максимальная длина пересылаемого куска чата, 0 - без ограничений
// define( "CHATTRIM", 1000); //Максимальная длина пересылаемого куска чата, 0 - без ограничений

define( "MAXUSERNAMELEN", 20 ); //Максимальная длина имени пользователя
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
		$log = $log ?? new Logger('arh.log', DR);
		call_user_func_array([$log,'add'], func_get_args());
	}
}

// *
$pathname= filter_var($_GET['f'], FILTER_SANITIZE_STRING);
// echo $pathname;

$Arh= new Chat($pathname);
$Arh->useStartIndex= false;


?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Archive</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div id="msgsDialog" class="block">
		<div id="msgsContent">
			<?=$Arh->getHTML()?>
			<?#die?>
		</div>
	</div>
</body>
</html>

