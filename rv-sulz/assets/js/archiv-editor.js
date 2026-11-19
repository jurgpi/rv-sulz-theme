/* Block „RV Sulz: Archiv“ im Editor: Vorschau vom Server, Einstellungen in der Seitenleiste. */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var be = wp.blockEditor;
	var c = wp.components;

	wp.blocks.registerBlockType( 'rv-sulz/archiv', {
		title: __( 'RV Sulz: Archiv', 'rv-sulz' ),
		icon: 'archive',
		category: 'widgets',
		edit: function ( props ) {
			var a = props.attributes;
			return el(
				'div',
				be.useBlockProps(),
				el(
					be.InspectorControls,
					null,
					el(
						c.PanelBody,
						{ title: __( 'Archiv', 'rv-sulz' ) },
						el( c.RangeControl, {
							label: __( 'Beiträge pro Seite', 'rv-sulz' ),
							value: a.perPage,
							min: 3,
							max: 48,
							step: 3,
							onChange: function ( v ) { props.setAttributes( { perPage: v } ); }
						} ),
						el( c.ToggleControl, {
							label: __( 'Suchfeld zeigen', 'rv-sulz' ),
							checked: a.showSearch,
							onChange: function ( v ) { props.setAttributes( { showSearch: v } ); }
						} )
					)
				),
				el( c.Disabled, null,
					el( wp.serverSideRender, { block: 'rv-sulz/archiv', attributes: { perPage: a.perPage, showSearch: a.showSearch } } )
				)
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
