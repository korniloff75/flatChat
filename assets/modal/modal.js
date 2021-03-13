import {on,off,refresh} from '../../script.js';

var dfr= document.createDocumentFragment(),
	wrapper= document.createElement('div'),
	modalWin= document.createElement('div'),
	content= document.createElement('div'),
	close= document.createElement('button'),
	ok= document.createElement('button'),
	style= document.createElement('link');

// style.href= document.currentScript.src + '/style.css';
style.href= './assets/modal/style.css';
style.rel= 'stylesheet';
wrapper.className= 'modal-overlay closed';
modalWin.className= 'modal';
content.className= 'modal-guts';
close.className= 'close-button';
ok.className= 'ok-button';

close.textContent= '❌';
close.title= 'Отмена';
// ok.textContent= '✅';
ok.textContent= '☑';
ok.title= 'Подтвердить';

modalWin.appendChild(close);
modalWin.appendChild(ok);
modalWin.appendChild(content);
wrapper.appendChild(modalWin);

dfr.appendChild(style);
dfr.appendChild(wrapper);
document.body.appendChild(dfr);


/**
 *
 * @param {string} txt
 * @param {obj} opts
 * @returns {Promise}
 *
 * modal('Вы уверены?').then(ok=>{},fail=>{});
 */
export function modal(txt, opts){
	if(!(txt= txt.trim())) new Error("Нет текста для отображения");

	content.innerHTML= `<pre>${txt}</pre>`;

	wrapper.classList.remove('closed');
	return new Promise((resolve,reject)=>{
		wrapper.onclick= resolve;
		wrapper.onkeydown= e=>{
			console.log(e.key);
		}
	}).then(e=>{
		e.stopPropagation();
		var t= e.target;
		console.log('wrapper was clicked!!!', {t,ok});

		if(t === ok){
			wrapper.classList.add('closed');
			console.log('resolve');
			return Promise.resolve();
		}
		else if(t === close || t === wrapper ){
			wrapper.classList.add('closed');
			console.log('reject');
			return Promise.reject();
		}
	})
}
