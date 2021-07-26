/**
 * Translation block
 */

/* global gutenblocksI18n */
( function( wp ) {
	var el                = wp.element.createElement,
		registerBlockType = wp.blocks.registerBlockType,
		innerBlocks       = wp.blockEditor.InnerBlocks,
		allowedLanguages  = [],
		columnTemplates   = {};

	gutenblocksI18n.languages.forEach( function( l, i ) {
		var blockName = 'gutenblocks/language-' + l.replace( '_', '-' ).toLowerCase();

		allowedLanguages.push( blockName );
		columnTemplates[ i ] = [ blockName ];

		registerBlockType( blockName, {
			title: l,
			parent: [ 'gutenblocks/i18n' ],
			icon: 'rows',
			category: 'common',
			edit: function() {
				return el( 'div', {
					className: 'layout-row-' + l
				}, el( innerBlocks, {
					templateLock: false
				} ) );
			},
			save: function() {
				return el( 'div', {
					className: 'layout-row-' + l
				}, el( innerBlocks.Content ) );
			}
		} );
	} );

	registerBlockType( 'gutenblocks/i18n', {

		// Block Title
		title: gutenblocksI18n.title,

		// Description
		description: gutenblocksI18n.description,

		// Block Icon
		icon: 'translation',

		// Block Category
		category: 'layout',

		attributes: {
			columns: {
				type: 'number',
				'default': allowedLanguages.length
			}
		},

		edit: function() {
			// Output the rows of languages.
			return el( 'section', {
				className: 'gutenblocks-i18n'
			}, el( innerBlocks, {
				template:  columnTemplates,
				templateLock:  'all',
				allowedBlocks: allowedLanguages
			} ) );
		},

		save: function() {
			return el( 'section', {}, el( innerBlocks.Content ) );
		}
	} );

	wp.domReady( function () {
		var isCoreEditor = wp.data.select( 'core/editor' );

		if ( ! isCoreEditor ) {
			wp.blocks.unregisterBlockType( 'gutenblocks/i18n' );
		}
	} );

} )( window.wp || {} );
