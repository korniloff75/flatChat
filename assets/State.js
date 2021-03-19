'use strict';
// native

/**
 * REFRESHTIME, Chat - defined in index.php
 */

var db={},
	users;

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


// *Выделение постов пользователя
export function findMyPosts (msgs) {
	msgs.querySelectorAll(`div[data-uid]`).forEach(msg=>{
		var uid= msg.dataset.uid;

		([Chat.myUID, Chat.UID].includes(uid)) && msg.classList.add('myPost');
		// console.log(uid, [Chat.myUID, Chat.UID].includes(uid));
	});
}


/**
 * *Hilight online users and fill the user list
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
			stateElement= i.querySelector('span.state'),
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

		// *
		// console.log(i);
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

			var absence = now - users[uid].ts;

			// *Online
			users[uid].on= absence < REFRESHTIME/1000 * 2;

			var p= document.createElement('p'),
				d= new Date(uData.ts*1000);

			p.textContent= uData.name;

			if(uData.on){
				p.classList.add('on');
			}
			else{
				p.classList.remove('on');
				p.innerHTML+= ` <span class="date">(${d.getFullYear()}-${fixZero(d.getMonth()+1)}-${fixZero(d.getDate())} ${fixZero(d.getHours())}:${fixZero(d.getMinutes())})</span>`;
			}
			dfr.appendChild(p);
		});

		listNode.innerHTML='';
		listNode.appendChild(dfr);

	// console.log({node});
}

function fixZero (num) {
	return num < 10? '0'+num: num;
}