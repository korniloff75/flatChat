<div class="checkbox">
	<label class="options first"><input id="playSound" type="checkbox" checked="checked"> звук</label>
	<label class="options"><input id="autoHeight" type="checkbox" checked="checked"> авторазмер ввода</label>
</div>

<h4 class="auth">

<?php
// var_dump($_SESSION);
if(is_adm()){
	echo "(=Admin=) <button class='logout' title='Logout'>Logout</button>";
}
else{
	echo "<a href='./core/login.php'><button class='button' title='Login'>Login</button></a>";
}
?>
</h4><!-- .auth -->


<div id="settings">
	<strong>Шаблон</strong> -
	<select class="templates">
		<?php
		// tolog('$Chat->templatePath= ' . $Chat->templatePath, null, [$Chat]);

		foreach(new FilesystemIterator (dirname($Chat->templatePath)) as $tfi){
			if(!$tfi->isDir()) continue;

			// tolog(__FILE__,null,[$tfi, Chat::fixSlashes($tfi->getPathname())]);
			echo "<option data-dir='{$tfi->isDir()}' value='". Chat::fixSlashes($tfi->getPathname()) ."'>{$tfi->getFilename()}</option>";
		}
		?>
	</select>
</div>

<!-- Выплывающая панель инструментов при выделении чекбоксов постов -->
<div id='selectedPanel'>
	<div class='voice button' title='Озвучить выбранные'>📢🎧</div>
	<div class='reset button' title='Сброс'>⍻</div>
	<?php if(is_adm()):?>
	<div class='del button' title='Удалить выбранное'>❌</div>
	<?php endif?>
</div>