import {on} from '../helpers.js';

// let templateSel= document.querySelector();

on('#settings .templates', 'click change', e=>{
	let t= e.target;
	fetch('',{
		method:'post',
		body: JSON.stringify({
			mode: 'set',
			changeTemplate: t.value,
		})
	})
});