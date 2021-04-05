<section class="bottom">
	<div>
		<h3>Обращения</h3>
		<div class="appeals box">
		</div>
	</div>

	<div>
		<h3>Участники за последние <?=State::EXPIRES/3600?>ч.</h3>
		<div class="users box">
		</div>
	</div>

	<div>
		<h3>Архивные посты</h3>
		<div class="arhive box">
			<?=$this->getArhives()?>
		</div>
	</div>

</section>



<footer class="right" style="font-size:.7em;background: #000; padding-top:1em;">
	<a href="//github.com/korniloff75/flatChat" target="_blank" title="Репозиторий">
		KorniloFF &copy;
		<svg style="background: #fff;border: none;border-radius: 100%;" viewBox="0 0 16 16" version="1.1" width="50" aria-hidden="true"><path fill-rule="evenodd" d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.013 8.013 0 0016 8c0-4.42-3.58-8-8-8z"></path></svg>
	</a>
</footer>

<script type="text/javascript">
	const REFRESHTIME= <?=\REFRESHTIME?>;
	let
		LastMod= <?=$this->lastMod?>,
		Out= <?=$this->Out()?>,
		Chat= Out.state.users[Out.UID] || {},
		Online= Out.online;
</script>

<!-- <?=realpath('./')?> -->

<script src="./script.js" type="module" defer></script>

<script src="./assets/helpers.js" type="module" async></script>
<script src="./assets/State.js" type="module" async></script>
<script src="./assets/BB.js" type="module" async></script>
<script src="./assets/Images/Images.js" type="module" async></script>
<script src="./assets/Template/tpl.mod.js" type="module" async></script>

<script src="./assets/modal/modal.js" type="module" async></script>
<?php if(is_adm()): ?>
<script src="./assets/Admin.js" type="module" defer></script>
<?php endif?>