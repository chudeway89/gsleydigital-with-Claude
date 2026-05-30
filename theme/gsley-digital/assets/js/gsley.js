/* GSLEY Digital — front-end behaviour.
   Mobile nav toggle + scroll-reveal. Ported from the Phase 1 design reference.
   Reveal is progressive enhancement: prefers-reduced-motion shows everything
   immediately (handled in CSS), and without JS nothing is hidden. */
( function () {
	'use strict';

	// Mobile nav toggle.
	document.addEventListener( 'click', function ( ev ) {
		var btn = ev.target.closest( '.nav-toggle' );
		if ( ! btn ) {
			return;
		}
		var menu = document.getElementById( 'mnav' );
		if ( ! menu ) {
			return;
		}
		var open = menu.classList.toggle( 'open' );
		btn.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
	} );

	// Scroll reveal.
	var reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	var targets = document.querySelectorAll( '.reveal' );
	if ( reduce || ! ( 'IntersectionObserver' in window ) ) {
		targets.forEach( function ( el ) {
			el.classList.add( 'in' );
		} );
		return;
	}
	var io = new IntersectionObserver(
		function ( entries ) {
			entries.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					e.target.classList.add( 'in' );
					io.unobserve( e.target );
				}
			} );
		},
		{ threshold: 0.12, rootMargin: '0px 0px -40px 0px' }
	);
	targets.forEach( function ( el ) {
		io.observe( el );
	} );
} )();
