'use strict';
// native

import {on,Ajax,refresh} from '../script.js';

var msgs = document.getElementById("msgsContent");

console.log('Admin module included');

on(msgs,'click',e=>{
	var t= e.target,
		msg= t.closest('.msg'),
		num= msg.querySelector('.num').textContent,
		adm= t.closest('.adm'),
		btn;

	if(adm){
		e.stopPropagation();
		e.preventDefault();
		console.log('click on the admin panel', msg);
	}

	// *Edit post
	if((btn= t.closest('.edit')) && !msg.area){
		msg.area= document.createElement('textarea');
		msg.area.className= 'editarea';
		var save= document.createElement('button');
		save.className= 'saveEdits';
		save.textContent= '💾 SAVE';
		save.title= "Сохранить";
		Ajax.get('?getPost='+num, null, (success,status,resp)=>{
			msg.area.value= resp.text;
		});

		msg.appendChild(msg.area);
		msg.appendChild(save);
	}

	// *Save edits
	if((btn= t.closest('.saveEdits'))){
		var area= msg.querySelector('.editarea');
		refresh({
			num: num,
			saveEdits: area.value,
		}, (success,status,resp)=>{
			area.remove();
		});
	}

	// *Remove post
	if((btn= t.closest('.del'))){
		if(!confirm("Удалить пост "+num+"?")) return;

		refresh({
			removePost: num,
		}, (success,status,resp)=>{
			return null
		});
	}
});