<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/index.php';

?>
<h2>Модальное окно</h2>
<div id="test-modal" class="item-block">

<button id="modal-btn">Modal</button>
<button id="modal-btn1">Modal1</button>
<button id="modal-btn2">Modal2</button>

<svg id="Capa_1" viewBox="0 0 465.882 465.882" xmlns="http://www.w3.org/2000/svg" style="width: 3em; fill:#abc;"><path d="m465.882 0-465.882 262.059 148.887 55.143 229.643-215.29-174.674 235.65.142.053-.174-.053v128.321l83.495-97.41 105.77 39.175z"></path></svg>

<script type="module">
	import {modal} from '../assets/modal/modal.js';

	document.querySelector('#modal-btn').onclick= e=> modal('Вы уверены?')
	.then(ok=>{
		console.log({ok});
	},fail=>{
		console.log({fail});
	});

	document.querySelector('#modal-btn1').onclick= e=> modal('Какой-то <b>текст</b> даже с <u>разметкой</u>.')
	.then(ok=>{
		console.log({ok});
	},fail=>{
		console.log({fail});
	});

	document.querySelector('#modal-btn2').onclick= e=> modal('Ещё <b>текст</b> проверяем с <u>разметкой</u>.');

</script>
</div>