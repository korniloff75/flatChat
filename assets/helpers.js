'use strict';

import { modal } from "./modal/modal.js";

const _w= window;

// *noConsole
if(
	/\.ru/i.test(location.host)
	&& !location.search.includes('dev')
){
	_w.console= {
		log: ()=>false,
		info: ()=>false,
	}
}

export const Ajax={
	get: function(){
		Ajax.method= 'get';
		return Ajax.request.apply(null, arguments);
	},
	post: function(){
		Ajax.method= 'post';
		// console.log(['post'].concat(arguments));
		return Ajax.request.apply(null, arguments);
	},

	request: function (url, reqParams, callback) {
		var XMLo;

		reqParams= reqParams || {responseType:'json'};

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

		if (XMLo === null) return null;

		XMLo.open(Ajax.method, url, true);

		if(reqParams.responseType) XMLo.responseType = reqParams.responseType;

		// console.log(reqParams, XMLo.responseType);

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
		XMLo.setRequestHeader("Accept", "*\/*");

		XMLo.onreadystatechange = function () {
			if (XMLo.readyState !== 4) return;
			var resp= XMLo.response;

			// if (XMLo.status == 200 || XMLo.status == 0) {
			if (XMLo.status === 200) {
				console.log({XMLo});

				callback&&callback(true, XMLo.status, resp);
				return Promise.resolve(XMLo);
			}
			else{
				return Promise.reject(XMLo);
			}

			XMLo = null;
		};

		XMLo.send(reqParams);

		return (XMLo !== null);
	}
}


/**
 * Прокрутка к элементу
 * @param {Node|String} targetEl - целевой элемент
 * @param {obj} opts - параметры для scrollIntoView
 * optional @param {Event} e - событие вызова
 */
export function scrollIntoView(targetEl,opts,e){
	e && e.preventDefault();

	opts= Object.assign({
		behavior: 'smooth',
		block: 'start',
		inline: 'center'
	}, (opts || {}));

	targetEl= getNode(targetEl);

	try {
		targetEl.scrollIntoView(opts);
	} catch (err) {
		// location.replace(`#${el.id}`);
		smoothScrollTo(targetEl, 500);
	}
	return false;
}

/**
 * polifill 4 scrollIntoView
 * @param {node|int} targetY
 * @param {int} duration
 * optional @param {node} wrapper
 */
const smoothScrollTo = (function (_w) {

	var timer, start, factor;

	return function (targetY, duration, wrapper) {
		if(targetY instanceof HTMLElement){
			targetY= targetY.offsetTop;
		}
		wrapper= wrapper || _w;
		var offset = wrapper.pageYOffset,
				delta  = targetY - wrapper.pageYOffset; // Y-offset difference
		duration = duration || 1000;              // default 1 sec animation
		start = Date.now();                       // get start time
		factor = 0;

		if( timer ) {
			clearInterval(timer); // stop any running animations
		}

		function step() {
			var y;
			factor = (Date.now() - start) / duration; // get interpolation factor
			if( factor >= 1 ) {
				clearInterval(timer); // stop animation
				factor = 1;           // clip to max 1.0
			}
			y = factor * delta + offset;
			wrapper.scrollBy(0, y - wrapper.pageYOffset);
		}

		timer = setInterval(step, 50);
		return timer;
	};
})(window);


/**
 * *Определяем видимость элемента
 * @param {Node} el
 * @param {float} kt - коэффициент 0...1
 * @returns {bool} true - когда элемент находится во вьюпорте
 */
export function elemInViewport(el,kt) {
	let
		{top,left,bottom,right}= el.getBoundingClientRect(),
		width = document.documentElement.clientWidth,
		height = document.documentElement.clientHeight,
		maxWidth = right - left,
		maxHeight = bottom - top;

	return Math.min(height,bottom)- Math.max(0,top) >= maxHeight*kt && Math.min(width,right)- Math.max(0,left)>= maxWidth*kt
}


/**
 * @param {Node|NodeList} els
 * @param {obj} cssObj - CSS rules
 */
export function css (els, cssObj) {
	if(!els.length) els=[els];

	[].forEach.call(els, el => {
		// console.log({el});
		Object.keys(cssObj).forEach(st=>{
			el.style[st]= cssObj[st];
			// console.log(st,el.style[st]);
		});
	});
}

/**
 *
 * @param {Node|selector} n
 * optional @param {Node} ctx - контекст поиска
 * @returns {Node}
 */
function getNode(n, ctx){
	ctx= ctx || document;
	return (n instanceof Object)? n: ctx.querySelector(n);
}

/**
 * Навешиваем обработчик(и)
 * @param {Node|string} obj - Node | selector
 * @param {Event.responseType} event
 * @param {function} handler
 */
export function on(obj, event, handler) {
	if(!obj){
		return;
	}
	event= event.split(' ');
	obj= getNode(obj);
	/* try{
		obj= (obj instanceof Object)? obj: document.querySelector(obj);
	}
	catch{
		console.log({obj});
	}
 */

	event.forEach(e=>{
		if (obj.addEventListener !== undefined) obj.addEventListener(e, handler, true);
		else if (obj.attachEvent !== undefined) obj.attachEvent('on' + e, handler, true);
	});
}

// *Удаляем обработчик
export function off(obj, event, handler) {
	if (typeof (obj.removeEventListener) != 'undefined') obj.removeEventListener(event, handler, true);
	else if (typeof (obj.detachEvent) != 'undefined') obj.detachEvent('on' + event, handler);
}


// *Озвучка текста
export function speak(txt){
	const synth = window.speechSynthesis;
	if(synth.pending){
		return;
	}
	else if(synth.speaking){
		// synth.pause();
		synth.cancel();
		return;
	}
	// console.log({synth});
	var t= new SpeechSynthesisUtterance(txt);
	t.lang= 'ru';
	synth.speak(t);
}


export const SpeechRecognition =
window.SpeechRecognition || window.webkitSpeechRecognition;

export function prinText(ta){
	const SpeechGrammarList =
		window.SpeechGrammarList || window.webkitSpeechGrammarList;
	const SpeechRecognitionEvent =
		window.SpeechRecognitionEvent || window.webkitSpeechRecognitionEvent;

	if(!SpeechRecognition) return;

	const grammar = '#JSGF V1.0; ';

	const recognition = new SpeechRecognition();
	const speechRecognitionList = new SpeechGrammarList();
	speechRecognitionList.addFromString(grammar, 1);
	recognition.grammars = speechRecognitionList;
	recognition.lang = 'ru-RU';
	recognition.interimResults = false;
	// recognition.interimResults = true;
	recognition.maxAlternatives = 1;
	recognition.continuous = false;

	recognition.start();
	console.log('Ready to receive...');

	recognition.onaudiostart = function() {
		console.log('Audio START...');
	};

	recognition.onspeechend = function(event) {
		recognition.stop();
		console.log('Audio END...', {event});
	};

	return new Promise((res,rej)=>{
		recognition.onerror = function(err) {
			console.log(`Error: ${err.message}`, err);
			 return rej(err);
		}

		recognition.onresult = function(event) {
			console.log('Result:', event);
			const last = event.results.length - 1;
			const txt = event.results[last][0].transcript;

			return res(txt);
		};
	});
}


export function sendNotification(title, options) {
	// Проверим, поддерживает ли браузер HTML5 Notifications
	if (!window.Notification) {
	modal('Ваш браузер не поддерживает HTML Notifications, его необходимо обновить.');
	}

	// Проверим, есть ли права на отправку уведомлений
	else if (Notification.permission === "granted") {
	// Если права есть, отправим уведомление
	var notification = new Notification(title, options);
	}

	// Если прав нет, пытаемся их получить
	else if (Notification.permission !== 'denied') {
		Notification.requestPermission()
		.then(ok=>new Notification(title, options));
	} else {
		// Пользователь ранее отклонил наш запрос на показ уведомлений
	}
	return notification;
}



/**
 * Асинхронная подгрузка стиля
 * @param {url} href
 */
export function addStyle(href){
	href= href.replace(/^\/|\.\//g,'');
	if(href.indexOf('http') !== 0){
		href= `${location.protocol}//${location.host}${location.pathname}${href}`;
	}
	if(document.querySelector(`link[href='${href}']`)){
		return console.log(`Style already ${href} exist in `, document.styleSheets );
	}
	//

	let style= document.createElement('link');

	style.href= href;
	style.rel= 'stylesheet';
	document.head.appendChild(style);
	console.log({style});
}