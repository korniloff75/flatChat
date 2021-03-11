import {on,Ajax,refresh} from '../../script.js';

var dfr= document.createDocumentFragment(),
	wrapper= document.createElement('div'),
	modal= document.createElement('div'),
	content= document.createElement('div'),
	close= document.createElement('button'),
	ok= document.createElement('button'),
	style= document.createElement('link');

// style.href= document.currentScript.src + '/style.css';
style.href= './assets/modal/style.css';
style.rel= 'stylesheet';
wrapper.className= 'modal-overlay closed';
modal.className= 'modal';
content.className= 'modal-guts';
close.className= 'close-button';
ok.className= 'ok-button';

close.textContent= 'X';
// ok.textContent= '✅';
ok.textContent= '☑';

modal.appendChild(close);
modal.appendChild(content);
dfr.appendChild(style);
wrapper.appendChild(modal);
dfr.appendChild(wrapper);

document.body.appendChild(dfr);


/* window.confirm= function(txt){
	content.innerHTML= `<pre>${txt}</pre>`;
	content.appendChild(ok);
	wrapper.classList.remove('closed');
}

on(modal,'click',e=>{
	if(e.target == ok)
}) */


// *Закрываем окно
on(wrapper,'click',closeModal);
on(close,'click',closeModal);

function closeModal(e){
	if (e.target !== e.currentTarget) return;

	e.stopPropagation();
	wrapper.classList.add('closed')
}


/* content.innerHTML= `<h1>Заголовок</h1>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repudiandae expedita corrupti laudantium aperiam, doloremque explicabo ipsum earum dicta saepe delectus totam vitae ipsam doloribus et obcaecati facilis eius assumenda, cumque.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Repudiandae expedita corrupti laudantium aperiam, doloremque explicabo ipsum earum dicta saepe delectus totam vitae ipsam doloribus et obcaecati facilis eius assumenda, cumque.</p>`; */