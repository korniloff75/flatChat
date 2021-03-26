<?php
require_once 'define.php';

// *try log in
if(
	!empty($pwd= filter_var(@$_POST['pwd'], FILTER_SANITIZE_STRING))
	&& !empty($_POST)
	|| is_adm()
){
	$user= 'nobody';

	$base= new DbJSON(\DR.'/assets/adm.json');
	if(!$base->count()){
		$base->set(['Adm'=>hash('sha256',$pwd)]);
	}
	elseif(
		hash('sha256',$pwd) === $base->Adm
		|| is_adm()
	){
		// session_start();
		$_SESSION['adm']= true;
		$user= 'Admin';

		// var_dump($_SERVER);
	}
	else{
		$_SESSION['user']= ['name'=>$pwd];
		$user= $pwd;
	}

	echo "<h2>You are $user!</h2>
	<a href='../'><button class='button'>to chat</button></a><pre>";

	die;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Login</title>
	<style>
	form{
		text-align:center;
	}
	</style>
</head>
<body>
	<form action="./bot.php" method="post">
		<input type="text" name="pwd" autofocus>
		<button>GO</button>
	</form>

	<script>
		var f= document.querySelector('form');

		f.onsubmit= e=>{
			e.preventDefault();
			var fd= new FormData(f);

			fetch('', {method:'post', body:fd}).then(r=>{
				console.log(r,r.text);
				if (r.ok) {
					return r.text();
				} else {
					alert("Ошибка HTTP: " + response.status);
				}
			}).then(render)
			.catch(function(err) {
				console.log({err});
			});
		}

		function render (txt) {
			document.body.innerHTML= txt;
		}

	</script>
</body>
</html>