/**
 * WordPress Embed block
 *
 * NB: this block will disappear as soon as https://github.com/WordPress/gutenberg/pull/4226
 * is fixed.
 */

/* global gutenBlocksStrings, wpApiSettings */
( function( wp ) {
	var el                = wp.element.createElement,
	    registerBlockType = wp.blocks.registerBlockType;

	registerBlockType( 'gutenblocks/wp-embed', {

		// Block Title
		title: gutenBlocksStrings.wp_embed.title,

		// Block Icon
		icon: 'wordpress-alt',

		// Block Category
		category: 'embed',

		// Block Attributes
		attributes: {
			url: {
				type: 'string'
			}
		},

		edit: function( props ) {
			var onChangeInput = function( event ) {
				event.preventDefault();

				props.setAttributes( {
					url:        event.target.value,
					hasChanged: true
				} );
			};

			var onUrlSubmit = function( event ) {
				if ( event ) {
					event.preventDefault();
				}

				props.setAttributes( {
					loading: true
				} );

				// Defaults to proxy
				var action = 'proxy',
				    url = encodeURIComponent( props.attributes.url );

				// If it's a self embed, use the embed action.
				if ( -1 !== props.attributes.url.indexOf( wpApiSettings.root.replace( '/wp-json', '' ) ) ) {
					action = 'embed';
				}

				window.fetch( wpApiSettings.root + 'oembed/1.0/' + action +
					'?url=' + url +
					'&_wpnonce=' + wpApiSettings.nonce, {
					credentials: 'include'
				} ).then(
					function( response ) {
						response.json().then( function( reply ) {
							props.setAttributes( {
								title: reply.title,
								html:  reply.html
							} );
						} );
					}
				);
			};

			// If the URL has been already saved, load the WordPress embed.
			if ( props.attributes.url && ! props.attributes.loading && ! props.attributes.hasChanged ) {
				onUrlSubmit();
			}

			// Output the form to insert a gist.
			if ( ! props.attributes.html && ! props.attributes.loading ) {
				return el(
					'div', {
						className: 'components-placeholder'
					}, el(
						'form', {
							onSubmit: onUrlSubmit
						},
						[
							el(
								'div', {
									key:       'block-placeholder',
									className: 'components-placeholder__label'
								}, el(
									'label', {
										key    : 'block-label',
										htmlFor: 'url-input-' + props.id
									}, gutenBlocksStrings.wp_embed.title
								)
							),
							el(
								'input', {
									key:         'url-input',
									type:        'url',
									id:          'url-input-' + props.id,
									className:   'components-placeholder__input',
									placeholder: gutenBlocksStrings.wp_embed.inputPlaceholder,
									onChange:    onChangeInput
								}
							),
							el(
								'button', {
									key:       'url-button',
									type:      'submit',
									className: 'button-secondary'
								}, gutenBlocksStrings.wp_embed.buttonPlaceholder
							)
						]
					)
				);

			// Display the loader.
			} else if ( ! props.attributes.html ) {
				return el( 'div', {
					className: 'components-placeholder'

				}, el( 'div', {
						key:       'loading',
						className: 'wp-block-embed is-loading'
					}, el( 'span', {
						className: 'spinner is-active'
					} )
				) );
			}

			// Output the WordPress embed.
			return el( 'figure', {
				key:       'wp-embed-output',
				className: 'wp-block-embed'
			}, el(
				'div', {
					className:               'wp-block-embed__wrapper',
					dangerouslySetInnerHTML: { __html: props.attributes.html }
				} )
			);
		},

		save: function( props ) {
			if ( ! props || ! props.attributes.url ) {
				return;
			}

			return el( 'div', null, '\n' + props.attributes.url + '\n' );
		}
	} );

} )( window.wp || {} );
