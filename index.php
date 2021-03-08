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


/* ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

mb_internal_encoding( "UTF-8" );
mb_http_output( "UTF-8" );
mb_http_input( "UTF-8" );
mb_language( "uni" );
mb_regex_encoding( "UTF-8" );
ob_start( "mb_output_handler" );

date_default_timezone_set ('Europe/Moscow');

// setlocale( LC_ALL, array( 'ru_RU.UTF-8', 'ru_RU.UTF8', 'ru_RU.65001' ), array( 'rus_RUS.UTF-8', 'rus_RUS.UTF8', 'rus_RUS.65001' ), array( 'Russian_Russia.UTF-8', 'Russian_Russia.UTF8', 'Russian_Russia.65001' ) );
// setlocale( LC_NUMERIC, 'C' ); //in float number deilmiter = "."

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
		$log = $log ?? new Logger('my.log', DR);
		call_user_func_array([$log,'add'], func_get_args());
	}
}


tolog(__LINE__,null,['$_POST'=>$_POST, '$_POST["mode"]'=>@$_POST["mode"],'$_FILES'=>$_FILES]);
// tolog(__LINE__,null,['$_FILES'=>$_FILES]);


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
		<main id="wrapper">
			<header class="box">
				<h1><?= HEADER ?></h1>

				<div class="checkbox">
					<label class="options first"><input id="autoScroll" type="checkbox" checked="checked"> прокручивать вниз</label>
					<label class="options"><input id="playSound" type="checkbox" checked="checked"> звук</label>
					<label class="options"><input id="autoHeight" type="checkbox" checked="checked"> авторазмер ввода</label>
				</div>
			</header>

			<div id="msgsDialog" class="block">
				<div id="msgsContent">
					<?=$Chat->getHTML()?>
					<?#die?>
				</div>
			</div>

			<div class="item-block">
				<p class="right">Вы можете ввести <strong id="maxLen"><?=\MAXUSERTEXTLEN?></strong> символов</p>

				<form action="" method="post" id="sendForm">
					<div id="sendDialog" class="block2">
						<input type="text" name="name" value="<?=$Chat->name?>" maxLength="<?=\MAXUSERNAMELEN?>" placeholder="Имя" required />
						<textarea name="text" placeholder="Текст" maxLength="<?=\MAXUSERTEXTLEN?>" required></textarea>
						<div class="submit">
							<label class="input__file button" for="attach">Добавить файл
								<input type="file" name="attach[]" id="attach" multiple hidden>
							</label>
							<input type="submit" value="отправить" class="button" title="ctrl + enter" id="submit"/>
						</div>
					</div>
				</form>
			</div><!-- .item-block -->

			<h3>Участники за последние <?=State::EXPIRES/3600?>ч.</h3>
			<div class="users box">

			</div>

		</main><!-- #wrapper -->

		<footer class="right" style="font-size:.7em;background: #000; padding-top:1em;">
			<a href="//github.com/korniloff75/flatChat" target="_blank" title="Репозиторий">
				KorniloFF &copy;
				<svg style="background: #fff;border: none;border-radius: 100%;" height="32" viewBox="0 0 16 16" version="1.1" width="32" aria-hidden="true"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
			</a>
		</footer>


		<script type="text/javascript">
			const REFRESHTIME= <?=\REFRESHTIME?>;
			let Chat= <?=$Chat->getJsonData()?>,
				lastMod= <?=$Chat->lastMod?>,
				State= <?=$Chat->Out()['state'] ?? '[]'?>;
		</script>

		<script src="script.js"></script>

		<script src="./assets/BB.js" type="module"></script>
		<script src="./assets/State.js" type="module"></script>
		<script src="./assets/Images.js" type="module"></script>

	</body>
</html>

<?php
die;

$link= mysqli_connect('localhost','root','');
if($link === false){
	die;
}

mysqli_set_charset($link, "utf8");

if(!$db= mysqli_query($link, "SHOW DATABASES LIKE myDB")){
	$link = mysqli_query($link, "CREATE DATABASE myDB");
};

tolog('$link', null, $db);