'use strict';
import {Ajax, scrollIntoView, css, on, off, speak, elemInViewport, sendNotification, tipUpper, autoHeight, logTrace} from './assets/helpers.js';
import * as Adm from './assets/Admin.js';
import * as BB from './assets/BB.js';
import * as State from './assets/State.js';
import * as Img from './assets/Images/Images.js';
import { modal } from './assets/modal/modal.js';
export {Ajax, scrollIntoView, css, on, off, speak};

console.log('Glob server vars', {Chat, LastMod, Out});

export const _w= window;

const msgsDialog = document.getElementById("msgsDialog");
const sendDialog = document.getElementById("sendDialog");

const msgs = document.getElementById("msgsContent");
const f = document.getElementById("sendForm");

if(f){
	var name = f.elements.name,
	text = f.elements.text,
	usersList = document.querySelector('.users'),
	attach= f['attach[]'],
	attachNode= f.querySelector('.attaches>div');
}


// *Чекбоксы
let oSND = document.getElementById("playSound"),
	oNTF = document.getElementById("notifications"),
	oAH = document.getElementById("autoHeight");


function hideName(){
	if(!f) return;

	f.name.value= f.name.value || Chat.name || null;

	f.name.type= f.name.value? 'hidden': 'text';

	console.log(f.name.value);
}

hideName();

if (oAH && oAH.checked) autoHeight(text, 500, true);

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
 * @returns {Promise}
 */
export function refresh(params) {
// function refresh(params, handler) {
	params.lastMod = params.lastMod == 0? 0 : LastMod;

	return Ajax.post(
		_w.location.toString(),
		params
	).then(
		XMLo=>refreshAfter(XMLo)
		, err=>{logTrace('refresh Error',err);}
	);
};


/**
 * *Коллбэк для refresh
 * @param {obj} XMLo - result of ajax
 */
function refreshAfter (XMLo) {
	// logTrace(arguments);

	let success= XMLo.status === 200,
		statusCode= XMLo.status,
		response= XMLo.response;

	if (!success) {
		tipUpper(msgsDialog, "Ошибка сервера: " + statusCode);
		response = undefined;
	}
	else if(!response) {
		console.log('Response after refresh is empty!',{response});
	};

	if (response && response !== undefined) {
		var html= (response instanceof Object)
			? response.html
			: response;

		Object.assign(Chat, response.Chat);

		console.log('Response after refresh', {response}, 'Updated Chat', {Chat});

		hideName();

		let p = html.indexOf("\n");

		if (p > 0) {
			var h = html.substring(0, p).split(':'), lm;

			if (h) {
				lm = +h[1];
				h = h[0];

				html = html.substring(p + 1);

				if (h === "NONMODIFIED") html = undefined;
				if (h === "OK") LastMod = lm;
			}
		}

		console.log('test',{h,lm,html});

		// *if Modifed
		if (html !== undefined) {
			msgsModifed(html);
		}

		// *Every
		State.setDB(response.state)
		.hilightUsers(msgs, usersList);

	}

} //refreshAfter


// *new post
function msgsModifed(html){
	console.log('Content was modifed');
	msgs.innerHTML = html;

	State.handlePosts(msgs);

	scrollBottom();

	Img.init(msgs);

	if (oSND.checked && snd) {
		snd.pause();
		snd.currentTime = 0;
		snd.play()
		.catch((err) => {
			console.log('Аудио не воспроизвелось: \n'+err);
		});
	}

	let lastMsg= msgs.lastElementChild;

	if(document.hidden && oNTF.checked) sendNotification(`Новое сообщение с ${location.host}${location.pathname} от ${lastMsg.querySelector('.name').textContent}`, {
		body: lastMsg.querySelector('.post').textContent,
		icon: './assets/imgs/mail.png',
		dir: 'auto'
	});
}


/**
 * *Long Polling
 * @param {bool} rewait - Ожидание перед запросом
 */
export var poll = (function () {
	var t;

	var rq = function () {
		Chat.on= document.hidden? false: true;

		var data= { chatUser: JSON.stringify(Chat), mode: "list", responseType:'json' };
			// console.log({Chat});
		if ( poll.stop || !Chat.name ) return;

		// msgsDialogWaiter.show(true, false);
		refresh( data ).then(()=>{
			rq(true);
		})
		.catch(err=>{
			logTrace('error in poll',err);
			// rq();
		});
	};

	return function (rewait) {
		if (rewait) {
			if (t) clearTimeout(t);
			t = setTimeout(rq, REFRESHTIME*1000 );
		}
		else rq();
	};
})();


// *Вкл. звук
oSND && (oSND.onchange = function () {
	if (oSND.checked === false) {
		if (snd) {
			snd.pause();
			snd.currentTime = 0;
		}
	}
});

// *Вкл. autoHeight
oAH && (oAH.onclick = function () {
	autoHeight(text, 500, oAH.checked);
});


// *Отправка
function formSubmit (e) {
	if(e){
		e.stopPropagation();
		e.preventDefault();
	}

	if(f.submit.disabled) return false;

	if (!(name.value= name.value.trim())) {
		tipUpper(name, "Пожалуйста, введите свое имя");
		return false;
	}

	// console.log("f['attach[]'].value= ", f['attach[]'].value);

	if (!(text.value= text.value.trim()) && !f['attach[]'].value) {
		tipUpper(text, "Пожалуйста, введите текст");
		return false;
	}

	// *Smiles
	BB.replaceText(f.text);
	// console.log(f.text.value);
	// debugger;

	var fd= new FormData(f);
	fd.append('mode','post');
	fd.append('lastMod',LastMod);
	fd.append('ts', parseInt(Date.now()/1000));
	Chat.on= true;
	fd.append('chatUser', JSON.stringify(Chat));
	fd.responseType= 'json';
	// fd.responseType= 'text/html';

	f.submit.disabled= true;

	refresh( fd	)
	// Ajax.post( location.href, fd	)
	.then(function (XMLo) {
		f.submit.disabled= false;
		scrollIntoView(msgs,{block:'start'});
		text.value = text.textContent = "";
		autoHeight(text);

		// *Очищаем
		f.reset();
		name.value= Chat.name;
		f.appeals.value= '';

		showAttaches();
		countChars.call(f.text);

		State.handlePosts(msgs);
		// refreshAfter(XMLo);
	}).catch(err=>{
		f.submit.disabled= false;
		console.log('Ошибка при отправке: ', err.message);
	});
	return false;
};


on(f,'submit', formSubmit);

on(f,'click',e=>{
	let t= e.target;

	if(t.closest('[type=\'reset\']')){
		e.stopPropagation();
		e.preventDefault();
		modal('Очистить форму?').then(ok=>t.form.reset());
	}
});


// *Scroll to posts/form
let toReadSvg= document.querySelector('.svg-toRead'),
	toBottomSvg= document.querySelector('.svg-toBottom'),
	toFormSvg= document.querySelector('.svg-toForm');

on(_w, 'scroll', e=>{
	if(elemInViewport(msgs, .9)){
		toReadSvg.style.display='none';
		toFormSvg.style.display='';
	}
	else if(elemInViewport(sendDialog, .9)){
		toReadSvg.style.display='';
		toFormSvg.style.display='none';
	}
	// console.log('sendDialog elemInViewport', elemInViewport(sendDialog, true));
})

// console.log('sendDialog elemInViewport', elemInViewport(sendDialog, true));

// *Scroll to msgs
on(toReadSvg, 'click', e=>{
	e.stopPropagation();
	scrollIntoView(msgs,{block:'start'}, e);
});

// *Scroll to bottom post
on(toBottomSvg, 'click', e=>{
	e.stopPropagation();
	scrollIntoView(msgs.lastElementChild,{block:'start'}, e);
});

// *Scroll to form
on(toFormSvg, 'click', e=>{
	e.stopPropagation();
	scrollIntoView(sendDialog.closest('.item-block'),{block:'end'}, e);
	// f.text.focus();
});


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
	addAppeal(msg);
}

function goCite(link,e){
	// e.preventDefault();
	e.stopPropagation();
	link.target= "_self";
	return true;
}

function addAppeal(msg){
	let appeals= f.appeals.value.split(',');

	if(!appeals.includes(msg.dataset.uid)){
		appeals.push(msg.dataset.uid);
	}

	f.appeals.value= appeals.join(',');
}

/* msgs.onscroll = function () {
	// oAS.checked = false;
}; */

name.onkeydown = text.onkeydown = function (e) {
	// if (sendDialogWaiter.isShow()) return;
	if (!e) e = _w.event;
	if (e.keyCode === 13 && e.ctrlKey) formSubmit();
};


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

if(f){
	on(f.text, 'input change', countChars);

	on(f, 'change', function(e){
		showAttaches();
	});
}

// *remove attaches
on(attachNode.parentNode, 'click', function(e) {
	var t= e.target;
	e.preventDefault();

	if(t.closest('.clear')){
		attach.value='';
		showAttaches();
	}
});



// *Пакетная обработка постов
export let selectedPanel= document.querySelector('#selectedPanel'),
	// voiceBtn= selectedPanel.querySelector('.voice'),
	selectedPosts= [];

on(selectedPanel, 'click', e=>{
	e.stopPropagation();
	const t = e.target;

	console.log({t,selectedPosts});

	if(!selectedPosts.length) return;

	let tmp= document.createElement('div');

	if(t.classList.contains('voice')){
		selectedPosts.forEach(p=>{
			let txt= p.querySelector('.post'),
				name= p.querySelector('.name').textContent,
				num= p.querySelector('.num');

			tmp.innerHTML += `Пост ${num.textContent}. ${name}. ${txt.innerHTML}...`;
		});

		[].forEach.call(tmp.querySelectorAll('.cite_disp'), i=>i.remove());

		try {
			tmp.textContent= tmp.textContent.replace(/\p{S}/iug,'');
		} catch (err) {

		}

		speak(tmp.textContent);

		return tmp.remove();
	}

	if(t.classList.contains('reset')){
		selectedPosts.forEach(p=>{
			p.querySelector('.select input').checked= false;
		});
		selectedPosts= [];
		selectedPanel.classList.remove('active');
		return;
	}
});


// *Одиночная обработка постов
on(msgs,'click',e=>{
	e = e || _w.event;

	var t = e.target || e.srcElement,
		msg= t.closest('.msg'),
		select= t.closest('.select'),
		ancor=  t.closest('a[href*=\'#\']'),
		cite= t.closest('.cite'),
		vb= t.closest('.voice');

	if(!msg) return;

	// *Клик по имени
	var name= t.closest('span.name');
	if(name) {
		e.stopPropagation();
		e.preventDefault();

		addAppeal(msg);

		return BB.insert(`[b]@${name.textContent}`,'[/b], ',f.text);
	}

	// *Переход с цитаты к посту
	if(ancor) return goCite(ancor,e);

	// *Вставляем цитату
	if (cite) return addCite(msg,e);

	if(vb) {
		var post= msg.querySelector('.post').cloneNode(true);
		[].forEach.call(post.querySelectorAll('.cite_disp'), i=>i.remove());
		return speak(post.textContent.replace(/\p{S}/iug,''));
	}


	// *Выделяем посты
	if(select){
		select= select.querySelector('input');

		selectedPosts= collectSelected();
	}
});


export function collectSelected(){
	let selectedPosts= Array.from(msgs.querySelectorAll('.select input'));

	selectedPosts= selectedPosts.filter(i=>i.checked);
	selectedPosts= selectedPosts.map(i=>i.closest('.msg'));

	console.log({selectedPosts});

	if(selectedPosts.length){
		selectedPanel.classList.add('active');
	}
	else{
		selectedPanel.classList.remove('active');
	}
	return selectedPosts;
}



// *DOM ready
// on(_w, _w.onpageshow? 'pageshow': 'load', e=>{
on(_w, 'load', e=>{
	poll(true);
	scrollIntoView(msgs, {block:'start'});

	scrollBottom();
	// var msgs= document.querySelectorAll('.msg');
	// smoothScrollTo(msgs[msgs.length-1].offsetTop, 500, document.querySelector('#msgsContent'));

	if(f){
		countChars.call(f.text);

		var panel= BB.createPanel(f.text);
		sendDialog.insertBefore(panel, f.text);
	}

	Img.init(msgs);

	State.handlePosts(msgs);

	// todo
	State.setDB(Out.state)
	.hilightUsers(msgs, usersList);

	showAttaches();

	console.log({Adm});
});
