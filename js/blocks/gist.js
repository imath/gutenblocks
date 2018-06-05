/**
 * Gist block
 */

/* global gutenBlocksStrings, wpApiSettings */
( function( wp ) {
	var el                = wp.element.createElement,
	    registerBlockType = wp.blocks.registerBlockType,
	    SandBox           = wp.components.SandBox;

	registerBlockType( 'gutenblocks/gist', {

		// Block Title
		title: gutenBlocksStrings.gist.title,

		// Block Icon
		icon: function() {
			return el( 'svg', {
				'aria-hidden': true,
				role:          'img',
				className:     'dashicon gist-icon',
				focusable:     'false',
				width:         '20',
				height:        '20',
				viewBox:       '0 0 12 16',
				stroke:        '#0073aa',
				strokeWidth:   '0.5',
				xmlns:         'http://www.w3.org/2000/svg'
			}, el( 'path',
					{
						d: 'M7.5 5L10 7.5 7.5 10l-.75-.75L8.5 7.5 6.75 5.75 7.5 5zm-3 0L2 7.5 4.5 10l.75-.75L3.5 7.5l1.75-1.75L4.5 5zM0 13V2c0-.55.45-1 1-1h10c.55 0 1 .45 1 1v11c0 .55-.45 1-1 1H1c-.55 0-1-.45-1-1zm1 0h10V2H1v11z'
					}
				)
			);
		},

		// Block Category
		category: 'embed',

		// Block Attributes
		attributes: {
			url: {
				type: 'string'
			},
			src: {
				type: 'string'
			}
		},

		edit: function( props ) {
			var onChangeInput = function( event ) {
				event.preventDefault();

				props.setAttributes( {
					url: event.target.value
				} );
			};

			var onUrlSubmit = function( event ) {
				event.preventDefault();

				props.setAttributes( {
					loading: true
				} );

				var url = encodeURIComponent( props.attributes.url );

				window.fetch( wpApiSettings.root + 'oembed/1.0/proxy' +
					'?url=' + url +
					'&_wpnonce=' + wpApiSettings.nonce, {
					credentials: 'include'
				} ).then(
					function( response ) {
						response.json().then( function( reply ) {
							props.setAttributes( {
								src : reply.url
							} );
						} );
					}
				);
			};

			// Output the form to insert a gist.
			if ( ! props.attributes.src && ! props.attributes.loading ) {
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
									}, gutenBlocksStrings.gist.title
								)
							),
							el(
								'input', {
									key:         'url-input',
									type:        'url',
									id:          'url-input-' + props.id,
									className:   'components-placeholder__input',
									placeholder: gutenBlocksStrings.gist.inputPlaceholder,
									onChange:    onChangeInput
								}
							),
							el(
								'button', {
									key:       'url-button',
									type:      'submit',
									className: 'button-secondary'
								}, gutenBlocksStrings.gist.buttonPlaceholder
							)
						]
					)
				);

			// Display the loader.
			} else if ( ! props.attributes.src ) {
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

			// Output the gist using a SandBox.
			return el( 'figure', {
				key:       'gist-sandbox',
				className: 'wp-block-embed'
			}, el(
				SandBox, {
					html: '<script src="' + props.attributes.src +'"></script>'
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
