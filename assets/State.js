'use strict';

// import { on } from "../script.js";
import { Ajax,getUTC } from "./helpers.js";

// native

/**
 * REFRESHTIME, Chat - defined in index.php
 */

var _w= window,
	users,
	online,
	appeals= document.querySelector('.appeals');


export function setDB (Out){
	users= Out.state.users;
	online= Out.online;
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
		// if(users[uid].ts > (Date.now() - REFRESHTIME*2)){
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

	// console.log({users});
	return this;
}


// *Текущие пользователи за timeRange сек.
function addToUsersList (listNode) {
	if(!listNode){
		console.log('listNode is empty');
		return;
	}

	// *
	const timeRange = 24*3600;

	const dfr= document.createDocumentFragment(),
		now= Date.now()/1000; //sec;

	Object.keys(online).forEach(uid=>{
		var uData= users[uid];

		// console.log('beforeFilter',{uData});

		if(
			!uData
			|| !uData.name
			|| (now - (uData.ts= online[uid].ts)) > timeRange
		) return;

		uData.on= uData.ts + REFRESHTIME > now;
		uData.check= {now, restTime: (uData.ts + REFRESHTIME - now), note:'If restTime > 0 uData.on=true'};

		console.log('afterFilter',{uData});

		var p= document.createElement('p'),
			d= getUTC(uData.ts*1000);

		p.textContent= uData.name;

		if(uData.on){
			p.classList.add('on');
		}
		else {
			p.classList.remove('on');
			p.innerHTML+= ` <span class="date">(${d})</span>`;
		}

		if(uData.ban){
			p.classList.add('banned');
		}

		dfr.appendChild(p);
	});

	console.log('addToUsersList',{users});

	listNode.innerHTML='';
	listNode.appendChild(dfr);

// console.log({node});
}
