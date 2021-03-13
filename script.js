'use strict';
import {scrollIntoView, css, on, off} from './assets/helpers.js';
export {scrollIntoView, css, on, off};
import * as Adm from './assets/Admin.js';
import * as BB from './assets/BB.js';
import * as State from './assets/State.js';
import * as Img from './assets/Images.js';

// console.log({Adm});

let _w= window;

// *noConsole
if(/\.ru/i.test(location.host)){
	var console= {
		log: ()=>false,
		info: ()=>false,
	}
}
else {
	var console= _w.console;
}

// var BBscript= import ('./assets/BB.js');
// var StateScript= import ('./assets/State.js');
// var Imgscript= import ('./assets/Images.js');


export var Ajax={
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

		if (XMLo == null) return null;

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

			XMLo = null;
		};

		XMLo.send(reqParams);

		return (XMLo !== null);
	}
}

// ?
// var post= Ajax.post;

var msgsDialog = document.getElementById("msgsDialog");
var sendDialog = document.getElementById("sendDialog");

var msgs = document.getElementById("msgsContent");
var f = document.getElementById("sendForm"),
	name = f.elements.name,
	text = f.elements.text,
	usersList = document.querySelector('.users'),
	attach= f['attach[]'],
	attachNode= f.querySelector('.attaches>div');

var oSND = document.getElementById("playSound");
var oAH = document.getElementById("autoHeight");


function hideName(){
	if(f.name.type !== 'hidden' && Chat.name){
		f.name.type= 'hidden';
		f.name.value= Chat.name;
		console.log(f.name.value);
	}
}

hideName();

// autoHeight
function ah(el, maxH, state) {
	if (arguments.length === 1) {
		if (el._ah_) el._ah_();
		return;
	}

	if (el._ah_) off(el, "input", el._ah_);
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

			//							el.style.height = "auto";
			var nh = el.scrollHeight + dh;
			if (maxH && nh > maxH) nh = maxH;
			el.style.height = nh + "px";
		};

		on(el, "input", el._ah_);
		el._ah_();
	}
}
if (oAH.checked) ah(text, 500, true);

// var msgsDialogWaiter = WAITER(msgsDialog);
// var sendDialogWaiter = WAITER(sendDialog);

var snd = null;
try {
	snd = new Audio("./assets/chat2.mp3");
	snd.volume= .5;
}
catch (e) {
	snd = null;
	console.error("can't play sounds");
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

var tipUpper = (function () {
	var lastTip = null;

	return function (o, html, ax, ay) {
		if (ax == undefined) ax = 0;
		if (ay == undefined) ay = -5;

		function getCords(elem) {
			var box = elem.getBoundingClientRect();

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

		function attachEvents(tip, o) {
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
			tip.o = o;

			on(o, "change", tip.eh);
			on(document, "mousedown", tip.eh);
			on(document, "keydown", tip.eh);
		}

		function detachEvents(tip) {
			if (!tip.eh) return;

			off(tip.o, "change", tip.eh);
			off(document, "mousedown", tip.eh);
			off(document, "keydown", tip.eh);
		}

		if (lastTip) lastTip.eh();

		var t = document.createElement("div");
		t.className = "tipUpper";
		t.innerHTML = '<div class="ugol"></div><div class="ugolI"></div><div class="msg">' + html + '</div>';
		var c = getCords(o);
		t.style.left = (c.left + ax) + "px";
		t.style.top = (c.top + o.offsetHeight + ay) + "px";
		var f = fade(
			t,
			{
				ob: 0,
				oe: 1,
				os: 0.1
			}
		);

		document.body.appendChild(t);

		attachEvents(t, o);
		o.focus();

		lastTip = t;
	};
})();


_w.scrollBottom= function scrollBottom() {
	var oAS = document.getElementById("autoScroll");
	if(!oAS.checked) return;

	var os = msgs.onscroll;
	msgs.onscroll = function (e) {
		if (!e) e = _w.event;
		if (e.preventDefault) e.preventDefault();
		if (e.stopPropagation) e.stopPropagation();

		return false;
	};
	msgs.scrollLeft = 0;
	msgs.scrollTop = msgs.scrollHeight;
	setTimeout(function () { msgs.onscroll = os; }, 10);
}


/**
 * *Обновление страницы
 * @param {obj} params
 * @param {function} handler
 */
export function refresh(params, handler) {
	// if(!(params instanceof FormData)){
		Object.assign(params, {responseType:'json'});
	// }

	params.lastMod = params.lastMod == 0? 0 : lastMod;

	Ajax.post(
		_w.location.toString(),
		params,
		refreshAfter.bind(null,handler)
	);
};


/**
 * *Коллбэк для refresh
 * @param {function} handler - callback after ajax
 * @param {bool} success - result of ajax
 * @param {string} statusCode - must be 200
 * @param {obj|string} response - data from ajax
 */
function refreshAfter (handler, success, statusCode, response) {
	// console.log(arguments);

	if (!success) {
		tipUpper(msgsDialog, "Ошибка сервера: " + statusCode);
		response = undefined;
	}

	if (response !== undefined) {
		var html= (response instanceof String)
			? response
			: response.html;

		Object.assign(Chat, response.Chat);

		// console.log({Chat});

		hideName();

		var p = html.indexOf("\n");

		if (p > 0) {
			var s = html.substring(0, p).split(':'), lm;

			if (s) {
				lm = +s[1];
				console.log({s,lm});
				s = s[0];

				html = html.substring(p + 1);

				if (s === "NONMODIFIED") html = undefined;
				if (s === "OK") lastMod = lm;
			}
		}

		// *if Modifed
		if (html !== undefined) {
			msgs.innerHTML = html;

			State.findMyPosts(msgs);

			scrollBottom();

			if (oSND.checked) {
				if (snd) {
					snd.pause();
					snd.currentTime = 0;
					snd.play()
					.catch((err) => {
						console.log('Аудио не воспроизвелось: \n'+err);
					});
				}
			}
		}

		// *Every
		State.setDB(response.state)
		.hilightUsers(msgs, usersList);

	}

	if (handler) handler(success, statusCode, html);
} //refreshAfter


/**
 * Long Polling
 * @param {bool} rewait - Ожидание перед запросом
 */
export var poll = (function () {
	var t,
		inProgress = false,
		data= { mode: "list" };

	var rq = function () {
		console.log({Chat});
		if (inProgress || poll.stop || !Chat.name) return;

		/* if(!Chat.name){
			data.name= f.name.value;
		} */

		inProgress = true;
		// msgsDialogWaiter.show(true, false);
		refresh(
			data,
			function (success, status, txt) {
				// msgsDialogWaiter.show(false);
				inProgress = false;
				poll(true);
			}
		);
	};

	return function (rewait) {
		if (rewait === true) {
			if (t) clearTimeout(t);
			t = setTimeout(rq, REFRESHTIME );
		}
		else rq();
	};
})();

/* oAS.onchange = function () {
	scrollBottom();
}; */

oSND.onchange = function () {
	if (oSND.checked === false) {
		if (snd) {
			snd.pause();
			snd.currentTime = 0;
		}
	}
};

oAH.onclick = function () {
	ah(text, 500, oAH.checked);
};


// *Отправка
function formSubmit (e) {
	if(e){
		e.stopPropagation();
		e.preventDefault();
	}
	if (!(name.value= name.value.trim())) {
		tipUpper(name, "Пожалуйста, введите свое имя");
		return false;
	}

	if (!(text.value= text.value.trim())) {
		tipUpper(text, "Пожалуйста, введите текст");
		return false;
	}

	// *Smiles
	BB.replaceText(f.text);
	// console.log(f.text.value);
	// debugger;

	var fd= new FormData(f);
	fd.append('mode','post');
	fd.append('lastMod',0);
	fd.append('ts', parseInt(Date.now()/1000));
	fd.responseType= 'json';

	refresh(
		fd,
		function (state, status, txt) {
			if (state) {
				text.value = "";
				ah(text);
			}

			// *Очищаем
			f.reset();
			name.value= Chat.name;

			showAttaches();
			countChars.call(f.text);

			State.findMyPosts(msgs);
		}
	);
	return false;
};


on(document.querySelector('.svg-toRead'), 'click', e=>{
	e.stopPropagation();
	scrollIntoView(msgs,{block:'start'}, e);
});

on(document.querySelector('.svg-toForm'), 'click', e=>{
	e.stopPropagation();
	scrollIntoView(sendDialog,{block:'end'}, e);
});

on(f,'submit', formSubmit);

on(msgs,'click',e=>{
	e = e || _w.event;

	var t = e.target || e.srcElement,
		s= t.closest('.msg'),
		c=  t.closest('a[href*=\'#\']'),
		ac= t.closest('.cite'),
		vb= t.closest('.voice');

	// *Переход с цитаты к посту
	if(c) return goCite(c,e);

	// if (!t.closest('.cite')) return true;

	// *Вставляем цитату
	if (s && ac) return addCite(s,e);

	if(s && vb) {
		var post= s.querySelector('.post').cloneNode(true);
		[].forEach.call(post.querySelectorAll('.cite_disp'), i=>i.remove());
		speak(post.textContent.replace(/\p{S}/iug,''));
		return;
	}
});


// *Озвучка текста
function speak(txt){
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


// *Цитата
function addCite(msg,e){
	// e.preventDefault();
	e.stopPropagation();

	var ps = msg.getElementsByTagName("span");
	var name = ps[0].innerText || ps[0].textContent;
	var misc = ps[1].innerText || ps[1].textContent;
	var txt = "",
		s = msg.firstChild.nextSibling;
	while (s) {
		if (s.tagName) txt += s.tagName == "BR" ? "\n" : s.innerText || s.textContent;
		else txt += s.nodeValue;
		s = s.nextSibling;
	}

	console.log({msg});

	// *Цитата
	text.value += "[cite]" + name + " " + misc + "\n" + txt.replace(/(^|\n)/g, "$1>");

	var href= location.href.split('#')[0];
	text.value += "\n>" + href + '#' + msg.id + "[/cite]\n";

	text.focus();
}

function goCite(link,e){
	// e.preventDefault();
	e.stopPropagation();
	link.target= "_self";
	return true;
}

/* msgs.onscroll = function () {
	// oAS.checked = false;
}; */

name.onkeydown = text.onkeydown = function (e) {
	// if (sendDialogWaiter.isShow()) return;
	if (!e) e = _w.event;
	if (e.keyCode === 13 && e.ctrlKey) formSubmit();
};


poll(true);


// *Считаем символы
function countChars(e) {
	var
		maxLen= this.maxLength,
		count= maxLen - this.value.length;

	if (count < 1) {
		count=0;
		this.blur();
		this.value= this.value.substr(0,maxLen);
	}

	// console.log(maxLen, this.value.length);

	document.querySelector('#maxLen').textContent= count;
};

function showAttaches(){
	if (!attach.files.length){
		attachNode.parentNode.hidden= 1;
		return;
	}

	attachNode.innerHTML='';
	attachNode.parentNode.hidden= 0;

	for(var file of attach.files){
		var p= document.createElement('p');
		p.textContent= file.name;
		attachNode.appendChild(p);
	}
}


on(f.text, 'input', countChars);
// ?
on(f.text, 'change', countChars);
on(f, 'change', function(e){
	showAttaches();
});

// *remove attaches
on(attachNode.parentNode, 'click', function(e) {
	var t= e.target;
	e.preventDefault();

	if(t.closest('.clear')){
		attach.value='';
		showAttaches();
	}
});


// *Клик по имени
on(msgs, 'click', e=>{
	var name= e.target.closest('span.name');
	if(!name) return;

	e.stopPropagation();
	e.preventDefault();

	BB.insert(`[b]${name.textContent}`,'[/b], ',f.text);
});





// *
on(_w, _w.onpageshow? 'pageshow': 'load', e=>{
	scrollIntoView(msgs, {block:'start'});

	scrollBottom();
	// var msgs= document.querySelectorAll('.msg');
	// smoothScrollTo(msgs[msgs.length-1].offsetTop, 500, document.querySelector('#msgsContent'));

	countChars.call(f.text);

	var panel= BB.createPanel(f.text);
	sendDialog.insertBefore(panel, f.text);

	Img.init(msgs);

	State.findMyPosts(msgs);

	showAttaches();

	console.log({Adm});

	/* Admin.then(Adm=>{
		console.log(Adm.PanelHandles);
		Adm.PanelHandles();
	}) */
});
