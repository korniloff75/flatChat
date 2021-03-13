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

	<script type="module">
		import {on,speak} from './assets/helpers.js';

		on(document.getElementById("msgsContent"),'click',e=>{
			var t = e.target,
				s= t.closest('.msg'),
				vb= t.closest('.voice');

			if(s && vb) {
				var post= s.querySelector('.post').cloneNode(true);
				[].forEach.call(post.querySelectorAll('.cite_disp'), i=>i.remove());
				speak(post.textContent.replace(/\p{S}/iug,''));
				return;
			}
		});
	</script>

	<script src="./assets/helpers.js" type="module"></script>
</body>
</html>

