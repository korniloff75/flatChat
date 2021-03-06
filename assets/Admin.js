'use strict';
// native

import {on,refresh/* ,poll */, selectedPosts} from '../script.js';
// import { css } from './helpers.js';
import {modal} from './modal/modal.js';

var _w= window,
	msgs = document.getElementById("msgsContent");

console.log('Admin module included', {msgs});

// *logOut
var logoutBtn= document.querySelector('.logout');

logoutBtn && on(logoutBtn, 'click', e=>{
	return modal("Вы точно хотите выйти\nиз своей учётной записи?")
	.then(ok=>{
		// poll.stop=1;
		fetch('', {
			method: 'set',
			body: JSON.stringify({logOut: true}),
		}).then(_w.location.reload.bind(location));
	}, err=>false)
});


// *Обработка кликов по админ-панели
on(msgs,'click',e=>{
	const t= e.target,
		msg= t.closest('.msg'),
		num= msg&&msg.querySelector('.num').textContent,
		adm= t.closest('.adm');

	let btn;

	if(adm){
		e.stopPropagation();
		e.preventDefault();
		console.log('click on the admin panel', adm);
	}
	// *Save edits
	else if((btn= t.closest('.saveEdits'))){
		let area= msg.querySelector('.editarea');
		return refresh({
			responseType:'json',
			num: num,
			saveEdits: area.value,
			mode: 'set',
		});
	}
	else return;


	const
		name=  msg.querySelector('.name').textContent,
		UID= msg.dataset.uid;


	// *Pin post
	if((btn= t.closest('.pin'))){
		let pinned= msg.classList.contains('pinned');
		return fetch('', {
			method:'post',
			body: JSON.stringify({
				mode: pinned? 'remove': 'set',
				pinPost: num,
			}),
		}).then(ok=>{
			if(pinned){
				btn.title= 'Закрепить';
				msg.classList.remove('pinned');
			}
			else{
				btn.title= 'Открепить';
				let exists= msgs.querySelectorAll('.pinned');
				exists && exists.forEach(p=>p.classList.remove('pinned'));
				msg.classList.add('pinned');
				msg.classList.remove('myPost');
			}

		});
	}

	// *Edit post
	if((btn= t.closest('.edit'))){
		if(msg.area) {
			// console.log(msg.area);
			msg.area.remove();
			msg.save.remove();
			msg.area= null;
			return;
		}

		msg.area= document.createElement('textarea');
		msg.area.className= 'editarea';
		var save= msg.save= document.createElement('button');
		save.className= 'saveEdits';
		save.textContent= '💾 SAVE';
		save.title= "Сохранить";

		fetch('?getPost='+num)
		.then(resp=>{
			 return resp.json();
		}).then(json=>msg.area.value=json.text);

		msg.appendChild(msg.area);
		msg.appendChild(save);
		return;
	}

	// *Remove post
	if((btn= t.closest('.del'))){
		// if(!confirm("Удалить пост "+num+"?")) return;

		return modal("Удалить пост "+num+"?")
		.then(ok=>{
			refresh({
				responseType:'json',
				removePost: num,
			})
		}, err=>{
			return new Error(`Пост №${num} не был удалён`);
		})
	}

	// *BAN
	if((btn= t.closest('.ban'))){
		let banned= msg.classList.contains('banned');

		return modal(`${banned?'Разблокировать':'Заблокировать'} пользователя ${name}?`)
		.then(ok=>{
			refresh({
				responseType:'json',
				mode:'set',
				bool: banned? 0:1,
				banUser: UID,
			})
		}, err=>{
			return new Error(`Ошибка бана пользователя ${name}`);
		})
	}
}); // Админ-панель



//* Обработка пакетного выбора
let selectedPanel= document.querySelector('#selectedPanel');
	// selectedPosts= [];

on(selectedPanel, 'click', e=>{
	e.stopPropagation();
	const t = e.target;

	console.log({t,selectedPosts});

	if(!selectedPosts.length) return;

	let tmp= document.createElement('div');

	// *Удаление
	if(t.classList.contains('del')){
		let nums=[];
		selectedPosts.forEach(p=>{
			nums.push(+p.querySelector('.num').textContent);
		});

		console.log({nums});

		modal("Удалить выбранные посты " + nums + '?')
		.then(ok=>{
			refresh({
				responseType:'json',
				removePost: JSON.stringify(nums),
			})
		}, err=>{
			return new Error(`Посты №${nums} не были удалены!`);
		});

		selectedPanel.classList.remove('active');
		return;
	}
});

// *
/* on(selectedPanel, 'click', e=>{
	let t= e.target;

	if(t.classList.contains('del')){
		console.log({t}, collectSelected());
	}
}); */