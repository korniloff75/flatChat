<?php
/* ini_set('error_reporting', E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1); */

require_once __DIR__.'/core/define.php';


tolog(__LINE__,null,['$_REQUEST'=>$_REQUEST, '$_REQUEST["mode"]'=>@$_REQUEST["mode"],'$_FILES'=>$_FILES]);
// tolog(__LINE__,null,['$_FILES'=>$_FILES]);


$Chat= new Chat;

// tolog('$Chat',null,[$Chat]);

// *Выводим в шаблон
$template= $Chat->setTemplate();

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

		<?=$template['head']?>

	</head>

	<body>
		<main id="wrapper">

			<div id="msgsDialog" class="block">
				<div id="msgsContent">
					<?=$Chat->getHTMLContent()?>
					<?#die?>
				</div>

				<div class="right" style="position:relative;">
					<svg class="svg-toRead" viewBox="0 0 512.002 512.002" xmlns="http://www.w3.org/2000/svg"><circle cx="462.796" cy="159.195" r="15"/><g><path d="m287.451 205.502c-13.924-46.811-46.866-86.175-91.486-107.922l44.167-44.167c5.858-5.857 5.858-15.355 0-21.213-5.857-5.857-15.355-5.857-21.213 0l-59.184 59.184c-.698.582-1.346 1.226-1.934 1.934l-153.407 153.409c-5.858 5.857-5.858 15.355 0 21.213l151.93 151.93c.232.248.471.487.718.718l59.213 59.213c2.929 2.929 6.768 4.394 10.606 4.394s7.678-1.465 10.606-4.394c5.858-5.857 5.858-15.355 0-21.213l-43.391-43.391c25.968-12.275 48.479-30.712 65.944-54.181 22.737-30.558 34.756-66.896 34.756-105.086 0-17.354-2.529-34.242-7.278-50.263-.015-.055-.031-.11-.047-.165zm-51.499 137.604c-.463.623-.953 1.225-1.427 1.84-14.439-7.084-26.843-17.629-36.245-30.921-11.647-16.466-17.804-35.979-17.804-56.43 0-38.206 22.125-72.469 55.99-88.252 6.842 9.323 12.579 19.454 17.057 30.195-22.827 9.465-38.469 31.915-38.469 58.057 0 25.424 15.151 47.636 37.294 57.473-4.361 9.835-9.839 19.228-16.396 28.038zm9.103-85.51c0-12.547 6.856-23.462 17.163-28.995 1.68 8.89 2.559 18.028 2.559 27.329 0 10.292-1.058 20.42-3.124 30.275-9.977-5.677-16.598-16.438-16.598-28.609zm-208.842-.263 137.037-137.037c15.757 6.336 29.983 15.228 42.269 26.097-39.637 22.157-65.042 64.425-65.042 111.203 0 26.691 8.062 52.195 23.312 73.755 10.491 14.831 23.84 27.047 39.258 36.085-12.468 10.549-26.659 19.012-41.837 24.895z"/><path d="m328.787 169.033c2.679 0 5.392-.718 7.842-2.224l168.228-103.428c7.057-4.339 9.261-13.577 4.922-20.635-4.339-7.056-13.575-9.261-20.635-4.922l-168.228 103.429c-7.057 4.339-9.261 13.577-4.922 20.635 2.833 4.607 7.753 7.145 12.793 7.145z"/><path d="m504.857 449.047-168.228-103.429c-7.059-4.338-16.296-2.134-20.635 4.922-4.339 7.058-2.135 16.296 4.922 20.635l168.228 103.429c2.45 1.506 5.163 2.224 7.842 2.224 5.039 0 9.96-2.539 12.793-7.146 4.338-7.058 2.134-16.296-4.922-20.635z"/><path d="m468.328 384.3c8.284 0 15-6.716 15-15v-26.053h13.672c8.284 0 15-6.716 15-15s-6.716-15-15-15h-13.672v-26.053c0-8.284-6.716-15-15-15s-15 6.716-15 15v26.053h-13.671c-8.284 0-15 6.716-15 15s6.716 15 15 15h13.671v26.053c0 8.284 6.716 15 15 15z"/><path d="m388.223 272.157c8.284 0 15-6.716 15-15v-17.055h7.387c8.284 0 15-6.716 15-15s-6.716-15-15-15h-7.387v-17.055c0-8.284-6.716-15-15-15s-15 6.716-15 15v17.055h-7.388c-8.284 0-15 6.716-15 15s6.716 15 15 15h7.388v17.055c0 8.284 6.716 15 15 15z"/><circle cx="350.31" cy="305.408" r="15"/></g></svg>

					<!-- <div class="svg-toBottom">⬇</div> -->
					<svg class="svg-toBottom" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
	 viewBox="0 0 51.619 51.619">
						<path d="M26.31,0c-1.104,0-2,0.896-2,2v32.844l-8.523-8.771c-0.781-0.781-1.922-0.781-2.704,0
							c-0.781,0.781-0.718,2.048,0.063,2.828l12.924,12.89c0.375,0.375,0.9,0.586,1.429,0.586c0.531,0,1.048-0.211,1.423-0.586
							l12.894-12.893c0.781-0.781,0.783-2.048,0.002-2.828c-0.781-0.781-2.297-0.781-3.078,0L28.31,36.254V2
							C28.31,0.895,27.414,0,26.31,0z"/>
						<path d="M48.31,47.326V41c0-1.104-0.896-2-2-2s-2,0.896-2,2v6.326c0,0.319,0.461,0.293,0.424,0.293H8.548
							c-0.342,0-1.239-0.027-1.239-0.293V41c0-1.104-0.896-2-2-2s-2,0.896-2,2v6.326c0,2.49,2.714,4.293,5.239,4.293h36.349
							C46.845,51.619,48.31,50.555,48.31,47.326z"/>
				</svg>

					<svg class="svg-toForm" viewBox="0 0 465.882 465.882" xmlns="http://www.w3.org/2000/svg" title="Написать"><path d="m465.882 0-465.882 262.059 148.887 55.143 229.643-215.29-174.674 235.65.142.053-.174-.053v128.321l83.495-97.41 105.77 39.175z"></path></svg>
				</div>


			</div>

			<div class="item-block">
				<p class="right">Вы можете ввести <strong id="maxLen"><?=Chat::MAXUSERTEXTLEN?></strong> символов</p>

				<form action="./core/bot.php" method="post" id="sendForm">
					<div id="sendDialog" class="block2">
						<input type="hidden" name="name" value="<?=$Chat->name?>" maxLength="<?=Chat::MAXUSERNAMELEN?>" placeholder="Имя" required />
						<textarea name="text" placeholder="Текст" maxLength="<?=Chat::MAXUSERTEXTLEN?>" required autofocus></textarea>
						<div class="submit">
							<label class="input__file button" for="attach" title="jpg,png,gif">Добавить изображение
								<input type="file" name="attach[]" id="attach" multiple hidden>
							</label>
							<input type="submit" value="отправить" class="button" title="ctrl + enter" id="submit"/>
						</div>
					</div>
					<div class="attaches" hidden>
						<h3>Прикрепления:</h3>
						<p><button class="clear button">Очистить</button></p>
						<div><!-- attach items --></div>
					</div>
				</form>

			</div><!-- .item-block -->

		</main><!-- #wrapper -->

		<?=$template['header']?>

		<?=$template['footer']?>

	</body>
</html>