/* Startseiten-Blöcke „Mitmachen & Training“ im Editor: Vorschau vom Server, Inhalt im Customizer. */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;
	var blocks = [
		[ 'rv-sulz/mitmachen', __( 'RV Sulz: Probier’s aus', 'rv-sulz' ), 'groups' ],
		[ 'rv-sulz/training', __( 'RV Sulz: Trainingszeiten', 'rv-sulz' ), 'clock' ],
		[ 'rv-sulz/weiterlesen', __( 'RV Sulz: Archiv & Chronik', 'rv-sulz' ), 'archive' ]
	];
	blocks.forEach( function ( b ) {
		wp.blocks.registerBlockType( b[ 0 ], {
			title: b[ 1 ],
			icon: b[ 2 ],
			category: 'widgets',
			edit: function () {
				return el(
					'div',
					be.useBlockProps(),
					el( be.InspectorControls, null,
						el( c.PanelBody, { title: __( 'Inhalt', 'rv-sulz' ) },
							el( 'p', null, __( 'Texte und Trainingszeiten pflegen: Design → Customizer → Mitmachen & Training. Ein leerer Bereich wird auf der Website nicht angezeigt.', 'rv-sulz' ) )
						)
					),
					el( c.Disabled, null,
						el( wp.serverSideRender, {
							block: b[ 0 ],
							EmptyResponsePlaceholder: function () {
								return el( c.Placeholder, { icon: b[ 2 ], label: b[ 1 ], instructions: __( 'Noch leer – Inhalt unter Design → Customizer → Mitmachen & Training eintragen.', 'rv-sulz' ) } );
							}
						} )
					)
				);
			},
			save: function () { return null; }
		} );
	} );
} )( window.wp );
