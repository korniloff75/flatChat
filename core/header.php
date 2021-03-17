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
	echo "<a href='./core/login.php'><button class='button' title='Login'>Login</button></a>";
}
?>
</h3>

<style>
	#settings {
		clear:both;
	}
</style>

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