'use strict';
import {on} from '../helpers.js';

let templateSel= document.querySelector('#settings .templates');


on(templateSel, 'change', e=>{
	let t= e.target;
	fetch('',{
		method:'post',
		body: JSON.stringify({
			mode: 'set',
			changeTemplate: t.value,
		})
	}).then(()=>{
		Chat.template= templateSel.value;
		location.reload();
	});
});

templateSel.value= Chat.template || '_default_';
console.log(Chat.template);
