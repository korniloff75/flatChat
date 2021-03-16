'use strict';
import {on} from '../helpers.js';

let templateSel= document.querySelector('#settings .templates');

// todo remove 'click change'
on(templateSel, 'change', e=>{
	let t= e.target;
	fetch('',{
		method:'post',
		/* headers: {
			cookie: 'accessToken=1234abc; userId=1234'
		}, */
		body: JSON.stringify({
			mode: 'set',
			changeTemplate: t.value,
		})
	}).then(()=>location.reload());
});

console.log(Chat.template);

if(Chat.template){
	templateSel.value= Chat.template;
}