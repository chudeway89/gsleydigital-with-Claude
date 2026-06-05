/* ============================================================
   GSLEY DIAGNOSTICS — shared engine (plugin)
   One shell + three engines (likert | calculator | finder),
   instance-scoped so multiple tools can live on one page.
   Answer-first: the result renders before the email gate.
   ============================================================ */
( function () {
	'use strict';

	var SETTINGS = window.gsleyDiagnostics || { rest: '/wp-json/gsley/v1/tools/', nonce: '' };
	var reduce = window.matchMedia && window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	function esc( s ) {
		return String( s == null ? '' : s ).replace( /[&<>"']/g, function ( c ) {
			return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[ c ];
		} );
	}
	function money( n ) {
		return '₦' + Math.round( n ).toLocaleString( 'en-NG' );
	}
	function clampPct( x ) { return Math.max( 0, Math.min( 100, x ) ); }

	/* ---------- shell ---------- */
	function buildShell( root, cfg ) {
		root.innerHTML =
			'<div class="gt-progress" hidden>' +
				'<div class="gt-progress__bar"></div>' +
				'<div class="gt-progress__meta"><span class="gt-mono gt-progress__label"></span><span class="gt-mono gt-progress__count"></span></div>' +
			'</div>' +
			'<div class="gt-stage">' +
				'<section class="gt-screen gt-intro is-active" aria-label="Introduction"></section>' +
				'<section class="gt-screen gt-step" aria-live="polite"></section>' +
				'<section class="gt-screen gt-results" aria-label="Your result" aria-live="polite"></section>' +
			'</div>';
		return {
			progress: root.querySelector( '.gt-progress' ),
			bar: root.querySelector( '.gt-progress__bar' ),
			label: root.querySelector( '.gt-progress__label' ),
			count: root.querySelector( '.gt-progress__count' ),
			intro: root.querySelector( '.gt-intro' ),
			step: root.querySelector( '.gt-step' ),
			results: root.querySelector( '.gt-results' )
		};
	}

	function intro( ui, cfg, onStart ) {
		var f = ( cfg.facts || [] ).map( function ( x ) {
			return '<div><span class="v">' + esc( x.v ) + '</span><span class="k">' + esc( x.k ) + '</span></div>';
		} ).join( '' );
		ui.intro.innerHTML =
			'<span class="gt-mono">' + esc( cfg.code || 'Diagnostic' ) + '</span>' +
			'<h2>' + esc( cfg.title ) + '</h2>' +
			'<p class="gt-lede">' + esc( cfg.lede ) + '</p>' +
			( f ? '<div class="gt-facts">' + f + '</div>' : '' ) +
			'<button class="gt-btn gt-btn--solid gt-start">' + esc( cfg.startLabel || 'Begin' ) + ' ↗</button>' +
			'<p class="gt-mono gt-fineprint">No sign-up to begin. You’ll see your result before we ask for anything.</p>';
		ui.intro.querySelector( '.gt-start' ).addEventListener( 'click', onStart );
	}

	function showScreen( ui, name ) {
		[ 'intro', 'step', 'results' ].forEach( function ( n ) {
			ui[ n ].classList.toggle( 'is-active', n === name );
		} );
		ui.progress.hidden = name !== 'step';
		if ( ui.root && ui.root.scrollIntoView ) { ui.root.scrollIntoView( { behavior: reduce ? 'auto' : 'smooth', block: 'start' } ); }
	}

	/* ---------- shared results tail: recommendation + email gate ---------- */
	function recommendation( rec ) {
		if ( ! rec ) { return ''; }
		return '<div class="gt-rec">' +
			'<span class="tag">Your priority</span>' +
			'<h3>' + esc( rec.title ) + '</h3>' +
			'<p>' + esc( rec.body ) + '</p>' +
			'<div class="gt-rec__btns">' +
				'<a class="gt-btn gt-btn--digital" href="' + esc( rec.url ) + '">' + esc( rec.linkText ) + ' ↗</a>' +
				'<a class="gt-btn gt-btn--ghost" href="/contact">Book a discovery call ↗</a>' +
			'</div>' +
		'</div>';
	}

	function emailGate( reportLines ) {
		var li = ( reportLines || [] ).map( function ( t ) {
			return '<li><span class="tick">✓</span> ' + esc( t ) + '</li>';
		} ).join( '' );
		return '<div class="gt-report">' +
			'<h3>Get the full report</h3>' +
			'<p>The on-screen result is your direction. The emailed report adds the detail — a full breakdown and a sequenced 90-day action plan mapped to the relevant archetypes.</p>' +
			'<ul>' + li + '</ul>' +
			'<div class="gt-hp"><label>Leave blank<input type="text" class="gt-company" tabindex="-1" autocomplete="off"></label></div>' +
			'<div class="gt-field"><label>Work email<input type="email" class="gt-email" autocomplete="email" placeholder="you@organisation.com"></label><span class="gt-err"></span></div>' +
			'<div class="gt-field"><label>Name (optional)<input type="text" class="gt-name" autocomplete="name"></label></div>' +
			'<button class="gt-btn gt-btn--solid gt-send">Email me the full report ↗</button>' +
			'<p class="gt-consent">We send the report and, occasionally, relevant insights. Unsubscribe anytime. See our <a href="/legal/privacy" style="text-decoration:underline">privacy policy</a>.</p>' +
		'</div>' +
		'<div class="gt-sent"><div class="check">✓</div><p style="margin-top:.75rem;font-weight:500">Report on its way.</p>' +
			'<p class="gt-mono" style="margin-top:.5rem">Check your inbox shortly. Prefer to talk? <a href="https://wa.me/2349024767079" style="text-decoration:underline">WhatsApp us ↗</a></p></div>';
	}

	function wireEmail( ui, cfg, getPayload ) {
		var report = ui.results.querySelector( '.gt-report' );
		var sent = ui.results.querySelector( '.gt-sent' );
		var send = ui.results.querySelector( '.gt-send' );
		if ( ! send ) { return; }
		send.addEventListener( 'click', function () {
			var email = ui.results.querySelector( '.gt-email' ).value.trim();
			var name = ui.results.querySelector( '.gt-name' ).value.trim();
			var company = ui.results.querySelector( '.gt-company' ).value; // honeypot
			var errEl = ui.results.querySelector( '.gt-err' );
			if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) {
				errEl.textContent = 'Please enter a valid work email.';
				ui.results.querySelector( '.gt-email' ).focus();
				return;
			}
			errEl.textContent = '';
			send.disabled = true;
			var payload = getPayload();
			payload.email = email;
			payload.name = name;
			payload.company_website = company;
			payload.ts = new Date().toISOString();

			fetch( SETTINGS.rest + encodeURIComponent( cfg.id ), {
				method: 'POST',
				headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': SETTINGS.nonce || '' },
				body: JSON.stringify( payload )
			} ).then( function ( r ) { return r.ok ? r.json() : Promise.reject( r ); } )
			.then( function () { report.style.display = 'none'; sent.classList.add( 'is-show' ); } )
			.catch( function () {
				send.disabled = false;
				errEl.textContent = 'Something went wrong sending the report. Please try WhatsApp or email us directly.';
			} );
		} );
	}

	function restartButton( ui, onRestart ) {
		var d = document.createElement( 'div' );
		d.className = 'gt-restart';
		d.innerHTML = '<button class="gt-btn gt-btn--ghost gt-restart-btn">↺ Start over</button>';
		ui.results.appendChild( d );
		d.querySelector( '.gt-restart-btn' ).addEventListener( 'click', onRestart );
	}

	/* ============================================================ LIKERT ENGINE */
	function likert( root, ui, cfg ) {
		var answers = new Array( cfg.questions.length ).fill( null );
		var qi = 0;

		function renderQuestion() {
			var q = cfg.questions[ qi ];
			var dim = cfg.dimensions.find( function ( d ) { return d.id === q.dim; } );
			ui.label.textContent = dim.label;
			ui.count.textContent = ( qi + 1 ) + ' / ' + cfg.questions.length;
			ui.bar.style.width = ( qi / cfg.questions.length * 100 ) + '%';
			var opts = cfg.scale.map( function ( s, idx ) {
				return '<button class="gt-opt" role="radio" data-v="' + s.value + '" aria-checked="' + ( answers[ qi ] === s.value ? 'true' : 'false' ) + '">' +
					'<span class="gt-opt__dot" aria-hidden="true"></span><span>' + esc( s.label ) + '</span></button>';
			} ).join( '' );
			var last = qi === cfg.questions.length - 1;
			ui.step.innerHTML =
				'<span class="gt-mono gt-q__dim">' + esc( dim.label ) + '</span>' +
				'<h2 class="gt-q__text">' + esc( q.text ) + '</h2>' +
				'<div class="gt-opts" role="radiogroup">' + opts + '</div>' +
				'<div class="gt-nav">' +
					'<button class="gt-btn gt-btn--ghost gt-back"' + ( qi === 0 ? ' style="visibility:hidden"' : '' ) + '>← Back</button>' +
					'<button class="gt-btn gt-btn--solid gt-next"' + ( answers[ qi ] === null ? ' disabled' : '' ) + '>' + ( last ? 'See my result →' : 'Next →' ) + '</button>' +
				'</div>';
			ui.step.querySelectorAll( '.gt-opt' ).forEach( function ( b ) {
				b.addEventListener( 'click', function () {
					answers[ qi ] = parseInt( b.getAttribute( 'data-v' ), 10 );
					renderQuestion();
				} );
			} );
			ui.step.querySelector( '.gt-back' ).addEventListener( 'click', function () { if ( qi > 0 ) { qi--; renderQuestion(); } } );
			ui.step.querySelector( '.gt-next' ).addEventListener( 'click', advance );
		}
		function advance() {
			if ( answers[ qi ] === null ) { return; }
			if ( qi < cfg.questions.length - 1 ) { qi++; renderQuestion(); } else { renderResults(); }
		}
		function compute() {
			var byDim = {};
			cfg.dimensions.forEach( function ( d ) { byDim[ d.id ] = { sum: 0, count: 0 }; } );
			cfg.questions.forEach( function ( q, i ) { byDim[ q.dim ].sum += answers[ i ]; byDim[ q.dim ].count++; } );
			var maxPer = cfg.scale.length - 1;
			var dims = cfg.dimensions.map( function ( d ) {
				var o = byDim[ d.id ];
				return { id: d.id, label: d.label, pct: Math.round( o.sum / ( o.count * maxPer ) * 100 ) };
			} );
			var overall = Math.round( dims.reduce( function ( a, d ) { return a + d.pct; }, 0 ) / dims.length );
			var band = cfg.bands.find( function ( b ) { return overall >= b.min && overall <= b.max; } );
			var lowest = dims.reduce( function ( a, b ) { return b.pct < a.pct ? b : a; } );
			return { overall: overall, band: band, dims: dims, lowest: lowest };
		}
		function renderResults() {
			var r = compute();
			var circ = 2 * Math.PI * 80;
			var dimsHtml = r.dims.map( function ( d ) {
				var low = d.id === r.lowest.id;
				return '<div><div class="gt-dim__top"><span class="gt-dim__label">' + esc( d.label ) + ( low ? ' · priority' : '' ) +
					'</span><span class="gt-dim__pct">' + d.pct + '%</span></div>' +
					'<div class="gt-track"><div class="gt-fill' + ( low ? ' is-low' : '' ) + '" data-pct="' + d.pct + '"></div></div></div>';
			} ).join( '' );
			ui.results.innerHTML =
				'<div class="gt-score"><span class="gt-mono">' + esc( cfg.scoreLabel || 'Your score' ) + '</span>' +
					'<div class="gt-ring"><svg width="180" height="180" viewBox="0 0 180 180">' +
						'<circle cx="90" cy="90" r="80" fill="none" stroke="var(--bone)" stroke-width="12"/>' +
						'<circle class="gt-ring__arc" cx="90" cy="90" r="80" fill="none" stroke="var(--accent)" stroke-width="12" stroke-linecap="round" stroke-dasharray="' + circ + '" stroke-dashoffset="' + circ + '"/>' +
					'</svg><div class="gt-ring__num"><span class="n">0</span><span class="s">out of 100</span></div></div>' +
					'<div class="gt-band">' + esc( r.band.name ) + '</div><p class="gt-band__blurb">' + esc( r.band.blurb ) + '</p></div>' +
				'<div class="gt-dims">' + dimsHtml + '</div>' +
				recommendation( cfg.recommendations[ r.lowest.id ] ) +
				emailGate( cfg.reportLines );
			showScreen( ui, 'results' );
			animateScore( ui, r.overall, circ );
			ui.results.querySelectorAll( '.gt-fill' ).forEach( function ( f ) {
				setTimeout( function () { f.style.width = f.getAttribute( 'data-pct' ) + '%'; }, 120 );
			} );
			wireEmail( ui, cfg, function () {
				return { tool: cfg.id, answers: answers.slice(), overall: r.overall, band: r.band.name,
					dimensions: r.dims, priority: r.lowest.id };
			} );
			restartButton( ui, restart );
		}
		function restart() { answers.fill( null ); qi = 0; intro( ui, cfg, start ); showScreen( ui, 'intro' ); }
		function start() { qi = 0; renderQuestion(); showScreen( ui, 'step' ); }

		root.addEventListener( 'keydown', function ( e ) {
			if ( ! ui.step.classList.contains( 'is-active' ) ) { return; }
			var k = parseInt( e.key, 10 );
			if ( k >= 1 && k <= cfg.scale.length ) {
				answers[ qi ] = k - 1; renderQuestion();
			} else if ( e.key === 'Enter' ) {
				var n = ui.step.querySelector( '.gt-next' ); if ( n && ! n.disabled ) { n.click(); }
			}
		} );
		intro( ui, cfg, start );
	}

	/* ============================================================ CALCULATOR ENGINE */
	function calculator( root, ui, cfg ) {
		function start() {
			var fields = cfg.inputs.map( function ( inp ) {
				return '<div class="gt-field"><label>' + esc( inp.label ) +
					'<input type="number" class="gt-num" data-id="' + esc( inp.id ) + '" min="0" step="any" value="' + esc( inp.default ) + '"></label></div>';
			} ).join( '' );
			ui.step.innerHTML =
				'<span class="gt-mono gt-q__dim">Your numbers</span>' +
				'<h2 class="gt-q__text">' + esc( cfg.stepHeading ) + '</h2>' +
				'<div class="gt-grid2">' + fields + '</div>' +
				'<p class="gt-consent" style="margin-top:1rem">' + esc( cfg.estimateNote ) + '</p>' +
				'<div class="gt-nav"><span></span><button class="gt-btn gt-btn--solid gt-calc">See my estimate →</button></div>';
			ui.step.querySelector( '.gt-calc' ).addEventListener( 'click', renderResults );
			showScreen( ui, 'step' );
		}
		function readInputs() {
			var v = {};
			ui.step.querySelectorAll( '.gt-num' ).forEach( function ( n ) { v[ n.getAttribute( 'data-id' ) ] = parseFloat( n.value ) || 0; } );
			return v;
		}
		function compute( v ) {
			var b = cfg.benchmarks, rev = v.revenue || 0;
			var comps = [
				{ id: 'conversion', label: 'Conversion leak', amount: rev * Math.max( 0, ( b.benchmarkConv - ( v.conv || 0 ) ) ) / 100 },
				{ id: 'retention', label: 'Retention leak', amount: rev * clampPct( ( ( v.churn || 0 ) - b.targetChurn ) * 12 ) / 100 },
				{ id: 'pricing', label: 'Pricing leak', amount: rev * Math.max( 0, ( ( v.discount || 0 ) - b.floorDiscount ) ) / 100 },
				{ id: 'expansion', label: 'Expansion gap', amount: rev * Math.max( 0, ( b.targetExpansion - ( v.expansion || 0 ) ) ) / 100 }
			];
			var total = comps.reduce( function ( a, c ) { return a + c.amount; }, 0 );
			if ( rev > 0 ) { total = Math.min( total, rev ); } // never claim more than revenue
			var dominant = comps.slice().sort( function ( a, c ) { return c.amount - a.amount; } )[ 0 ];
			return { comps: comps, total: total, dominant: dominant };
		}
		function renderResults() {
			var v = readInputs(), r = compute( v );
			var max = Math.max.apply( null, r.comps.map( function ( c ) { return c.amount; } ).concat( [ 1 ] ) );
			var bars = r.comps.map( function ( c ) {
				return '<div><div class="gt-dim__top"><span class="gt-dim__label">' + esc( c.label ) +
					'</span><span class="gt-dim__pct">' + money( c.amount ) + '</span></div>' +
					'<div class="gt-track"><div class="gt-fill" data-pct="' + clampPct( c.amount / max * 100 ) + '"></div></div></div>';
			} ).join( '' );
			var rec = cfg.recommendations[ r.dominant.id ] || cfg.recommendations.default;
			ui.results.innerHTML =
				'<div class="gt-score"><span class="gt-mono">' + esc( cfg.scoreLabel ) + '</span>' +
					'<div class="gt-figure">≈ ' + money( r.total ) + '</div>' +
					'<p class="gt-figure__cap">' + esc( cfg.figureCaption ) + '</p></div>' +
				'<div class="gt-dims">' + bars + '</div>' +
				recommendation( rec ) +
				emailGate( cfg.reportLines );
			showScreen( ui, 'results' );
			ui.results.querySelectorAll( '.gt-fill' ).forEach( function ( f ) {
				setTimeout( function () { f.style.width = f.getAttribute( 'data-pct' ) + '%'; }, 120 );
			} );
			wireEmail( ui, cfg, function () {
				return { tool: cfg.id, inputs: v, total: Math.round( r.total ),
					components: r.comps.map( function ( c ) { return { id: c.id, amount: Math.round( c.amount ) }; } ),
					priority: r.dominant.id };
			} );
			restartButton( ui, start );
		}
		intro( ui, cfg, start );
	}

	/* ============================================================ FINDER ENGINE */
	function finder( root, ui, cfg ) {
		var selected = {};

		function stepSelect() {
			var areas = cfg.areas.map( function ( a ) {
				var on = !! selected[ a.id ];
				return '<button class="gt-area" data-id="' + esc( a.id ) + '" aria-pressed="' + ( on ? 'true' : 'false' ) + '">' +
					'<span class="gt-area__box" aria-hidden="true">' + ( on ? '✓' : '' ) + '</span><span>' + esc( a.label ) + '</span></button>';
			} ).join( '' );
			ui.step.innerHTML =
				'<span class="gt-mono gt-q__dim">Step 1 of 2</span>' +
				'<h2 class="gt-q__text">' + esc( cfg.selectHeading ) + '</h2>' +
				'<div class="gt-areas">' + areas + '</div>' +
				'<div class="gt-nav"><span></span><button class="gt-btn gt-btn--solid gt-to-qual" ' + ( hasSel() ? '' : 'disabled' ) + '>Next →</button></div>';
			ui.step.querySelectorAll( '.gt-area' ).forEach( function ( b ) {
				b.addEventListener( 'click', function () {
					var id = b.getAttribute( 'data-id' );
					if ( selected[ id ] ) { delete selected[ id ]; } else { selected[ id ] = { freq: 2, pain: 2 }; }
					stepSelect();
				} );
			} );
			var nx = ui.step.querySelector( '.gt-to-qual' );
			if ( nx ) { nx.addEventListener( 'click', stepQualify ); }
			showScreen( ui, 'step' );
		}
		function hasSel() { return Object.keys( selected ).length > 0; }

		function seg( areaId, kind ) {
			var labels = [ [ 1, 'Low' ], [ 2, 'Medium' ], [ 3, 'High' ] ];
			return '<div class="gt-seg" data-area="' + esc( areaId ) + '" data-kind="' + kind + '">' + labels.map( function ( l ) {
				return '<button data-val="' + l[ 0 ] + '" aria-pressed="' + ( selected[ areaId ][ kind ] === l[ 0 ] ? 'true' : 'false' ) + '">' + l[ 1 ] + '</button>';
			} ).join( '' ) + '</div>';
		}
		function stepQualify() {
			var blocks = Object.keys( selected ).map( function ( id ) {
				var a = cfg.areas.find( function ( x ) { return x.id === id; } );
				return '<div class="gt-qual"><div class="gt-qual__name">' + esc( a.label ) + '</div><div class="gt-qual__rows">' +
					'<div><div class="gt-mono" style="margin-bottom:.4rem">How often</div>' + seg( id, 'freq' ) + '</div>' +
					'<div><div class="gt-mono" style="margin-bottom:.4rem">How painful</div>' + seg( id, 'pain' ) + '</div>' +
					'</div></div>';
			} ).join( '' );
			ui.step.innerHTML =
				'<span class="gt-mono gt-q__dim">Step 2 of 2</span>' +
				'<h2 class="gt-q__text">' + esc( cfg.qualifyHeading ) + '</h2>' + blocks +
				'<div class="gt-nav"><button class="gt-btn gt-btn--ghost gt-back">← Back</button><button class="gt-btn gt-btn--solid gt-rank">See opportunities →</button></div>';
			ui.step.querySelectorAll( '.gt-seg button' ).forEach( function ( b ) {
				b.addEventListener( 'click', function () {
					var seg = b.parentNode, area = seg.getAttribute( 'data-area' ), kind = seg.getAttribute( 'data-kind' );
					selected[ area ][ kind ] = parseInt( b.getAttribute( 'data-val' ), 10 );
					seg.querySelectorAll( 'button' ).forEach( function ( x ) { x.setAttribute( 'aria-pressed', x === b ? 'true' : 'false' ); } );
				} );
			} );
			ui.step.querySelector( '.gt-back' ).addEventListener( 'click', stepSelect );
			ui.step.querySelector( '.gt-rank' ).addEventListener( 'click', renderResults );
		}
		function renderResults() {
			var ranked = Object.keys( selected ).map( function ( id ) {
				var a = cfg.areas.find( function ( x ) { return x.id === id; } );
				var s = selected[ id ];
				return { id: id, label: a.label, method: a.method, effort: a.effort, service: a.service, score: s.freq * s.pain, freq: s.freq, pain: s.pain };
			} ).sort( function ( a, b ) { return b.score - a.score; } );
			var top = ranked.slice( 0, 5 );
			var ops = top.map( function ( o, i ) {
				return '<div class="gt-op"><span class="gt-op__rank">#' + ( i + 1 ) + '</span><div><h4>' + esc( o.label ) + '</h4>' +
					'<p>' + esc( o.method ) + '</p><span class="gt-op__effort">' + esc( o.effort ) + ' effort</span></div></div>';
			} ).join( '' );
			// overall recommendation: agentic / data flags
			var ids = top.map( function ( o ) { return o.service; } );
			var rec = cfg.recommendations.default;
			if ( ids.indexOf( 'ai-agent-deployment' ) === 0 ) { rec = cfg.recommendations.agentic; }
			else if ( ids.indexOf( 'data-infrastructure' ) === 0 ) { rec = cfg.recommendations.data; }
			ui.results.innerHTML =
				'<div class="gt-score"><span class="gt-mono">' + esc( cfg.scoreLabel ) + '</span>' +
					'<h2 class="gt-band" style="margin-top:.5rem">' + esc( cfg.resultHeading ) + '</h2></div>' +
				'<div class="gt-ops">' + ops + '</div>' +
				recommendation( rec ) +
				emailGate( cfg.reportLines );
			showScreen( ui, 'results' );
			wireEmail( ui, cfg, function () {
				return { tool: cfg.id, selections: ranked.map( function ( o ) { return { area: o.id, freq: o.freq, pain: o.pain, score: o.score }; } ),
					top: top.map( function ( o ) { return o.id; } ), priority: top.length ? top[ 0 ].service : null };
			} );
			restartButton( ui, function () { selected = {}; stepSelect(); } );
		}
		intro( ui, cfg, stepSelect );
	}

	/* ============================================================ AUDIT ENGINE (paid) */
	function audit( root, ui, cfg ) {
		var base = ( SETTINGS.rest || '/wp-json/gsley/v1/tools/' ).replace( /tools\/$/, '' );
		var lsKey = 'gsleyAudit_' + cfg.id;
		var ringSvg = function () {
			var circ = 2 * Math.PI * 80;
			return '<div class="gt-ring"><svg width="180" height="180" viewBox="0 0 180 180">' +
				'<circle cx="90" cy="90" r="80" fill="none" stroke="var(--bone)" stroke-width="12"/>' +
				'<circle class="gt-ring__arc" cx="90" cy="90" r="80" fill="none" stroke="var(--accent)" stroke-width="12" stroke-linecap="round" stroke-dasharray="' + circ + '" stroke-dashoffset="' + circ + '"/>' +
				'</svg><div class="gt-ring__num"><span class="n">0</span><span class="s">out of 100</span></div></div>';
		};
		var bandWord = function ( n ) {
			return n >= 80 ? 'Strong' : ( n >= 60 ? 'Solid' : ( n >= 40 ? 'Developing' : 'Needs work' ) );
		};

		function start() { stepInputs(); }

		function stepInputs() {
			var fields = cfg.inputs.map( function ( inp ) {
				if ( 'urls' === inp.type ) {
					return '<div class="gt-field"><label>' + esc( inp.label ) + '<textarea class="gt-in" data-id="' + esc( inp.id ) + '" data-type="urls" rows="3" placeholder="' + esc( inp.placeholder || '' ) + '"></textarea></label><span class="gt-hint2">One link per line.</span></div>';
				}
				if ( 'number' === inp.type ) {
					return '<div class="gt-field"><label>' + esc( inp.label ) + '<input class="gt-in" data-id="' + esc( inp.id ) + '" data-type="number" type="number"' + ( inp.step ? ' step="' + esc( inp.step ) + '"' : '' ) + ( null != inp.min ? ' min="' + esc( inp.min ) + '"' : '' ) + ( null != inp.max ? ' max="' + esc( inp.max ) + '"' : '' ) + ' placeholder="' + esc( inp.placeholder || '' ) + '"></label></div>';
				}
				return '<div class="gt-field"><label>' + esc( inp.label ) + '<input class="gt-in" data-id="' + esc( inp.id ) + '" data-type="url" type="url" placeholder="' + esc( inp.placeholder || '' ) + '"' + ( inp.required ? ' required' : '' ) + '></label></div>';
			} ).join( '' );
			ui.step.innerHTML =
				'<span class="gt-mono gt-q__dim">' + esc( cfg.code || 'Audit' ) + '</span>' +
				'<h2 class="gt-q__text">Tell us what to audit</h2>' +
				fields +
				'<div class="gt-hp"><label>Leave blank<input type="text" class="gt-company" tabindex="-1" autocomplete="off"></label></div>' +
				'<div class="gt-err gt-run-err"></div>' +
				'<div class="gt-nav"><span></span><button class="gt-btn gt-btn--solid gt-run">Run the audit →</button></div>';
			ui.step.querySelector( '.gt-run' ).addEventListener( 'click', run );
			showScreen( ui, 'step' );
		}

		function collect() {
			var out = { socials: [] };
			ui.step.querySelectorAll( '.gt-in' ).forEach( function ( el ) {
				var id = el.getAttribute( 'data-id' ), t = el.getAttribute( 'data-type' ), v = el.value.trim();
				if ( 'urls' === t ) { out.socials = v.split( /[\n,]+/ ).map( function ( x ) { return x.trim(); } ).filter( Boolean ); }
				else if ( 'number' === t ) { if ( '' !== v ) { out[ id ] = parseFloat( v ); } }
				else if ( '' !== v ) { out[ id ] = v; }
			} );
			out.company_website = ui.step.querySelector( '.gt-company' ).value;
			return out;
		}

		function run() {
			var payload = collect();
			var errEl = ui.step.querySelector( '.gt-run-err' );
			var missing = cfg.inputs.filter( function ( i ) { return i.required && ! payload[ i.id ]; } );
			if ( missing.length ) { errEl.textContent = 'Please enter your ' + missing[ 0 ].label.replace( /\s*\(.*\)/, '' ).toLowerCase() + '.'; return; }
			if ( ! payload.website && ( ! payload.socials || ! payload.socials.length ) ) { errEl.textContent = 'Enter your website or at least one social link.'; return; }
			errEl.textContent = '';
			var btn = ui.step.querySelector( '.gt-run' );
			btn.disabled = true; btn.textContent = 'Auditing… (up to ~20s)';
			fetch( base + 'audit/' + encodeURIComponent( cfg.id ), {
				method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': SETTINGS.nonce || '' }, body: JSON.stringify( payload )
			} ).then( function ( r ) { return r.ok ? r.json() : Promise.reject( r ); } )
			.then( function ( d ) {
				try { localStorage.setItem( lsKey, JSON.stringify( { audit_id: d.audit_id, token: d.token } ) ); } catch ( e ) {}
				renderReport( d );
			} )
			.catch( function () { btn.disabled = false; btn.textContent = 'Run the audit →'; errEl.textContent = 'The audit could not complete. Check the URLs and try again.'; } );
		}

		function renderReport( d ) {
			var rep = d.report, circ = 2 * Math.PI * 80;
			var pillars = ( rep.pillars || [] ).map( function ( p ) {
				return '<div><div class="gt-dim__top"><span class="gt-dim__label">' + esc( p.label ) + '</span><span class="gt-dim__pct">' + p.pct + '%</span></div><div class="gt-track"><div class="gt-fill" data-pct="' + p.pct + '"></div></div></div>';
			} ).join( '' );
			var finds = ( rep.findings || [] ).map( function ( f ) {
				var c = 'pass' === f.status ? 'var(--success)' : ( 'fail' === f.status ? 'var(--danger)' : ( 'warn' === f.status ? 'var(--warning)' : 'var(--ink-300)' ) );
				return '<div class="gt-find"><span class="gt-find__s" style="color:' + c + '">' + esc( f.status.toUpperCase() ) + '</span><span class="gt-find__l">' + esc( f.label ) + '</span><span class="gt-find__d">' + esc( f.detail ) + '</span></div>';
			} ).join( '' );
			ui.results.innerHTML =
				'<div class="gt-score"><span class="gt-mono">' + esc( cfg.scoreLabel || 'Your score' ) + '</span>' + ringSvg() +
					'<div class="gt-band">' + esc( bandWord( rep.overall ) ) + '</div><p class="gt-band__blurb">' + esc( rep.summary ) + '</p></div>' +
				'<div class="gt-dims">' + pillars + '</div>' +
				'<h3 class="gt-find-h">What we checked</h3><div class="gt-finds">' + finds + '</div>' +
				unlockPanel( d );
			showScreen( ui, 'results' );
			animateScore( ui, rep.overall, circ );
			ui.results.querySelectorAll( '.gt-fill' ).forEach( function ( f ) { setTimeout( function () { f.style.width = f.getAttribute( 'data-pct' ) + '%'; }, 120 ); } );
			wireUnlock( d );
			restartButton( ui, stepInputs );
		}

		function unlockPanel( d ) {
			if ( ! d.report.reco_count ) {
				return '<div class="gt-report"><h3>No issues found</h3><p>Your audit came back clean across every check — nothing to unlock. Want a deeper, human review? <a href="/contact">Talk to us</a>.</p></div>';
			}
			var li = ( cfg.reportLines || [] ).map( function ( t ) { return '<li><span class="tick">✓</span> ' + esc( t ) + '</li>'; } ).join( '' );
			return '<div class="gt-report"><h3>Unlock your ' + d.report.reco_count + ' detailed recommendations</h3>' +
				'<p>Your score shows <em>where</em> you stand. The paid report tells you exactly <em>what to fix</em>, in priority order, with the evidence behind each.</p>' +
				'<ul>' + li + '</ul>' +
				'<div class="gt-field"><label>Work email<input type="email" class="gt-email" placeholder="you@brand.com"></label><span class="gt-err"></span></div>' +
				'<button class="gt-btn gt-btn--solid gt-pay">Unlock for ' + esc( d.priceLabel ) + ' →</button>' +
				'<p class="gt-consent">Secure payment via Paystack. We email your recommendations and a copy of this report. See our <a href="/legal/privacy" style="text-decoration:underline">privacy policy</a>.</p></div>';
		}

		function wireUnlock( d ) {
			var pay = ui.results.querySelector( '.gt-pay' );
			if ( ! pay ) { return; }
			pay.addEventListener( 'click', function () {
				var email = ( ui.results.querySelector( '.gt-email' ).value || '' ).trim();
				var errEl = ui.results.querySelector( '.gt-report .gt-err' );
				if ( ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( email ) ) { errEl.textContent = 'Please enter a valid email.'; return; }
				errEl.textContent = ''; pay.disabled = true; pay.textContent = 'Starting secure checkout…';
				fetch( base + 'pay/init', {
					method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': SETTINGS.nonce || '' },
					body: JSON.stringify( { audit_id: d.audit_id, token: d.token, email: email, callback: location.href.split( '?' )[ 0 ] } )
				} ).then( function ( r ) { return r.ok ? r.json() : r.json().then( function ( j ) { return Promise.reject( j ); } ); } )
				.then( function ( res ) {
					if ( res.authorization_url ) { window.location = res.authorization_url; }
					else if ( res.paid ) { confirmPayment( d.audit_id, d.token, '' ); }
					else { throw res; }
				} )
				.catch( function ( j ) { pay.disabled = false; pay.textContent = 'Unlock for ' + cfg.priceLabel + ' →'; errEl.textContent = ( j && j.message ) ? j.message : 'Could not start checkout. Please try again.'; } );
			} );
		}

		function confirmPayment( id, token, ref ) {
			ui.results.innerHTML = '<p class="gt-mono" style="padding:2rem 0">Confirming your payment…</p>';
			showScreen( ui, 'results' );
			fetch( base + 'pay/confirm', {
				method: 'POST', headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': SETTINGS.nonce || '' },
				body: JSON.stringify( { audit_id: id, token: token, reference: ref } )
			} ).then( function ( r ) { return r.json(); } )
			.then( function ( d ) {
				if ( d.paid ) { try { localStorage.removeItem( lsKey ); } catch ( e ) {} renderUnlocked( d.recommendations ); }
				else { ui.results.innerHTML = '<div class="gt-report"><h3>Payment not confirmed</h3><p>' + esc( d.error || 'We could not confirm the payment.' ) + ' If you were charged, your recommendations have also been emailed to you.</p></div>'; }
			} )
			.catch( function () { ui.results.innerHTML = '<div class="gt-report"><h3>Could not confirm right now</h3><p>Please check your inbox — we email your recommendations there too.</p></div>'; } );
		}

		function renderUnlocked( recs ) {
			var items = ( recs || [] ).map( function ( r ) {
				var c = 'high' === r.priority ? 'var(--danger)' : ( 'medium' === r.priority ? 'var(--warning)' : 'var(--ink-500)' );
				return '<div class="gt-rec-item" style="border-left-color:' + c + '"><div class="gt-mono" style="color:' + c + '">' + esc( ( r.priority || '' ).toUpperCase() ) + '</div><strong>' + esc( r.title ) + '</strong><p>' + esc( r.detail ) + '</p><span class="gt-rec-ev">Why: ' + esc( r.evidence || '' ) + '</span></div>';
			} ).join( '' );
			ui.results.innerHTML =
				'<div class="gt-score"><span class="gt-mono">Unlocked</span><h2 class="gt-band" style="margin-top:.4rem">Your detailed recommendations</h2><p class="gt-band__blurb">We’ve emailed a copy too.</p></div>' +
				'<div class="gt-recs">' + items + '</div>' +
				'<div class="gt-rec"><span class="tag">Next step</span><h3>Want us to fix these for you?</h3><p>We can implement the fixes on a productised engagement.</p><div class="gt-rec__btns"><a class="gt-btn gt-btn--digital" href="/contact">Book a discovery call ↗</a></div></div>';
			showScreen( ui, 'results' );
		}

		// Payment return: Paystack sends the buyer back with ?reference / ?trxref.
		var params = new URLSearchParams( location.search );
		var ref = params.get( 'reference' ) || params.get( 'trxref' );
		var stored = null;
		try { stored = JSON.parse( localStorage.getItem( lsKey ) || 'null' ); } catch ( e ) {}
		if ( ref && stored && stored.audit_id ) {
			confirmPayment( stored.audit_id, stored.token, ref );
		} else {
			intro( ui, cfg, start );
		}
	}

	/* ---------- score ring animation ---------- */
	function animateScore( ui, overall, circ ) {
		var arc = ui.results.querySelector( '.gt-ring__arc' );
		var num = ui.results.querySelector( '.gt-ring__num .n' );
		if ( reduce ) { if ( arc ) { arc.setAttribute( 'stroke-dashoffset', circ * ( 1 - overall / 100 ) ); } if ( num ) { num.textContent = overall; } return; }
		setTimeout( function () { if ( arc ) { arc.style.transition = 'stroke-dashoffset 900ms cubic-bezier(.22,1,.36,1)'; arc.setAttribute( 'stroke-dashoffset', circ * ( 1 - overall / 100 ) ); } }, 60 );
		var n = 0, t = setInterval( function () { n += Math.ceil( overall / 30 ) || 1; if ( n >= overall ) { n = overall; clearInterval( t ); } if ( num ) { num.textContent = n; } }, 24 );
	}

	/* ---------- boot ---------- */
	function initTool( root ) {
		if ( root.dataset.gtReady ) { return; }
		root.dataset.gtReady = '1';
		var cfgEl = root.querySelector( '.gsley-tool-config' );
		if ( ! cfgEl ) { return; }
		var cfg;
		try { cfg = JSON.parse( cfgEl.textContent ); } catch ( e ) { return; }
		var ui = buildShell( root, cfg );
		ui.root = root;
		showScreen( ui, 'intro' );
		if ( cfg.engine === 'calculator' ) { calculator( root, ui, cfg ); }
		else if ( cfg.engine === 'finder' ) { finder( root, ui, cfg ); }
		else if ( cfg.engine === 'audit' ) { audit( root, ui, cfg ); }
		else { likert( root, ui, cfg ); }
	}

	function boot() { document.querySelectorAll( '.gsley-tool' ).forEach( initTool ); }
	if ( document.readyState === 'loading' ) { document.addEventListener( 'DOMContentLoaded', boot ); } else { boot(); }
} )();
