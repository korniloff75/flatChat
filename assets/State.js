'use strict';

import { on } from "../script.js";
import { Ajax,getUTC } from "./helpers.js";

// native

/**
 * REFRESHTIME, Chat - defined in index.php
 */

var db={},
	_w= window,
	users,
	appeals= document.querySelector('.appeals');

// console.log({Chat});

export function setDB (state){
	db= state;
	users= db.users;
	return this;
}


export function defineUID (name,IP){
	var dotPos= IP.lastIndexOf('.');
	return name + IP.substring(0,dotPos+1);
}


/**
 * *Выделение постов пользователя
 * после отправки и появления новых постов
 * !deprecated
 * todo- add appeals, перенести в handlePosts
 * @param {Node} msgs
 */
/* export function findMyPosts (msgs) {
	msgs.querySelectorAll(`div[data-uid]`).forEach(msg=>{
		var uid= msg.dataset.uid;

		([Chat.myUID, Chat.UID].includes(uid)) && msg.classList.add('myPost');
		// console.log(uid, [Chat.myUID, Chat.UID].includes(uid));
	});
} */


/**
 * *Handle posts after update content
 *
 * @param {HTMLElement} msgs
 */
export function handlePosts (msgs) {
	let dfr= document.createDocumentFragment();

	msgs.querySelectorAll(`div[data-uid]`).forEach(msg=>{
		var uid= msg.dataset.uid,
			appealsList= msg.dataset.appeals.split(',');

		// *Выделение постов пользователя
		[Chat.myUID, Chat.UID].includes(uid) && msg.classList.add('myPost');

		// console.log({appealsList},Chat.UID);

		// *Обращения
		if(appealsList.includes(Chat.UID)){
			let post= msg.querySelector('.post').cloneNode(true),
				name= msg.querySelector('span.name'),
				num= msg.querySelector('.num');

			post.insertAdjacentHTML('afterbegin', `<p><a href="#${msg.id}">${num.textContent}</a> from <b>${name.textContent}</b></p>`);
			post.insertAdjacentHTML('beforeend', `<hr>`);
			// dfr.appendChild(post);
			dfr.insertBefore(post, dfr.firstElementChild);
		}
		// console.log(uid, [Chat.myUID, Chat.UID].includes(uid));
	});

	appeals.innerHTML='';
	appeals.appendChild(dfr);
}


/**
 * *Hilight online users and fill the users list
 * every server response (with/without update content)
 * @param {HTMLElement} box
 * @param {HTMLElement} listNode
 */
export function hilightUsers (box, listNode){
	if(!users) return;

	var uInfo= box.querySelectorAll('.info');

	addToUsersList(listNode);

	// *Перебираем шапки постов
	uInfo.forEach(i=>{
		var name= i.querySelector('span.name'),
			stateElement= i.querySelector('span.state'), //deprecated
			IP= i.dataset.ip,
			uid= defineUID(name.textContent,IP);

		if(!users[uid]) return;

		if(users[uid].on){
			stateElement && stateElement.classList.add('on');
			name.classList.add('on');
		}
		else{
			stateElement && stateElement.classList.remove('on');
			name.classList.remove('on');
		}
		// console.log({users, uid}, users[uid]);
		// debugger;
	});

	console.log({users});
	return this;
}


// *Текущие пользователи
function addToUsersList (listNode) {
		var dfr= document.createDocumentFragment(),
			now= Date.now()/1000; //sec;

		Object.keys(users).forEach(uid=>{
			var uData= users[uid];

			if(!uData.name) return;

			// deprecated
			/* if(users[uid].on === undefined){
				let absence = now - users[uid].ts;
				users[uid].on= absence < REFRESHTIME/1000 * 2;
			} */

			var p= document.createElement('p'),
				d= new Date(uData.ts*1000);

			p.textContent= uData.name;

			console.log({uData});

			if(uData.on){
				p.classList.add('on');
			}
			else{
				p.classList.remove('on');
				p.innerHTML+= ` <span class="date">(${getUTC(d)})</span>`;
			}
			dfr.appendChild(p);
		});

		listNode.innerHTML='';
		listNode.appendChild(dfr);

	// console.log({node});
}


// *Выход по закрытию вкладки
on(_w, 'unload', e=>{
	Chat.on= false;


	Ajax.post('',{
		// mode: 'status',
		mode: 'post',
		name: Chat.name,
		text: `Пользователь [b]${Chat.name}[/b] покинул чат.`,
		chatUser: JSON.stringify(Chat),
	}).then(oXML=>console.log(oXML));
});