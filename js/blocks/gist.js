/**
 * Gist block
 */

/* global gutenBlocksStrings, wpApiSettings */
( function( wp ) {
	var el = wp.element.createElement,
		registerBlockType = wp.blocks.registerBlockType,
		createBlock = wp.blocks.createBlock,
		SandBox = wp.components.SandBox,
		blockIcon = el( 'svg', {
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

	registerBlockType( 'gutenblocks/gist', {

		// Block Title
		title: gutenBlocksStrings.gist.title,

		// Description
		description: gutenBlocksStrings.gist.description,

		// Block Icon
		icon: function() {
			return blockIcon;
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

			var fetchGist = function() {
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

			var onUrlSubmit = function( event ) {
				event.preventDefault();

				props.setAttributes( {
					loading: true,
					needsSubmit: false
				} );
			};

			// Output the form to insert a gist.
			if ( ( ! props.attributes.url && ! props.attributes.loading ) || props.attributes.needsSubmit ) {
				props.setAttributes( {
					needsSubmit: true
				} );

				return el(
					'div',
					{
						className: 'components-placeholder wp-block-embed is-large'
					},
					[
						el(
							'div',
							{
								key:       'block-placeholder',
								className: 'components-placeholder__label'
							},
							[
									el(
										'span',
										{
											key    : 'block-icon',
											className: 'block-editor-block-icon has-colors'
										},
										blockIcon
									),
									gutenBlocksStrings.gist.title
							]
						),
						el(
							'div',
							{
								key:       'block-instructions',
								className: 'components-placeholder__instructions'
							},
							gutenBlocksStrings.gist.instructions
						),
						el(
							'div',
							{
								key:       'block-fieldset',
								className: 'components-placeholder__fieldset'
							}, el(
								'form',
								{
									onSubmit: onUrlSubmit
								},
								[
									el(
										'input',
										{
											key:         'url-input',
											type:        'url',
											id:          'url-input-' + props.id,
											className:   'components-placeholder__input',
											placeholder: gutenBlocksStrings.gist.inputPlaceholder,
											onChange:    onChangeInput
										}
									),
									el(
										'button',
										{
											key:       'url-button',
											type:      'submit',
											className: 'components-button is-primary'
										},
										gutenBlocksStrings.gist.buttonPlaceholder
									)
								]
							)
						)
					]
				);

			// Display the loader.
			} else if ( ! props.attributes.src ) {
				fetchGist();

				return el(
					'div',
					{
						key:       'loading',
						className: 'wp-block-embed is-loading'
					}, el(
						'span',
						{
							className: 'spinner is-active'
						}
					)
				);
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
		},

		transforms: {
			from: [
				{
					type: 'raw',
					isMatch: function ( node ) {
						return node.nodeName === 'P' && /^\s*(https?:\/\/gist\.github\.com\S+)\s*$/i.test( node.textContent );
					},
					transform: function( node ) {
						return createBlock( 'gutenblocks/gist', {
							url: node.textContent.trim()
						} );
					}
				}
			]
		}
	} );

} )( window.wp || {} );
