<?php
// require_once $_SERVER['DOCUMENT_ROOT'].'/index.php';

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


<!--  -->
<link rel="stylesheet" type="text/css" media="screen" href="./main.css" />
<section>
    <h1>Синтез речи</h1>
    <p>
      Введите текст в поле ниже и нажмите кнопку "Play", чтобы прослушать запись. Выбирайте возможные голоса из списка
      ниже
    </p>
    <form>
      <input type="text" class="text">
      <div class="row">
        <div class="values-box">
          <div class="value-box">
            <div>Темп (Rate)</div>
            <div class="value value--rate-value">1</div>
          </div>
          <div class="value-box">
            <div>Диапазон (Pitch)</div>
            <div class="value value--pitch-value">1</div>
          </div>
        </div>
        <div class="ranges-box">
          <input type="range" min="0.5" max="2" value="1" step="0.1" id="rate">
          <input type="range" min="0" max="2" value="1" step="0.1" id="pitch">
        </div>
      </div>

      </div>

      <select>
      </select>

      <button id="play" type="submit">Play</button>
    </form>
  </section>
  <hr>
  <section class="speech-recognition-section">
    <h1>Распознавание речи</h1>
    <p>
      Нажмите на иконку микрофона, и назовите любой цвет радуги, чтобы изменить фон
    </p>
    <div class="audio-record-animation__wrapper">
      <div style="visibility: hidden;" class="audio-record-animation">
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
    <div class="microphone-wrapper">
      <img class="microphone__image" src="microphone.png" width="50" height="50" alt="">
      <p class="recognition-result">Результат: по умолчанию</p>
    </div>
  </section>
  <script src="./main.js"></script>
<!--  -->


<button onclick="speech ()">Слушать</button>
  <button onclick="talk ()">Проговорить</button>
  <button onclick="stop ()">Остановить</button>

  <script>
/* 		'use strict';
  // Создаем распознаватель
	const SpeechRecognition = window.speechRecognition || window.webkitSpeechRecognition;
	const SpeechGrammarList = window.SpeechGrammarList || window.webkitSpeechGrammarList;
	const SpeechRecognitionEvent = window.SpeechRecognitionEvent || window.webkitSpeechRecognitionEvent;

  // Ставим опцию, чтобы распознавание началось ещё до того, как пользователь закончит говорить
	console.log({SpeechRecognition}, window.SpeechRecognition);
  SpeechRecognition.interimResults = true;

  // Какой язык будем распознавать?
  SpeechRecognition.lang = 'ru-Ru';

  // Используем колбек для обработки результатов
  SpeechRecognition.onresult = function (event) {
    var result = event.results[event.resultIndex];
    if (result.isFinal) {
      alert('Вы сказали: ' + result[0].transcript);
    } else {
      console.log('Промежуточный результат: ', result[0].transcript);
    }
  };

  function speech () {
    // Начинаем слушать микрофон и распознавать голос
    SpeechRecognition.start();
  }

  var synth = window.speechSynthesis;
  var utterance = new SpeechSynthesisUtterance('How about we say this now? This is quite a long sentence to say.');

  function talk () {
    synth.speak (utterance);
  }

  function stop () {
    synth.pause();
  } */
  </script>
</div>