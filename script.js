(function () {
	var msgsDialog = document.getElementById("msgsDialog");
	var sendDialog = document.getElementById("sendDialog");
	var submit = document.getElementById("submit");

	var msgs = document.getElementById("msgsContent");
	var oAS = document.getElementById("autoScroll");
	var oSND = document.getElementById("playSound");
	var oAH = document.getElementById("autoHeight");
	var f = document.getElementById("sendForm");
	var name = f.elements.name;
	var text = f.elements.text;

	function ah(el, maxH, state) {
		if (arguments.length === 1) {
			if (el._ah_) el._ah_();
			return;
		}

		if (el._ah_) de(el, "input", el._ah_);
		delete (el._ah_);
		el.style.height = "auto";

		if (state) {
			el.style.boxSizing = "border-box";
			var h = el.offsetHeight;
			var dh = h - el.clientHeight;

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

			ae(el, "input", el._ah_);
			el._ah_();
		}
	}
	if (oAH.checked) ah(text, 500, true);

	var msgsDialogWaiter = WAITER(msgsDialog);
	var sendDialogWaiter = WAITER(sendDialog);

	var snd = null;
	try {
		snd = new Audio("data:audio/mpeg;base64,//uQxAAAEvGLIVT0AAuBtax3P2QCIAAIAGWUC+HkqfLeTs0zTQg7wL4BGCfQQ3A1BYDjCA4BoHgpWlFh2Lu+QLnkCgpRYu+4uL2QDQPDIT0FBQyRQUpwbh+fo7i4uLvoKA3BufBAoYlehAoKClOKChiIlbigokli4u8O4u73oh7v/6PcIKChlC6J//1vcEChhYNAaGU7u717u78PoZRYdnkALgvHkClOe/ARmPZVAwGIyHA4FQkCQJBAMAHAEzABwATHDWRgjAHQYFwJEe8X1jOkkG4y04RCJQANqNrMDJKMgDIMDRFDAxXiDAy/CIAwrhVUWkuBgVCABqvXuBybuyBzvSGtzQraTgYJkCgaaxbgYrAVAaNjqAZCANLHNWvS4GC0E4GQARAAgNQMHYCwspBusxOtr/DbAs2Q4tCcxlxZZ5qpw//nS+WTcwPjkEQ/qf/m9y4aLD4xY0FCM/1vr/+DY2I0QIoLjKBoVwKACAwIAAAUBGWCCBZIDYgy509qN0uj/1/Pc3uTBLf/LAI423K5bICDMzK72SSB0piCDVSm//uSxAoDzHSZNbzGADAAADSAAAAEJz44gRLSUSRJEVS0SgAhJcOjJdZkxJsZJBqIqloyMjISTExMTExPVtWTkSRJMXWjISls2t81WrfqtqMBoOCJ4Kgqs6Ij3/8t/g1///ywcLVMQU1FMy45OC40VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVUxBTUUzLjk4LjRVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVf/7ksQ5A8AAAaQAAAAgAAA0gAAABFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVMQU1FMy45OC40VVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVX/+5LEOQPAAAGkAAAAIAAANIAAAARVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV");
	}
	catch (e) {
		snd = null;
		console.error("can't play sounds");
	}

	function ae(obj, event, handler) {
		if (typeof (obj.addEventListener) != 'undefined') obj.addEventListener(event, handler, true);
		else if (typeof (obj.attachEvent) != 'undefined') obj.attachEvent('on' + event, handler, true);
	}

	function de(obj, event, handler) {
		if (typeof (obj.removeEventListener) != 'undefined') obj.removeEventListener(event, handler, true);
		else if (typeof (obj.detachEvent) != 'undefined') obj.detachEvent('on' + event, handler);
	}

	function post(url, reqParams, handler) {
		var XMLo;

		if (window.XMLHttpRequest) {
			try { XMLo = new XMLHttpRequest(); }
			catch (e) { XMLo = null; }
		} else if (window.ActiveXObject) {
			try { XMLo = new ActiveXObject("Msxml2.XMLHTTP"); }
			catch (e) {
				try { XMLo = new ActiveXObject("Microsoft.XMLHTTP"); }
				catch (e) { XMLo = null; }
			}
		}

		if (XMLo == null) return null;

		XMLo.open("POST", url, true);

		XMLo.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
		if (reqParams) {
			var prm = "";
			for (var i in reqParams) prm += "&" + i + "=" + encodeURIComponent(reqParams[i]);
			reqParams = prm;
			//XMLo.setRequestHeader( "Content-Length", reqParams.length );
		}
		else {
			reqParams = " ";
			//XMLo.setRequestHeader( "Content-Length", 1 );
		}
		XMLo.setRequestHeader("X-Requested-With", "XMLHttpRequest");
		XMLo.setRequestHeader("Accept", "*/*");

		XMLo.onreadystatechange = function () {
			if (XMLo.readyState == 4) {
				if (XMLo.status == 200 || XMLo.status == 0) {
					handler(true, XMLo.status, XMLo.responseText, (XMLo.responseXML ? XMLo.responseXML.documentElement : null));
				}
				else {
					handler(false, XMLo.status, XMLo.responseText);
				}

				delete XMLo;
				XMLo = null;
			}
		};

		XMLo.send(reqParams);

		return (XMLo != null);
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

				var scrollTop = window.pageYOffset || docEl.scrollTop || body.scrollTop;
				var scrollLeft = window.pageXOffset || docEl.scrollLeft || body.scrollLeft;

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

				ae(o, "change", tip.eh);
				ae(document, "mousedown", tip.eh);
				ae(document, "keydown", tip.eh);
			}

			function detachEvents(tip) {
				if (!tip.eh) return;

				de(tip.o, "change", tip.eh);
				de(document, "mousedown", tip.eh);
				de(document, "keydown", tip.eh);
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

	function WAITER(o) {
		var count = 0;
		var w = document.createElement("div");
		remove();
		var f = fade(w, null, true);
		var oMax = 0.3;
		var t = null;


		function th() {
			clearTimeout(t);
			t = null;

			if (count > 0) {
				w.style.visibility = "hidden";
				if (!w.parentNode) {
					if (!o.style.position) o.style.position = "relative";
					o.appendChild(w);
				}
				w.className = "waiter waiterProgress";
				f.start(true, { ob: 0, oe: oMax, os: 0.05 }, true);
				w.style.visibility = "visible";
			}
			else remove();
		}

		function remove() {
			if (w.parentNode) w.parentNode.removeChild(w);
			w.className = "waiter";
			w.style.opacity = 0;
		}

		return {
			show: function (state, always) {
				var full = w.className.indexOf("waiterProgress") >= 0;

				if (state) {
					if (t) clearTimeout(t);

					if (count == 0) {
						if (!w.parentNode && always !== false) {
							if (!o.style.position) o.style.position = "relative";
							o.appendChild(w);
						}
						if (full) {
							f.stop(false, true);
							f.start(true, { oe: oMax, os: 0.05 }, true);
						}
					}

					if (!full) t = setTimeout(th, 500);
					count++;
				}
				else {
					if (count > 0) {
						if (count == 1) {
							count = 0;
							if (t) {
								clearTimeout(t);
								t = null;
							}

							if (full) {
								f.stop(false, true);
								f.start(
									true,
									{
										oe: 0,
										os: 0.05,
										handler: remove
									},
									true
								);
							}
							else remove();
						}
						else count--;
					}
				}
			},

			isShow: function () {
				return count > 0;
			}
		};
	}

	function scrollBottom() {
		var os = msgs.onscroll;
		msgs.onscroll = function (e) {
			if (!e) e = window.event;
			if (e.preventDefault) e.preventDefault();
			if (e.stopPropagation) e.stopPropagation();

			return false;
		};
		msgs.scrollLeft = 0;
		msgs.scrollTop = msgs.scrollHeight;
		setTimeout(function () { msgs.onscroll = os; }, 10);
	}

	function insertAtCursor(o, val) {
		if (document.selection) {
			o.focus();
			sel = document.selection.createRange();
			sel.text = val;
		}
		else if (o.selectionStart || o.selectionStart == '0') {
			var startPos = o.selectionStart;
			var endPos = o.selectionEnd;
			o.value = o.value.substring(0, startPos) + val + o.value.substring(endPos, o.value.length);
		}
		else o.value += val;
	}

	var refresh = (function () {
		return function (params, handler) {
			params = params || {};
			params.lastMod = params.lastMod || LASTMOD;

			post(
				window.location.toString(),
				params,
				function (state, status, txt) {
					if (!state) {
						tipUpper(msgsDialog, "Ошибка сервера: " + status);
						txt = undefined;
					}

					if (txt !== undefined) {
						var p = txt.indexOf("\n");
						if (p > 0) {
							var s = /^([a-z]+):(\d+)$/i.exec(txt.substring(0, p)), lm;
							if (s) {
								lm = s[2];
								s = s[1];

								txt = txt.substring(p + 1);

								if (s == "NONMODIFIED") txt = undefined;
								if (s == "OK") lastMod = lm;
							}
						}

						if (txt !== undefined) {
							msgs.innerHTML = txt;
							if (oAS.checked) scrollBottom();

							if (oSND.checked) {
								if (snd) {
									snd.pause();
									snd.currentTime = 0;
									snd.play();
								}
							}
						}
					}

					if (handler) handler(state, status, txt);
				}
			);
		};
	})();

	var poll = (function () {
		var t = null;
		var inProgress = false;

		var rq = function () {
			if (inProgress) return;

			inProgress = true;
			msgsDialogWaiter.show(true, false);
			refresh(
				{ mode: "list" },
				function (state, status, txt) {
					msgsDialogWaiter.show(false);
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

	oAS.onclick = function () {
		if (this.checked) scrollBottom();
	};

	oSND.onclick = function () {
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

	f.onsubmit = function () {
		if (!name.value.trim()) {
			tipUpper(name, "Пожалуйста, введите свое имя");
			return false;
		}

		if (/^\s*$/.test(text.value)) {
			tipUpper(text, "Пожалуйста, введите текст");
			return false;
		}

		sendDialogWaiter.show(true);
		msgsDialogWaiter.show(true, false);

		refresh(
			{
				mode: "post",
				lastMod: 0,
				name: name.value,
				text: text.value
			},
			function (state, status, txt) {
				if (state) {
					text.value = "";
					ah(text);
				}
				sendDialogWaiter.show(false);
				msgsDialogWaiter.show(false);
			}
		);

		return false;
	};

	msgs.onclick = function (e) {
		if (!e) e = window.event;

		var s = e.srcElement || e.target;
		if (s.tagName == "A") return;

		for (var i = 0; i < 4; i++) {
			if (!s || s.className.indexOf("msg") >= 0) break;
			s = s.parentNode;
		}

		if (s) {
			var ps = s.getElementsByTagName("span");
			var name = ps[0].innerText || ps[0].textContent;
			var misc = ps[1].innerText || ps[1].textContent;
			var txt = "";
			s = s.firstChild.nextSibling;
			while (s) {
				if (s.tagName) txt += s.tagName == "BR" ? "\n" : s.innerText || s.textContent;
				else txt += s.nodeValue;
				s = s.nextSibling;
			}

			if (text.value) text.value += "\n";
			text.value += ">" + name + " " + misc + "\n" + txt.replace(/(^|\n)/g, "$1>");
		}
	};

	msgs.onscroll = function () {
		oAS.checked = false;
	};

	name.onkeydown = text.onkeydown = function (e) {
		if (sendDialogWaiter.isShow()) return;
		if (!e) e = window.event;
		if (e.keyCode === 13 && e.ctrlKey) f.onsubmit();
	};

	if (oAS.checked) scrollBottom();

	text.focus();

	poll(false, true);
})();