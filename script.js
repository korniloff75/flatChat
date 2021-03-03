'use strict';
/* import * as BBscript from "./assets/BB.js";
import * as StateScript from "./assets/State.js";
import * as Imgscript from "./assets/Images.js"; */


// console.log({BBscript});

// *noConsole
if(/\.ru/i.test(location.host)){
	var console= {
		log: ()=>false,
		info: ()=>false,
	}
}

var BBscript= import ('./assets/BB.js');
var StateScript= import ('./assets/State.js');
var Imgscript= import ('./assets/Images.js');


window.smoothScrollTo = (function (_w) {
	'use strict';

	var timer, start, factor;

	return function (targetY, duration, el) {
		el= el || _w;
		var offset = el.pageYOffset,
				delta  = targetY - el.pageYOffset; // Y-offset difference
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
			el.scrollBy(0, y - el.pageYOffset);
		}

		timer = setInterval(step, 50);
		return timer;
	};
})(window);


function css (els, cssObj) {
	if(!els.length) els=[els];

	[].forEach.call(els, el => {
		// console.log({el});
		Object.keys(cssObj).forEach(st=>{
			el.style[st]= cssObj[st];
			// console.log(st,el.style[st]);
		});
	});
}

function on(obj, event, handler) {
	if (typeof (obj.addEventListener) != 'undefined') obj.addEventListener(event, handler, true);
	else if (typeof (obj.attachEvent) != 'undefined') obj.attachEvent('on' + event, handler, true);
}

function off(obj, event, handler) {
	if (typeof (obj.removeEventListener) != 'undefined') obj.removeEventListener(event, handler, true);
	else if (typeof (obj.detachEvent) != 'undefined') obj.detachEvent('on' + event, handler);
}


(function (_w) {
	var msgsDialog = document.getElementById("msgsDialog");
	var sendDialog = document.getElementById("sendDialog");

	var msgs = document.getElementById("msgsContent");
	var oAS = document.getElementById("autoScroll");
	var oSND = document.getElementById("playSound");
	var oAH = document.getElementById("autoHeight");
	var f = document.getElementById("sendForm");
	var name = f.elements.name;
	var text = f.elements.text,
		usersList = document.querySelector('.users');;

	// StateScript.then(s=>{s.msgs= msgs});

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
		snd = new Audio("data:audio/mpeg;base64,//uQxAAAEvGLIVT0AAuBtax3P2QCIAAIAGWUC+HkqfLeTs0zTQg7wL4BGCfQQ3A1BYDjCA4BoHgpWlFh2Lu+QLnkCgpRYu+4uL2QDQPDIT0FBQyRQUpwbh+fo7i4uLvoKA3BufBAoYlehAoKClOKChiIlbigokli4u8O4u73oh7v/6PcIKChlC6J//1vcEChhYNAaGU7u717u78PoZRYdnkALgvHkClOe/ARmPZVAwGIyHA4FQkCQJBAMAHAEzABwATHDWRgjAHQYFwJEe8X1jOkkG4y04RCJQANqNrMDJKMgDIMDRFDAxXiDAy/CIAwrhVUWkuBgVCABqvXuBybuyBzvSGtzQraTgYJkCgaaxbgYrAVAaNjqAZCANLHNWvS4GC0E4GQARAAgNQMHYCwspBusxOtr/DbAs2Q4tCcxlxZZ5qpw//nS+WTcwPjkEQ/qf/m9y4aLD4xY0FCM/1vr/+DY2I0QIoLjKBoVwKACAwIAAAUBGWCCBZIDYgy509qN0uj/1/Pc3uTBLf/LAI423K5bICDMzK72SSB0piCDVSm//uSxAoDzHSZNbzGADAAADSAAAAEJz44gRLSUSRJEVS0SgAhJcOjJdZkxJsZJBqIqloyMjISTExMTExPVtWTkSRJMXWjISls2t81WrfqtqMBoOCJ4Kgqs6Ij3/8t/g1///ywcLVMQU1FMy45OC40VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVUxBTUUzLjk4LjRVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/7ksQ5A8AAAaQAAAAgAAA0gAAABFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVMQU1FMy45OC40VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVX/+5LEOQPAAAGkAAAAIAAANIAAAARVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV");
	}
	catch (e) {
		snd = null;
		console.error("can't play sounds");
	}


	function post(url, reqParams, handler) {
		var XMLo;

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

		XMLo.open("POST", url, true);

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
		XMLo.setRequestHeader("Accept", "*/*");

		XMLo.onreadystatechange = function () {
			if (XMLo.readyState !== 4) return;

			// if (XMLo.status == 200 || XMLo.status == 0) {
			if (XMLo.status === 200) {
				// console.log({XMLo});

				var json= JSON.parse(XMLo.responseText);
				// console.log({json});
				handler(true, XMLo.status, (json? json: XMLo.responseText), (XMLo.responseXML ? XMLo.responseXML.documentElement : null));
			}
			else if(XMLo.status !== 0){
				handler(false, XMLo.status, XMLo.responseText);
			}

			XMLo = null;
		};

		XMLo.send(reqParams);

		return (XMLo !== null);
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
	function refresh(params, handler) {
		params = params || {};
		params.lastMod = params.lastMod == 0? 0 : lastMod;

		post(
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

			if(f.name.type !== 'hidden' && Chat.name){
				f.name.type= 'hidden';
				f.name.value= Chat.name;
				console.log(f.name.value);
			}

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

				StateScript.then(s=>s.findMyPosts(msgs));

				if (oAS.checked) scrollBottom();

				if (oSND.checked) {
					if (snd) {
						snd.pause();
						snd.currentTime = 0;
						snd.play()
						.catch((error) => {
							console.log(error);
						});
					}
				}
			}

			// *Every
			StateScript.then(State=>{
				State.setDB(response.state)
				.hilightUsers(msgs, usersList);
			});
		}

		if (handler) handler(success, statusCode, html);
	} //refreshAfter


	var poll = (function () {
		var t,
			inProgress = false;

		var rq = function () {
			if (inProgress) return;

			inProgress = true;
			// msgsDialogWaiter.show(true, false);
			refresh(
				{ mode: "list" },
				function (success, status, txt) {
					// msgsDialogWaiter.show(false);
					inProgress = false;
					poll(false, true);
				}
			);
		};

		return function (refreshNow, rewait) {
			if (rewait === true) {
				if (t) clearTimeout(t);
				t = setTimeout(rq, REFRESHTIME );
			}

			if (refreshNow === true) rq();
		};
	})();

	oAS.onchange = function () {
		if (this.checked) scrollBottom();
	};

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
		BBscript.then(BB=>{
			BB.replaceText(f.text);
			// console.log(f.text.innerHTML);
			// debugger;

			var fd= new FormData(f);
			fd.append('mode','post');
			fd.append('lastMod',0);
			fd.append('ts', parseInt(Date.now()/1000));

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

					StateScript.then(s=>s.findMyPosts(msgs));
				}
			);
		});

		return false;
	};

	on(f,'submit', formSubmit);


	msgs.onclick = function (e) {
		e = e || _w.event;

		var t = e.target || e.srcElement,
			s= t.closest('.msg'),
			c=  t.closest('a[href*=\'#\']'),
			ac= t.closest('.cite');

		// *Переход с цитаты к посту
		if(c) return goCite(c,e);

		// if (!t.closest('.cite')) return true;

		// *Вставляем цитату
		if (s && ac) return addCite(s,e);
	};


	function addCite(msg,e){
		// e.preventDefault();
		e.stopPropagation();
		// *Цитата
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
		text.value += "\n>" + href + '#' + msg.id + "\n***[/cite]\n";

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

	if (oAS.checked) scrollBottom();

	text.focus();

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

	// on(f.text, 'keyup', countChars);
	on(f.text, 'input', countChars);
	// ?
	on(f.text, 'change', countChars);


	// *Клик по имени
	BBscript.then(BB=>{
		on(msgs, 'click', e=>{
			var name= e.target.closest('span.name');
			if(!name) return;

			e.stopPropagation();
			e.preventDefault();

			BB.insert(`[b]${name.textContent}`,'[/b], ',f.text);
		});
	});





	// *
	on(_w, _w.onpageshow? 'pageshow': 'load', e=>{
		smoothScrollTo(msgs.offsetTop, 500);
		scrollBottom();
		// var msgs= document.querySelectorAll('.msg');
		// smoothScrollTo(msgs[msgs.length-1].offsetTop, 500, document.querySelector('#msgsContent'));

		countChars.call(f.text);

		BBscript.then(BB=>{
			var panel= BB.createPanel(f.text);
			sendDialog.insertBefore(panel, f.text);

		});

		Imgscript.then(I=>{
			I.init(msgs);
		});

		StateScript.then(s=>s.findMyPosts(msgs));
	});

})(window);

