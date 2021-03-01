'use strict';
// native

var bbs= ['B','I','U','S'];

var codes= {
	':)': '😁',
	';)': '😉',
	':D': '😂',
	':(': '😕',
	':\'(': '😢',
	':*': '😘',
}

/**
 *
 * @param {node} ta -- textarea
 * @return {node} panel
 */
export function createPanel (ta){
	var p = document.createElement('div'),
		smiles= document.createElement('div'),
		bb= document.createElement('div');

	p.className= 'smile';
	smiles.className= 'sm';
	bb.className= 'bb';

	bbs.forEach(i=>{
		var b= document.createElement('i');
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

	p.appendChild(bb);
	p.appendChild(smiles);

	p.addEventListener('click',e=>{
		var t= e.target;
		if(t.closest('.bb')){
			var c= t.textContent.toLowerCase();
			insert('['+c+']', '[/'+c+']', ta);
		}
		else if(t.closest('.sm')){
			insert(' ' + t.textContent + ' ', '', ta);
		}
	});

	return p;
}

function replace (txt) {
	// Duples
	codes= Object.assign(codes, {
		'=)': codes[':)'],
		':))': codes[':D'],
	});
	Object.keys(codes).forEach(i=>{
		txt= txt.replace(i, codes[i], 'g');
	});
	console.log({codes});
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