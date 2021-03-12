'use strict';
// native

import {on,Ajax,refresh,poll} from '../script.js';
import {modal} from './modal/modal.js';

var _w= window,
	msgs = document.getElementById("msgsContent");

console.log('Admin module included', {msgs});

// *logOut
var logoutBtn= document.querySelector('.logout');

logoutBtn && on(logoutBtn, 'click', e=>{
	// if(!confirm("Вы точно хотите выйти\nиз своей учётной записи?")) return;

	return modal("Вы точно хотите выйти\nиз своей учётной записи?")
		.then(ok=>{
			poll.stop=1;
			Ajax.post('', {
				logOut: true,
			}, ()=>_w.location.reload());
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
		Ajax.get('?getPost='+num, null, (success,status,resp)=>{
			msg.area.value= resp.text;
		});

		msg.appendChild(msg.area);
		msg.appendChild(save);
		return;
	}

	// *Save edits
	if((btn= t.closest('.saveEdits'))){
		var area= msg.querySelector('.editarea');
		return refresh({
			num: num,
			saveEdits: area.value,
		});
	}

	// *Remove post
	if((btn= t.closest('.del'))){
		// if(!confirm("Удалить пост "+num+"?")) return;

	return modal("Удалить пост "+num+"?")
		.then(ok=>{
			refresh({
				removePost: num,
			})
		}, err=>{
			return new Error(`Пост №${num} не был удалён`);
		})
	}
}); // /Обработка элементов .msg