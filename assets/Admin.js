'use strict';
// native

import {on,refresh/* ,poll */, selectedPosts} from '../script.js';
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


// *Обработка элементов .msg
on(msgs,'click',e=>{
	var t= e.target,
		msg= t.closest('.msg');

		if(!msg) return;

		var num= msg.querySelector('.num').textContent,
		adm= t.closest('.adm'),
		btn;

	if(adm){
		e.stopPropagation();
		e.preventDefault();
		console.log('click on the admin panel', msg);
	}

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
				msg.classList.remove('pinned');
			}
			else{
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

	// *Save edits
	if((btn= t.closest('.saveEdits'))){
		var area= msg.querySelector('.editarea');
		return refresh({
			responseType:'json',
			num: num,
			saveEdits: area.value,
			mode: 'set',
		});
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
}); // /Обработка элементов .msg


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