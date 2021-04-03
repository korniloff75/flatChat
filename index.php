<?php
/* ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

$START_PROFILE = microtime(true);

// *Автозагрузка, Логгер и основные константы
require_once __DIR__.'/core/define.php';

// *Чат
$Chat= new Chat;

// tolog('$Chat',null,[$Chat]);


?>
<!DOCTYPE html>
<html lang="ru">
	<head>
		<title><?=HEADER?></title>
		<meta charset="utf-8" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<!-- <meta name="robots" content="noindex, nofollow"> -->
		<meta name="robots" content="index, follow">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

		<?=$Chat->getTemplateModule('head')?>

	</head>

	<body>
		<main id="wrapper">

			<div id="msgsDialog">
				<div id="msgsContent">
					<?=$Chat->getHTMLContent()?>
					<?#die?>
				</div>

				<?=$Chat->getTemplateModule('scrollNav')?>

			</div>

			<?=$Chat->getTemplateModule('sendForm')?>

		</main><!-- #wrapper -->

		<?=$Chat->getTemplateModule('header')?>

		<?=$Chat->getTemplateModule('footer')?>

		<?= Chat::profile()?>

	</body>
</html>