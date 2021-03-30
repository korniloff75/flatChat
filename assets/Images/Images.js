// ?
import {css,on} from '../../script.js';
import {addStyle, elemInViewport} from '../helpers.js';
export var inited;

/**
 *
 * @param {node} box - Блок с изображениями
 * document.querySelector('#msgsContent');
 */
export function init(box) {
	let ims = box.querySelectorAll('img');

	// *Подгрузка изображений во вьюпорте
	showVisible(ims);

	if (inited || !ims.length) return;

	inited= 1;

	const boxSelector= box.id? '#'+box.id: '.'+box.className.split(' ').join('.');

	addStyle('./assets/Images/Images.css');
	css(ims, { cursor: 'zoom-in' });

	on(box,'scroll',e=>showVisible(box.querySelectorAll('img')));

	let
		mw = document.createElement('div');

	mw.id = "$mw";

	mw.insertAdjacentHTML('beforeend', `<img draggable="false">
	<div class='close'>❌</div>`);

	let img = mw.querySelector('img'),
		close = mw.querySelector('.close');

	css(mw, {
		height: window.innerHeight + 'px',
	});

	mw.classList.remove('mod-show');

	img.draggable = false;
	css(img, { cursor: 'zoom-out', margin: 'auto' });

	/* css(close, {
		position: 'absolute',
		right: 0, top: 0,
		color: '#fff',
		background: '#f33',
		padding: '.3em .5em',
		cursor: 'pointer',
		borderRadius: '100%',
		border: '2px solid',
		font: 'bold 1em sans-serif',
	});
	close.textContent = '❌'; */

	on(close, 'click', e => {
		mw.classList.remove('mod-show');
	});

	on(img, 'click', e => {
		e.stopPropagation();
		e.preventDefault();
		mw.classList.remove('mod-show');
	});


	mw.appendChild(img);
	mw.appendChild(close);
	document.body.appendChild(mw);

	on(box, 'click', e => {
		let t = e.target;
		if (t.tagName !== 'IMG') return;

		img.src = t.getAttribute('data-fullsrc') || t.src

		mw.classList.add('mod-show');

		let gcs = getComputedStyle(t);

		// Убираем маленькие изображения
		if (parseInt(gcs.width) < 100) return;

		// console.log(t, gcs, parseInt(gcs.width));
		console.log(window.innerHeight / window.innerWidth, parseInt(gcs.height) / parseInt(gcs.width));

		// *Определение пропорций изображения
		if (
			window.innerHeight / window.innerWidth >= parseInt(gcs.height) / parseInt(gcs.width)
		) {
			css(img, {
				width: '100%',
				height: 'auto',
			})
		}
		else {
			css(img, {
				height: window.innerHeight + 'px',
				width: 'auto',
			})
		}
	});


	// *Arrows

	addEventListener('keydown', function (e) {
		// console.log(e.key);
		switch (e.key) {
			case 'Escape':
				mw.classList.remove('mod-show');
				break;
			case 'ArrowUp':
				// up arrow
				break;
			case 'ArrowDown':
				// down arrow
				break;
			case 'ArrowLeft':
				// left arrow
				break;
			case 'ArrowRight':
				break;
		}
	});

}


/**
 *
 * @param {nodeList} imgs
 */
function showVisible(ims) {
	for (let img of ims) {
		let realSrc = img.dataset.src;
		if (!realSrc) continue;

		if (realSrc && elemInViewport(img, .3)) {
			console.log({img});
			img.src = realSrc;
			img.dataset.src = '';
		}
	}
	// console.log(ims[0],ims);
}