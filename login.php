<?php
require_once 'define.php';

// *try log in
if(
	!empty($_POST)
	&& !empty($pwd= filter_var($_POST['pwd'], FILTER_SANITIZE_STRING))
){
	$base= new DbJSON(\DR.'/assets/adm.json');
	if(!$base->count()){
		$base->set(['pwd'=>hash('sha256',$pwd)]);
	}
	elseif(hash('sha256',$pwd) === $base->pwd){
		// session_start();
		$_SESSION['adm']= true;
		echo "<h2>You are admin!</h2>
		<a href='/'><button class='button'>to chat</button></a><pre>";
		// var_dump($_SERVER);

		// header('Location: '.$_SERVER['HTTP_ORIGIN']);
		die;
	}
	else{
		die("<p>Ты ошибся. Перезагрузи страницу и попробуй ещё раз.</p>");
	}
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
	<form action="/bot.php" method="post">
		<input type="text" name="pwd">
		<button>GO</button>
	</form>

	<script>
		var f= document.querySelector('form');

		f.onsubmit= e=>{
			e.preventDefault();
			var fd= new FormData(f);

			post('', fd, render);
		}

		function post(url, reqParams, callback) {
			var XMLo,
				_w= window,
				response;

			if (_w.XMLHttpRequest) {
				try { XMLo = new XMLHttpRequest(); }
				catch (e) { XMLo = null; }
			} else if (_w.ActiveXObject) {
				try { XMLo = new ActiveXObject("Msxml2.XMLHTTP"); }
				catch (e) {
					try { XMLo = new ActiveXObject("Microsoft.XMLHTTP"); }
					catch (e) { XMLo = null; }
				}
			}

			if (XMLo == null) return null;

			XMLo.open("POST", url, true);

			if(reqParams instanceof FormData){
				// XMLo.setRequestHeader("Content-Type", "multipart/form-data");
				// *Не меняем
			}
			else{
				XMLo.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

				if (reqParams) {
					var prm = "";
					for (var i in reqParams) prm += "&" + i + "=" + encodeURIComponent(reqParams[i]);
					reqParams = prm;
				}
				else {
					reqParams = " ";
				}
			}

			XMLo.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			XMLo.setRequestHeader("Accept", "*/*");

			XMLo.onreadystatechange = function () {
				if (XMLo.readyState !== 4) return;

				// if (XMLo.status == 200 || XMLo.status == 0) {
				if (XMLo.status === 200) {
					// console.log({XMLo});

					try {
						response= JSON.parse(XMLo.responseText)
					} catch (err) {
						response= XMLo.responseText
					}

					// console.log({response});
					callback&&callback(true, XMLo.status, response, (XMLo.responseXML ? XMLo.responseXML.documentElement : null));
				}
				else if(XMLo.status !== 0){
					callback&&callback(false, XMLo.status, response);
				}

				XMLo = null;
			};

			XMLo.send(reqParams);

			return (XMLo !== null);
		}

		function render (success,status,response) {
			if(success){
				document.body.innerHTML= response;
			}
		}

	</script>
</body>
</html>