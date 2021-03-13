'use strict';

let _w= window;

/**
 * Прокрутка к элементу
 * @param {Node} el 
 * @param {obj} opts 
 * optional @param {Event} e 
 */
export function scrollIntoView(el,opts,e){
	console.log(Element);
	e && e.preventDefault();

	opts= Object.assign({
		behavior: 'smooth',
		block: 'start',
		inline: 'center'
	}, (opts || {}));

	try {
		el.scrollIntoView(opts);
	} catch (err) {
		// location.replace(`#${el.id}`);
		smoothScrollTo(el.offsetTop, 500, el);
	}
	return false;
}


var smoothScrollTo = (function (_w) {

	var timer, start, factor;

	return function (targetY, duration, el) {
		el= el || _w;
		var offset = el.pageYOffset,
				delta  = targetY - el.pageYOffset; // Y-offset difference
		duration = duration || 1000;              // default 1 sec animation
		start = Date.now();                       // get start time
		factor = 0;

		if( timer ) {
			clearInterval(timer); // stop any running animations
		}

		function step() {
			var y;
			factor = (Date.now() - start) / duration; // get interpolation factor
			if( factor >= 1 ) {
				clearInterval(timer); // stop animation
				factor = 1;           // clip to max 1.0
			}
			y = factor * delta + offset;
			el.scrollBy(0, y - el.pageYOffset);
		}

		timer = setInterval(step, 50);
		return timer;
	};
})(window);


export function css (els, cssObj) {
	if(!els.length) els=[els];

	[].forEach.call(els, el => {
		// console.log({el});
		Object.keys(cssObj).forEach(st=>{
			el.style[st]= cssObj[st];
			// console.log(st,el.style[st]);
		});
	});
}

export function on(obj, event, handler) {
	if(!obj){
		return;
	}
	if (typeof (obj.addEventListener) != 'undefined') obj.addEventListener(event, handler, true);
	else if (typeof (obj.attachEvent) != 'undefined') obj.attachEvent('on' + event, handler, true);
}

export function off(obj, event, handler) {
	if (typeof (obj.removeEventListener) != 'undefined') obj.removeEventListener(event, handler, true);
	else if (typeof (obj.detachEvent) != 'undefined') obj.detachEvent('on' + event, handler);
}