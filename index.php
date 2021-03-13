<?php
/*
.......................................................
.                     Мини-Чат                        .
.                версия от 02.07.2020                 .
.												UTF-8                         .
.                                                     .
.                  (C) By Protocoder                  .
.           https://protocoder.ru/minichat            .
.                                                     .
. распространяется по лицензии Creative Commons BY-NC .
.   http://creativecommons.org/licenses/by-nc/3.0/    .
.......................................................
*/


/* ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

require_once __DIR__.'/define.php';


tolog(__LINE__,null,['$_REQUEST'=>$_REQUEST, '$_REQUEST["mode"]'=>@$_REQUEST["mode"],'$_FILES'=>$_FILES]);
// tolog(__LINE__,null,['$_FILES'=>$_FILES]);


$Chat= new Chat;

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

		<link rel="icon" href="./chat.svg" type="image/x-icon">

		<link rel="stylesheet" href="./style.css">

	</head>

	<body>
		<main id="wrapper">

			<div id="msgsDialog" class="block">
				<div id="msgsContent">
					<?=$Chat->getHTML()?>
					<?#die?>
				</div>

				<div class="right" style="position:relative;">
					<svg class="svg-toRead" viewBox="0 0 512.002 512.002" xmlns="http://www.w3.org/2000/svg"><circle cx="462.796" cy="159.195" r="15"/><g><path d="m287.451 205.502c-13.924-46.811-46.866-86.175-91.486-107.922l44.167-44.167c5.858-5.857 5.858-15.355 0-21.213-5.857-5.857-15.355-5.857-21.213 0l-59.184 59.184c-.698.582-1.346 1.226-1.934 1.934l-153.407 153.409c-5.858 5.857-5.858 15.355 0 21.213l151.93 151.93c.232.248.471.487.718.718l59.213 59.213c2.929 2.929 6.768 4.394 10.606 4.394s7.678-1.465 10.606-4.394c5.858-5.857 5.858-15.355 0-21.213l-43.391-43.391c25.968-12.275 48.479-30.712 65.944-54.181 22.737-30.558 34.756-66.896 34.756-105.086 0-17.354-2.529-34.242-7.278-50.263-.015-.055-.031-.11-.047-.165zm-51.499 137.604c-.463.623-.953 1.225-1.427 1.84-14.439-7.084-26.843-17.629-36.245-30.921-11.647-16.466-17.804-35.979-17.804-56.43 0-38.206 22.125-72.469 55.99-88.252 6.842 9.323 12.579 19.454 17.057 30.195-22.827 9.465-38.469 31.915-38.469 58.057 0 25.424 15.151 47.636 37.294 57.473-4.361 9.835-9.839 19.228-16.396 28.038zm9.103-85.51c0-12.547 6.856-23.462 17.163-28.995 1.68 8.89 2.559 18.028 2.559 27.329 0 10.292-1.058 20.42-3.124 30.275-9.977-5.677-16.598-16.438-16.598-28.609zm-208.842-.263 137.037-137.037c15.757 6.336 29.983 15.228 42.269 26.097-39.637 22.157-65.042 64.425-65.042 111.203 0 26.691 8.062 52.195 23.312 73.755 10.491 14.831 23.84 27.047 39.258 36.085-12.468 10.549-26.659 19.012-41.837 24.895z"/><path d="m328.787 169.033c2.679 0 5.392-.718 7.842-2.224l168.228-103.428c7.057-4.339 9.261-13.577 4.922-20.635-4.339-7.056-13.575-9.261-20.635-4.922l-168.228 103.429c-7.057 4.339-9.261 13.577-4.922 20.635 2.833 4.607 7.753 7.145 12.793 7.145z"/><path d="m504.857 449.047-168.228-103.429c-7.059-4.338-16.296-2.134-20.635 4.922-4.339 7.058-2.135 16.296 4.922 20.635l168.228 103.429c2.45 1.506 5.163 2.224 7.842 2.224 5.039 0 9.96-2.539 12.793-7.146 4.338-7.058 2.134-16.296-4.922-20.635z"/><path d="m468.328 384.3c8.284 0 15-6.716 15-15v-26.053h13.672c8.284 0 15-6.716 15-15s-6.716-15-15-15h-13.672v-26.053c0-8.284-6.716-15-15-15s-15 6.716-15 15v26.053h-13.671c-8.284 0-15 6.716-15 15s6.716 15 15 15h13.671v26.053c0 8.284 6.716 15 15 15z"/><path d="m388.223 272.157c8.284 0 15-6.716 15-15v-17.055h7.387c8.284 0 15-6.716 15-15s-6.716-15-15-15h-7.387v-17.055c0-8.284-6.716-15-15-15s-15 6.716-15 15v17.055h-7.388c-8.284 0-15 6.716-15 15s6.716 15 15 15h7.388v17.055c0 8.284 6.716 15 15 15z"/><circle cx="350.31" cy="305.408" r="15"/></g></svg>

					<svg class="svg-toForm" viewBox="0 0 465.882 465.882" xmlns="http://www.w3.org/2000/svg" title="Написать"><path d="m465.882 0-465.882 262.059 148.887 55.143 229.643-215.29-174.674 235.65.142.053-.174-.053v128.321l83.495-97.41 105.77 39.175z"></path></svg>
				</div>


			</div>

			<div class="item-block">
				<p class="right">Вы можете ввести <strong id="maxLen"><?=\MAXUSERTEXTLEN?></strong> символов</p>

				<form action="/bot.php" method="post" id="sendForm">
					<div id="sendDialog" class="block2">
						<input type="text" name="name" value="<?=$Chat->name?>" maxLength="<?=\MAXUSERNAMELEN?>" placeholder="Имя" required />
						<textarea name="text" placeholder="Текст" maxLength="<?=\MAXUSERTEXTLEN?>" required autofocus></textarea>
						<div class="submit">
							<label class="input__file button" for="attach">Добавить файл
								<input type="file" name="attach[]" id="attach" multiple hidden>
							</label>
							<input type="submit" value="отправить" class="button" title="ctrl + enter" id="submit"/>
						</div>
					</div>
					<div class="attaches">
						<h3>Прикрепления:</h3>
						<p><button class="clear button">Очистить</button></p>
						<div><!-- attach items --></div>
					</div>
				</form>

			</div><!-- .item-block -->

			<h3>Участники за последние <?=State::EXPIRES/3600?>ч.</h3>
			<div class="users box">

			</div>

			<h3>Архивные посты</h3>
			<div class="arhive box">
				<?=$Chat->getArhive()?>
			</div>

		</main><!-- #wrapper -->

		<header class="box">
			<h1><?= HEADER ?></h1>

			<div class="checkbox">

				<label class="options first"><input id="autoScroll" type="checkbox" checked="checked"> прокручивать вниз</label>
				<label class="options"><input id="playSound" type="checkbox" checked="checked"> звук</label>
				<label class="options"><input id="autoHeight" type="checkbox" checked="checked"> авторазмер ввода</label>
			</div>

			<h3 class="auth">

			<?php
			// var_dump($_SESSION);
			if(is_adm()){
				echo "(=Admin=) <button class='logout' title='Logout'>Logout</button>";
			}
			else{
				echo "<a href='./login.php'><button class='button' title='Login'>Login</button></a>";
			}
			?>
			</h3>
		</header>

		<footer class="right" style="font-size:.7em;background: #000; padding-top:1em;">
			<a href="//github.com/korniloff75/flatChat" target="_blank" title="Репозиторий">
				KorniloFF &copy;
				<svg style="background: #fff;border: none;border-radius: 100%;" viewBox="0 0 16 16" version="1.1" width="50" aria-hidden="true"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>

			</a>
		</footer>


		<script type="text/javascript">
			const REFRESHTIME= <?=\REFRESHTIME?>;
			let Chat= <?=$Chat->getJsonData()?>,
				lastMod= <?=$Chat->lastMod?>,
				State= <?=$Chat->Out()['state'] ?? '[]'?>;
		</script>

		<script src="./script.js" type="module"></script>

		<script src="./assets/helpers.js" type="module"></script>
		<script src="./assets/BB.js" type="module"></script>
		<script src="./assets/State.js" type="module"></script>
		<script src="./assets/Images.js" type="module"></script>
		<?php //todo ?>
		<script src="./assets/modal/modal.js" type="module"></script>
		<?php if(is_adm()): ?>
		<script src="./assets/Admin.js" type="module" defer></script>
		<?php endif?>

	</body>
</html>