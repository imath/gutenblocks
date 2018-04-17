/**
 * Translation block
 */

/* global gutenblocksI18n */
( function( wp ) {
	var el                = wp.element.createElement,
	    registerBlockType = wp.blocks.registerBlockType,
	    innerBlocks       = wp.blocks.InnerBlocks;

	registerBlockType( 'gutenblocks/i18n', {

		// Block Title
		title: gutenblocksI18n.title,

		// Block Icon
		icon: 'translation',

		// Block Category
		category: 'layout',

		edit: function() {
            // Output the rows of languages.
			return el( 'section', {
                className: 'gutenblocks-i18n'
            }, el( innerBlocks, {
                layouts: gutenblocksI18n.languages.map( function( l ) {
                    return { name: 'row-' + l, label: l, icon: 'rows' };
                } )
            } ) );
		},

		save: function() {
            return el( 'section', {}, el( innerBlocks.Content ) );
		}
	} );

} )( window.wp || {} );
