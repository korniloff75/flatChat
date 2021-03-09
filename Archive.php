<?php
require_once 'define.php';

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
	<nav style="text-align:center;"><a href="/"><button class="button">В чат</button></a></nav>
	<div id="msgsDialog" class="block">
		<div id="msgsContent">
			<?=$Arh->getHTML()?>
			<?#die?>
		</div>
	</div>
</body>
</html>

