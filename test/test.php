<?php
require_once $_SERVER['DOCUMENT_ROOT'].'/index.php';

?>
<h2>Модальное окно</h2>
<div id="test-modal">
<script type="module">
	import {modal} from '../assets/modal/modal.js';

	modal('Вы уверены?').then(ok=>{
		console.log({ok});
	},fail=>{
		console.log({fail});
	});
</script>
</div>