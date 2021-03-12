import {on,Ajax,refresh} from '../../script.js';

var dfr= document.createDocumentFragment(),
	wrapper= document.createElement('div'),
	modalWin= document.createElement('div'),
	content= document.createElement('div'),
	close= document.createElement('button'),
	ok= document.createElement('button'),
	style= document.createElement('link');

// style.href= document.currentScript.src + '/style.css';
style.href= '/assets/modal/style.css';
style.rel= 'stylesheet';
wrapper.className= 'modal-overlay closed';
modalWin.className= 'modal';
content.className= 'modal-guts';
close.className= 'close-button';
ok.className= 'ok-button';

close.textContent= 'X';
// ok.textContent= '✅';
ok.textContent= '☑';

modalWin.appendChild(close);
modalWin.appendChild(ok);
modalWin.appendChild(content);
wrapper.appendChild(modalWin);

dfr.appendChild(style);
dfr.appendChild(wrapper);
document.body.appendChild(dfr);


// *Закрываем окно
// on(wrapper,'click',closeModal);
// on(close,'click',closeModal);

function closeModal(e){
	if (e.target !== e.currentTarget) return;

	e.stopPropagation();
	wrapper.classList.add('closed')
}

/* content.innerHTML= `<h1>Заголовок</h1>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repudiandae expedita corrupti laudantium aperiam, doloremque explicabo ipsum earum dicta saepe delectus totam vitae ipsam doloribus et obcaecati facilis eius assumenda, cumque.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repudiandae expedita corrupti laudantium aperiam, doloremque explicabo ipsum earum dicta saepe delectus totam vitae ipsam doloribus et obcaecati facilis eius assumenda, cumque.</p>`; */


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

	content.innerHTML+= `<pre>${txt}</pre>`;

	wrapper.classList.remove('closed');
	return new Promise((resolve,reject)=>{
		on(wrapper,'click',e=>{
			e.stopPropagation();
			var t= e.target;
			console.log({t,ok});

			if(t === ok){
				resolve(123);
				wrapper.classList.add('closed');
			}
			else if(t === close || t === wrapper ){
				reject(321);
				wrapper.classList.add('closed');
			}

		})
	})
}

// ?test
