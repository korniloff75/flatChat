<?php
mb_internal_encoding( "UTF-8" );
mb_http_output( "UTF-8" );
mb_http_input( "UTF-8" );
mb_language( "uni" );
mb_regex_encoding( "UTF-8" );
ob_start( "mb_output_handler" );

date_default_timezone_set ('Europe/Moscow');

// setlocale( LC_ALL, array( 'ru_RU.UTF-8', 'ru_RU.UTF8', 'ru_RU.65001' ), array( 'rus_RUS.UTF-8', 'rus_RUS.UTF8', 'rus_RUS.65001' ), array( 'Russian_Russia.UTF-8', 'Russian_Russia.UTF8', 'Russian_Russia.65001' ) );
// setlocale( LC_NUMERIC, 'C' ); //in float number deilmiter = "."

// define( "DBFILE", realpath( str_replace( '\\', '/', __DIR__ ) ) . "/chat.db" ); //Путь и имя файла с чатом
define( "REFRESHTIME", 10 * 1000 ); //Клиентская задержка опроса сервера
define( "HEADER", "Chat" ); //Заголовок

define( "COOKIEPATH", "/" );

define( "CHATTRIM", 50 * 1024 ); //Максимальная длина пересылаемого куска чата, 0 - без ограничений
// define( "CHATTRIM", 1000); //Максимальная длина пересылаемого куска чата, 0 - без ограничений


// *Глобальный корень
define( "GDR", $_SERVER['DOCUMENT_ROOT'] );
// *Корень чата
$_SERVER['DOCUMENT_ROOT']= dirname(__DIR__);
define( "DR", $_SERVER['DOCUMENT_ROOT'] );


function _autoloader($class)
{
	include_once \DR."/core/classes/$class.class.php";
}

spl_autoload_register('_autoloader');

// *Логгируем загрузку страницы
function tolog()
{
	global $log;

	// *Отсекаем поллинги
	if(@$_REQUEST["mode"] !== 'list'){
		$log = $log ?? new Logger('my.log', \DR);
		call_user_func_array([$log,'add'], func_get_args());
	}
}

session_start();

// *Admin
function is_adm()
{
	return !empty($_SESSION['adm']);
}