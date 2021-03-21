'use strict';
// native

import {on, SpeechRecognition, prinText} from './helpers.js';

var bbs= ['B','I','U','S','❞'];

var codes= {
	':)': '😁',
	';)': '😉',
	':D': '😂',
	':*': '😘',
	':Р': '😋',
	'В)': '😎',
	':(': '😕',
	':\'(': '😢',
	':\\': '😒',
}

/**
 *
 * @param {node} ta -- textarea
 * @return {node} panel
 */
export function createPanel (ta){
	const p = document.createElement('div');
	p.className= 'smile';

	let panelHtml= `<div class='bb'></div>
	<div class='sm'>
	</div>
	`;

	if(SpeechRecognition){
		panelHtml+= "<div class='recognition button' title='Голосовой ввод'>🎤</div>";
	}

	p.insertAdjacentHTML('beforeend',panelHtml);


	const smiles= p.querySelector('.sm'),
		bb= p.querySelector('.bb'),
		recognition= p.querySelector('.recognition');


	bbs.forEach(i=>{
		let b= document.createElement('i');
		b.textContent= i;
		switch (i) {
			case 'B':
				b.style.fontWeight= 900;
				break;
			case 'I':
				b.style.fontStyle= 'italic';
				break;
			case 'U':
				b.style.textDecoration= 'underline';
				break;
			case 'S':
				b.style.textDecoration= 'line-through';
				break;
			case '❞':
				b.bb= 'cite';
				// b.style.textDecoration= 'line-through';
				break;

			default:
				break;
		}
		bb.appendChild(b);
	});

	Object.keys(codes).forEach(i=>{
		var sb= document.createElement('i');
		sb.textContent= codes[i];
		smiles.appendChild(sb);
	});

	on(p,'click',e=>{
		var t= e.target;
		if(t.closest('.bb')){
			var c= (t.bb || t.textContent).toLowerCase();
			insert('['+c+']', '[/'+c+']', ta);
		}
		else if(t.closest('.sm')){
			insert(' ' + t.textContent + ' ', '', ta);
		}
		// *Распознаём звук
		else if(t.closest('.recognition')){
			prinText().then(txt=>{
				console.log(txt);
				insert(' ' + txt + ' ', '', ta);
			}, err=>false);
		}
	});

	return p;
}


function replace (txt) {
	// Duples
	codes= Object.assign(codes, {
		'=)': codes[':)'],
		':))': codes[':D'],
		':/': codes[':\\'],
	});
	Object.keys(codes).forEach(i=>{
		var r= i.replace(/([\(\)\/\*\\])/g,"\\$1");
		// console.log({i,r});

		txt= txt.replace(new RegExp(`(^|\\s)${r}(\\s|$)`, 'gm'), `$1${codes[i]}$2`, 'g');
	});
	// console.log({codes});
	return txt;
}


export function replaceHTML (node) {
	node.innerHTML= replace(node.innerHTML);
}


export function replaceText (node) {
	var prop= node.value !== undefined? 'value': 'textContent';
	if(!node[prop]) return;

	node[prop]= replace(node[prop]);
}


export function insert(start, end, element) {
	element.focus();

	if (document.selection) {
		var sel = document.selection.createRange() || 0;
		sel.text = start + sel.text + end;
	} else if (element.selectionStart !== undefined) {
		var startPos = element.selectionStart, endPos = element.selectionEnd;

		element.value = element.value.substring(0, startPos) + start + element.value.substring(startPos, endPos) + end + element.value.substring(endPos, element.value.length);
		//== Возвращаем курсор в конец вставленного фрагмента
		var karet= endPos + start.length + end.length;
		element.setSelectionRange(karet,karet);
	} else {
		element.value += start + end;
	}

};