/* GSLEY Digital — front-end behaviour (v2, ported from redesign/site.js).
   - Mobile menu: full-height panel, scroll lock, Escape closes, links close,
     focus moves into the menu and back to the toggle.
   - Mobile action bar: shown after 480px of scroll (below 768px only, via CSS).
   - Active nav state, scroll-reveal, and the Contact brief form.
   Contact details, counts and the booking link are resolved on the server
   (GSLEY Settings), so nothing here hard-codes a fact. */
( function () {
	'use strict';
	var d = document;

	/* ---------- mobile menu ---------- */
	var tg = d.querySelector( '.nav-toggle' );
	var mn = d.getElementById( 'mnav' );
	function setNav( open ) {
		if ( ! tg || ! mn ) {
			return;
		}
		mn.classList.toggle( 'open', open );
		tg.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
		tg.setAttribute( 'aria-label', open ? 'Close menu' : 'Open menu' );
		d.body.classList.toggle( 'nav-open', open );
		if ( open ) {
			var f = mn.querySelector( 'a' );
			if ( f ) {
				f.focus();
			}
		}
	}
	if ( tg && mn ) {
		tg.addEventListener( 'click', function () {
			setNav( ! mn.classList.contains( 'open' ) );
		} );
		mn.addEventListener( 'click', function ( e ) {
			if ( e.target.closest( 'a' ) ) {
				setNav( false );
			}
		} );
		d.addEventListener( 'keydown', function ( e ) {
			if ( 'Escape' === e.key && mn.classList.contains( 'open' ) ) {
				setNav( false );
				tg.focus();
			}
		} );
		window.addEventListener( 'resize', function () {
			if ( window.innerWidth >= 768 && mn.classList.contains( 'open' ) ) {
				setNav( false );
			}
		} );
	}

	/* ---------- active nav ---------- */
	var section = '/' + ( location.pathname.split( '/' )[ 1 ] || '' );
	d.querySelectorAll( '.nav a, .mobile-nav a.mnav-link' ).forEach( function ( a ) {
		var target = '/' + ( ( a.getAttribute( 'href' ) || '' ).replace( /^https?:\/\/[^/]+/, '' ).split( '/' )[ 1 ] || '' );
		if ( target === section ) {
			a.setAttribute( 'aria-current', 'page' );
		}
	} );

	/* ---------- mobile action bar ---------- */
	var bar = d.querySelector( '.action-bar' );
	if ( bar ) {
		d.body.classList.add( 'has-action-bar' );
		var shown = false;
		var onScroll = function () {
			var s = window.scrollY > 480;
			if ( s !== shown ) {
				shown = s;
				bar.classList.toggle( 'show', s );
			}
		};
		window.addEventListener( 'scroll', onScroll, { passive: true } );
		onScroll();
	}

	/* ---------- reveal: first viewport shows at once, the rest on scroll ---------- */
	var rv = d.querySelectorAll( '.reveal' );
	var vh = window.innerHeight || 800;
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	rv.forEach( function ( el ) {
		if ( reduce || el.getBoundingClientRect().top < vh ) {
			el.classList.add( 'in' );
		}
	} );
	if ( 'IntersectionObserver' in window ) {
		var io = new IntersectionObserver( function ( es ) {
			es.forEach( function ( e ) {
				if ( e.isIntersecting ) {
					e.target.classList.add( 'in' );
					io.unobserve( e.target );
				}
			} );
		}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' } );
		rv.forEach( function ( el ) {
			if ( ! el.classList.contains( 'in' ) ) {
				io.observe( el );
			}
		} );
	} else {
		rv.forEach( function ( el ) {
			el.classList.add( 'in' );
		} );
	}

	/* ---------- Contact: the brief form ---------- */
	var f = d.getElementById( 'brief-form' );
	if ( ! f ) {
		return;
	}
	var q = new URLSearchParams( location.search );
	var ok = d.getElementById( 'brief-ok' );
	var err = d.getElementById( 'brief-err' );
	var btn = d.getElementById( 'brief-submit' );
	var label = btn ? btn.firstChild.textContent : 'Send brief ';

	// Preselect from links like /contact/?service=google-ads#brief.
	var svc = q.get( 'service' );
	if ( svc && f.need && f.need.querySelector( 'option[value="' + svc.replace( /[^a-z0-9-]/g, '' ) + '"]' ) ) {
		f.need.value = svc;
	}
	if ( f.source ) {
		f.source.value = ( q.get( 'from' ) || q.get( 'service' ) || d.referrer || 'direct' ).slice( 0, 200 );
	}
	// No-JS fallback round-trip: /contact/?sent=1.
	if ( '1' === q.get( 'sent' ) && ok ) {
		f.hidden = true;
		ok.hidden = false;
	} else if ( q.get( 'error' ) && err ) {
		err.hidden = false;
	}

	function check( el ) {
		var good = el.checkValidity() && ( 'email' !== el.type || /\S+@\S+\.\S+/.test( el.value ) );
		var field = el.closest( '.field' );
		if ( field ) {
			field.toggleAttribute( 'data-invalid', ! good );
		}
		el.setAttribute( 'aria-invalid', good ? 'false' : 'true' );
		return good;
	}
	f.querySelectorAll( '[required]' ).forEach( function ( el ) {
		el.addEventListener( 'blur', function () {
			if ( el.value ) {
				check( el );
			}
		} );
		el.addEventListener( 'input', function () {
			var field = el.closest( '.field' );
			if ( field && field.hasAttribute( 'data-invalid' ) ) {
				check( el );
			}
		} );
	} );

	function send() {
		var bad = [].filter.call( f.querySelectorAll( '[required]' ), function ( el ) {
			return ! check( el );
		} );
		if ( bad.length ) {
			bad[ 0 ].focus();
			return;
		}
		var data = {};
		new FormData( f ).forEach( function ( v, k ) {
			data[ k ] = v;
		} );
		btn.disabled = true;
		btn.firstChild.textContent = 'Sending… ';
		err.hidden = true;
		fetch( f.getAttribute( 'action' ), {
			method: 'POST',
			headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
			body: JSON.stringify( data ),
			credentials: 'same-origin'
		} ).then( function ( r ) {
			return r.json().catch( function () {
				return { ok: false };
			} ).then( function ( j ) {
				if ( ! r.ok || ! j.ok ) {
					throw new Error( 'send' );
				}
			} );
		} ).then( function () {
			f.hidden = true;
			ok.hidden = false;
			ok.focus();
		} ).catch( function () {
			err.hidden = false;
			err.focus();
		} ).then( function () {
			btn.disabled = false;
			btn.firstChild.textContent = label;
		} );
	}
	f.addEventListener( 'submit', function ( e ) {
		e.preventDefault();
		send();
	} );
	var retry = d.getElementById( 'brief-retry' );
	if ( retry ) {
		retry.addEventListener( 'click', send );
	}
} )();
