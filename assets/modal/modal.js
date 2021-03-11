import {on,Ajax,refresh} from '../../script.js';

var dfr= document.createDocumentFragment(),
	wrapper= document.createElement('div'),
	style= document.createElement('link');

style.href= 'style.css';
wrapper.className= 'modal-overlay';

dfr.appendChild(style);
dfr.appendChild(wrapper);
document.body.appendChild(dfr);