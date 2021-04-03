<div class="item-block">
	<p class="right">Вы можете ввести <strong id="maxLen"><?=Chat::MAXUSERTEXTLEN?></strong> символов</p>

	<?php //var_dump($this->ban, empty($this->ban), isset($this->ban), (!isset($this->ban) || !$this->ban));
	if(!$this->ban):?>

	<form action="./core/bot.php" method="post" id="sendForm">
		<div id="sendDialog" class="block2">
			<input type="hidden" name="name" value="<?=$this->name?>" maxLength="<?=Chat::MAXUSERNAMELEN?>" placeholder="Имя" required />
			<input type="hidden" name="UIN" value="<?=$this->UIN?>" placeholder="Это поле пока не работает" required />
			<input type="hidden" name="appeals" value="" placeholder="Обращения" />
			<textarea name="text" placeholder="Текст" maxLength="<?=Chat::MAXUSERTEXTLEN?>" autofocus></textarea>
			<div class="submit">
				<label class="input__file button" for="attach" title="jpg,png,gif">Добавить изображение
					<input type="file" name="attach[]" id="attach" multiple hidden>
				</label>
				<input type="submit" value="Отправить" class="button" title="ctrl + enter" />
				<input type="reset" value="Сброс" class="button" title="Очистить"/>
			</div>
		</div>
		<div class="attaches" hidden>
			<h4 class="left">Прикрепления:</h4>
			<p><button class="clear button">Очистить</button></p>
			<div><!-- attach items --></div>
		</div>
	</form>

	<?php else:?>
	<h2>You was BANNED!</h2>
	<div>
		<p>Ваша учётная запись была заблокирована администрацией чата.</p>
		<p>Если вы считаете это досадным недоразумением, свяжитесь с администрацией через <?=Chat::ADM['feedback']?> для выяснения причин. Постарайтесь быть вежливым и аккуратным в формулировках, чтобы не оказаться забаненым и там.</p>
		<p></p>
	</div>
	<?php endif?>

</div><!-- .item-block -->