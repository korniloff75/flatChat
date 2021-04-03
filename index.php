<?php
ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$START_PROFILE = microtime(true);

require_once __DIR__.'/core/define.php';


$Chat= new Chat;

// tolog('$Chat',null,[$Chat]);

// *Выводим в шаблон
$Chat->setTemplate();

// var_dump($template);

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


			<div class="item-block">
				<p class="right">Вы можете ввести <strong id="maxLen"><?=Chat::MAXUSERTEXTLEN?></strong> символов</p>

				<?php //var_dump($Chat->ban, empty($Chat->ban), isset($Chat->ban), (!isset($Chat->ban) || !$Chat->ban));
				if(!$Chat->ban):?>

				<form action="./core/bot.php" method="post" id="sendForm">
					<div id="sendDialog" class="block2">
						<input type="hidden" name="name" value="<?=$Chat->name?>" maxLength="<?=Chat::MAXUSERNAMELEN?>" placeholder="Имя" required />
						<input type="hidden" name="UIN" value="<?=$Chat->UIN?>" placeholder="Это поле пока не работает" required />
						<input type="hidden" name="appeals" value="" placeholder="Обращения" />
						<textarea name="text" placeholder="Текст" maxLength="<?=Chat::MAXUSERTEXTLEN?>" autofocus></textarea>
						<div class="submit">
							<label class="input__file button" for="attach" title="jpg,png,gif">Добавить изображение
								<input type="file" name="attach[]" id="attach" multiple hidden>
							</label>
							<input type="submit" value="Отправить" class="button" title="ctrl + enter" />
							<input type="reset" value="Сброс" class="button" title="Очистить"/>
						</div>
					</div>
					<div class="attaches" hidden>
						<h4 class="left">Прикрепления:</h4>
						<p><button class="clear button">Очистить</button></p>
						<div><!-- attach items --></div>
					</div>
				</form>

				<?php else:?>
				<h2>You was BANNED!</h2>
				<div>
					<p>Ваша учётная запись была заблокирована администрацией чата.</p>
					<p>Если вы считаете это досадным недоразумением, свяжитесь с администрацией через <?=Chat::ADM['feedback']?> для выяснения причин. Постарайтесь быть вежливым и аккуратным в формулировках, чтобы не оказаться забаненым и там.</p>
					<p></p>
				</div>
				<?php endif?>

			</div><!-- .item-block -->

		</main><!-- #wrapper -->

		<?=$Chat->getTemplateModule('header')?>

		<?=$Chat->getTemplateModule('footer')?>

		<?= Chat::profile()?>

	</body>
</html>