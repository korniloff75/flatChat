import {on} from '../helpers.js';

let templateSel= document.querySelector('#settings .templates');

// todo remove click
on(templateSel, 'click change', e=>{
	let t= e.target;
	fetch('',{
		method:'post',
		body: JSON.stringify({
			mode: 'set',
			changeTemplate: t.value,
		})
	})
});

console.log(Chat.template);
if(Chat.template){
	templateSel.value= Chat.template;
}