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

	/**
	 *
	 * @param {str} url
	 * optional @param {obj} reqParams
	 * optional @param {function} callback - deprecated
	 * @returns {Promise}
	 */
	request: function (url, reqParams, callback) {
		url= url || location.href;
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

			// reqParams = JSON.stringify(reqParams || {});
		}

		XMLo.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		XMLo.setRequestHeader("Accept", "*\/*");

		return ajaxHandler(XMLo, reqParams);

		// return (XMLo !== null);
	}
}

function ajaxHandler(XMLo, reqParams){
	return new Promise((res,rej)=>{
		console.log({reqParams});
		XMLo.onreadystatechange = function () {
			// console.log('XMLo.readyState', XMLo.readyState);
			if (XMLo.readyState !== 4) return;
			// if (XMLo.readyState !== 4) return ajaxHandler.call(this,arguments);

			// if (XMLo.status == 200 || XMLo.status == 0) {
			else if (XMLo.status === 200) {
				console.log('Ajax success',{XMLo});

				return res(XMLo);
			}
			else{
				console.log('Ajax fail',{XMLo});
				return rej(XMLo);
			}
		};

		/* XMLo.onprogress = e=>{
			console.log('progress',e);
		} */

		XMLo.send(reqParams);
	});
}


/**
 * Всплывающие уведомления
 * @param {HTMLElement} el - target 4 tipUpper
 */
export let tipUpper = (function () {
	var lastTip = null;

	return function (el, html, ax, ay) {
		var box = el.getBoundingClientRect();

		if (ax === undefined) ax = box.width/2;
		if (ay === undefined) ay = -5;

		function attachEvents(tip, el) {
			detachEvents(tip);
			tip.eh = function () {
				detachEvents(tip);
				f.stop();
				lastTip = null;
				f.start(
					true,
					{
						oe: 0,
						os: 0.1,
						handler: function (over, f) {
							if (over) {
								tip.style.display = "none";
								tip.parentNode.removeChild(tip);
							}
						}
					},
					true
				);
			};
			tip.el = el;

			on(el, "change", tip.eh);
			on(document, "mousedown keydown", tip.eh);
		}

		function detachEvents(tip) {
			if (!tip.eh) return;

			off(tip.el, "change", tip.eh);
			off(document, "mousedown", tip.eh);
			off(document, "keydown", tip.eh);
		}

		if (lastTip) lastTip.eh();

		var t = document.createElement("div");
		t.className = "tipUpper";
		t.innerHTML = '<div class="ugol"></div><div class="ugolI"></div><div class="msg">' + html + '</div>';
		var c = getCords(box);
		t.style.left = (c.left + ax) + "px";
		t.style.top = (c.top + el.offsetHeight + ay) + "px";
		var f = fade(
			t,
			{
				ob: 0,
				oe: 1,
				os: 0.1
			}
		);

		document.body.appendChild(t);

		attachEvents(t, el);
		el.focus();

		lastTip = t;
	};
})();


function getCords(box) {
	var body = document.body;
	var docEl = document.documentElement;

	var scrollTop = _w.pageYOffset || docEl.scrollTop || body.scrollTop;
	var scrollLeft = _w.pageXOffset || docEl.scrollLeft || body.scrollLeft;

	var clientTop = docEl.clientTop || body.clientTop || 0;
	var clientLeft = docEl.clientLeft || body.clientLeft || 0;

	var top = box.top + scrollTop - clientTop;
	var left = box.left + scrollLeft - clientLeft;

	return { top: Math.round(top), left: Math.round(left) };
}


function fade(o, opts, dontStartNow) {
	var ov, ob, oe, os, t;

	function th() {
		ov += os;
		if ((os > 0 && ov >= oe) || (os < 0 && ov <= oe)) {
			ov = oe;
			clearInterval(t);
			t = null;
		}

		o.style.opacity = ov;

		if (!t && opts.hasOwnProperty("handler")) opts.handler(true, fs, o);
	}

	function init() {
		os = opts.hasOwnProperty("os") ? Math.abs(opts.os) : 0.1;

		if (!opts.hasOwnProperty("delay")) opts.delay = 30;

		if (!opts.hasOwnProperty("ob")) {
			ob = parseFloat(o.style.opacity);
			if (isNaN(ob)) ob = 1;
		}
		else {
			ob = opts.ob;
			o.style.opacity = ob;
		}
		ov = ob;

		if (!opts.hasOwnProperty("oe")) {
			oe = parseFloat(o.style.opacity);
			if (isNaN(oe)) oe = 1;
		}
		else oe = opts.oe;

		if (ob > oe) os = -os;

		if (ob != oe) t = setInterval(th, opts.delay);
	}

	var fs = {
		get: function () {
			return {
				opts: opts,
				ov: ov,
				ob: ob,
				oe: oe,
				os: os
			};
		},

		stop: function (end, dontNotify) {
			if (!t) return;

			clearInterval(t);
			t = null;
			if (end) o.style.opacity = oe;

			if (dontNotify !== true && opts.hasOwnProperty("handler")) opts.handler(true, fs, o);
		},

		start: function (restart, newOpts, dontNotify) {
			if (t) return;

			if (newOpts) opts = newOpts;

			if (restart) {
				init();
				if (dontNotify !== true && opts.hasOwnProperty("handler")) opts.handler(false, fs, o);
			}
			else t = setInterval(th, opts.delay);
		}
	};

	if (dontStartNow !== true) fs.start(true);
	return fs;
}


/**
 * Авторазмер элемента
 * @param {HTMLElement} el - элемент, меняющий высоту от контента
 * optional @param {int} maxH - макс. высота
 * optional @param {bool} state
 */
 export function autoHeight(el, maxH, state) {
	if (arguments.length === 1) {
		if (el._ah_) el._ah_();
		return;
	}

	if (el._ah_) off(el, "input paste", el._ah_);
	delete (el._ah_);
	el.style.height = "auto";

	if (state) {
		el.style.boxSizing = "border-box";
		var h = el.offsetHeight,
			dh = h - el.clientHeight,
			t;

		el._ah_ = function () {
			while (true) {
				t = el.offsetHeight - 16;
				el.style.height = t + "px";
				if (t < h || el.scrollHeight > el.clientHeight) break;
			}

			var nh = el.scrollHeight + dh;
			if (maxH && nh > maxH) nh = maxH;
			el.style.height = nh + "px";
		};

		on(el, "input paste", el._ah_);
		el._ah_();
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
 * *Навешиваем обработчик(и)
 * @param {Node|string} obj - Node | selector
 * @param {Event.responseType} event
 * @param {function} handler
 */
export function on(obj, event, handler) {
	if(!obj){
		return;
	}
	event= event.split(' ').filter(i=>i);
	obj= getNode(obj);

	event.forEach(e=>{
		if (obj.addEventListener !== undefined) obj.addEventListener(e, handler, true);
		else if (obj.attachEvent !== undefined) obj.attachEvent('on' + e, handler, true);
	});
}

// *Удаляем обработчик(и)
export function off(obj, event, handler) {
	if(!obj){
		return;
	}
	event= event.split(' ').filter(i=>i);
	obj= getNode(obj);

	event.forEach(e=>{
		if (obj.removeEventListener !== undefined) obj.removeEventListener(e, handler, true);
		else if (obj.detachEvent !== undefined) obj.detachEvent('on' + e, handler, true);
	});
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

export function getUTC(d){
	if(!(d instanceof Date)) new Error('Аргумент должен быть экземляром Date');
	return `${d.getFullYear()}-${fixZero(d.getMonth()+1)}-${fixZero(d.getDate())} ${fixZero(d.getHours())}:${fixZero(d.getMinutes())}`;
}

// *fix dates
function fixZero (num) {
	return num < 10? '0'+num: num;
}

export function logTrace(msg) {
	let err = new Error(),
		args= Array.from(arguments);
	args.push(err.stack);
	// console.log(args, Array.from(arguments));
	console.log.apply(console, args );
}