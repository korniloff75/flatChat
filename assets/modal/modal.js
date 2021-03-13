// import {on,off,refresh} from '../../script.js';
import { addStyle } from '../helpers.js';

const wrapper= document.createElement('div');

wrapper.className= 'modal-overlay closed';

wrapper.insertAdjacentHTML('afterbegin', `
<div class='modal'>
	<button class="close-button" title="Отмена">❌</button>
	<button class="ok-button" title="Подтвердить">☑</button>
	<div class="modal-guts"></div>
</div>
`);


addStyle('./assets/modal/style.css');

const close= wrapper.querySelector('.close-button'),
	ok= wrapper.querySelector('.ok-button')

document.body.appendChild(wrapper);


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

	wrapper.querySelector('.modal-guts').innerHTML= `<pre>${txt}</pre>`;

	wrapper.classList.remove('closed');

	return new Promise((resolve,reject)=>{
		wrapper.onclick= clickHandler.bind(null,resolve,reject);
		// not worked
		wrapper.onkeydown= e=>{
			console.log(e.key);
		}
	})
}

function clickHandler(resolve,reject,e){
	e.stopPropagation();
	const t= e.target;

	if(t === ok){
		wrapper.classList.add('closed');
		console.log('modal resolved');
		return resolve(ok);
	}
	else if(t === close || t === wrapper ){
		wrapper.classList.add('closed');
		console.log('modal be closed', {t,ok});
		return reject(close);
	}
}
