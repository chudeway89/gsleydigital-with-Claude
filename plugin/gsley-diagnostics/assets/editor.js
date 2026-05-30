/* ============================================================
   GSLEY DIAGNOSTICS — editor registration (no build step)
   Registers all eight tool blocks for the inserter using the
   WordPress script globals. Each block is dynamic (server-rendered
   by the PHP render callback); the editor shows a labelled
   placeholder so admins don't run the live tool while editing.
   ============================================================ */
( function ( blocks, element, blockEditor, data ) {
	'use strict';
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var list = ( data && data.blocks ) || [];

	list.forEach( function ( t ) {
		blocks.registerBlockType( t.name, {
			apiVersion: 2,
			title: t.title,
			description: t.description,
			category: 'gsley',
			icon: t.icon || 'chart-bar',
			keywords: [ 'gsley', 'diagnostic', 'tool', 'quiz', 'calculator' ],
			supports: { html: false, multiple: true, reusable: true },
			attributes: { tool: { type: 'string', default: t.tool } },
			edit: function () {
				var props = useBlockProps( {
					style: {
						border: '1px dashed #16B8A6',
						borderRadius: '10px',
						padding: '20px',
						background: '#F0FBF9',
						fontFamily: 'Inter, sans-serif'
					}
				} );
				return el( 'div', props,
					el( 'div', { style: { fontFamily: 'monospace', fontSize: '11px', letterSpacing: '.1em', textTransform: 'uppercase', color: '#4A5568' } }, t.code || 'GSLEY diagnostic' ),
					el( 'div', { style: { fontWeight: 600, fontSize: '18px', margin: '6px 0 4px', color: '#0F1B2D' } }, t.title ),
					el( 'div', { style: { fontSize: '13px', color: '#4A5568' } }, 'Answer-first diagnostic — interactive on the published page. Nothing to configure here.' )
				);
			},
			save: function () { return null; } // dynamic: rendered by PHP
		} );
	} );
} )( window.wp.blocks, window.wp.element, window.wp.blockEditor, window.gsleyDiagnosticsBlocks || {} );
