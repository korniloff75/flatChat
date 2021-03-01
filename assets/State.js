'use strict';
// native

/**
 * REFRESHTIME, Chat - defined in index.php
 */

var db={};

export function set (state){
	db= state;
	return this;
}


export function defineUID (name,IP){
	var dotPos= IP.lastIndexOf('.');
	return name + IP.substring(0,dotPos+1);
}


// *Выделение постов пользователя
export function findMyPosts (msgs) {
	msgs.querySelectorAll(`div[data-uid]`).forEach(msg=>{
		var uid= msg.getAttribute('data-uid');

		([Chat.myUID, Chat.UID].includes(uid)) && msg.classList.add('myPost');
		// console.log(uid, [Chat.myUID, Chat.UID].includes(uid));
	});
}


export function hilightUsers (box){
	var uInfo= box.querySelectorAll('.info'),
		users= db.users,
		now= Date.now()/1000,
		keysDB;

	if(!users) return;

	Object.keys(users).forEach(uid=>{
		// *Online
		users[uid].on= now - users[uid].ts < REFRESHTIME/1000 * 2;
		// console.log(uid, now - users[uid].ts < REFRESHTIME/1000 * 2);
	});


	uInfo.forEach(i=>{
		var name= i.querySelector('span.name').textContent,
			ipElement= i.querySelector('span.ip'),
			IP= ipElement.textContent,
			uid= defineUID(name,IP);

			if(!users[uid]) return;

			if(users[uid].on){
				ipElement.classList.add('on');
			}
			else{
				ipElement.classList.remove('on');
			}
				// console.log({users, uid}, users[uid]);
				// debugger;
	});

	console.log({users});
	return this;
}