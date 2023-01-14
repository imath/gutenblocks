/**
 * GitHub Release block
 */

/* global gutenBlocksStrings */
( function( wp ) {
	var el                = wp.element.createElement,
	    registerBlockType = wp.blocks.registerBlockType,
	    githubReleaseIcon = el( 'svg', {
			'aria-hidden': true,
			role:          'img',
			key:           'logo-release',
			className:     'dashicon release-icon',
			focusable:     'false',
			width:         '20',
			height:        '20',
			viewBox:       '0 0 16 16',
			stroke:        '#0073aa',
			strokeWidth:   '0.5',
			xmlns:         'http://www.w3.org/2000/svg'
		}, el( 'path',
				{
					d: 'M1 4.27v7.47c0 .45.3.84.75.97l6.5 1.73c.16.05.34.05.5 0l6.5-1.73c.45-.13.75-.52.75-.97V4.27c0-.45-.3-.84-.75-.97l-6.5-1.74a1.4 1.4 0 0 0-.5 0L1.75 3.3c-.45.13-.75.52-.75.97zm7 9.09l-6-1.59V5l6 1.61v6.75zM2 4l2.5-.67L11 5.06l-2.5.67L2 4zm13 7.77l-6 1.59V6.61l2-.55V8.5l2-.53V5.53L15 5v6.77zm-2-7.24L6.5 2.8l2-.53L15 4l-2 .53z'
				}
			)
		);

	registerBlockType( 'gutenblocks/release', {

		// Block Title
		title: gutenBlocksStrings.release.title,

		// Description
		description: gutenBlocksStrings.release.description,

		// Block Icon
		icon: function() {
			return githubReleaseIcon;
		},

		// Block Category
		category: 'widgets',

		// Block Attributes
		attributes: {
			name: {
				type: 'string'
			},
			label: {
				type: 'string'
			},
			tag: {
				type: 'string'
			},
			logo: {
				type: 'string'
			},
			notes: {
				type: 'string'
			}
		},

		edit: function( props ) {
			var download = 'https://github.com/' + gutenBlocksStrings.release.ghUsername,
			    link = download, image = githubReleaseIcon, downloadText = gutenBlocksStrings.release.downloadHTML;

			var current = Object.assign( {
				name: '',
				tag:  '',
				label: '',
				logo: '',
				notes: ''
			}, props.attributes );

			var onNameBlur = function( event ) {
				event.preventDefault();

				if ( ! props.attributes.name || props.attributes.name.length <= 2 ) {
					return;
				}

				var logo = new Image();
				logo.onload = function() {
					props.setAttributes( {
						logo:  this.src
					} );
				};

				logo.src = 'https://raw.githubusercontent.com/' + gutenBlocksStrings.release.ghUsername + '/' + props.attributes.name + '/master/icon.png';
			};

			if ( props.attributes.name ) {
				download += '/' + props.attributes.name;
				link      = download;

				if ( props.attributes.tag ) {
					link        += '/releases/tag/' + props.attributes.tag;
					download    += '/releases/download/' + props.attributes.tag + '/' + props.attributes.name + '.zip' ;
					downloadText = downloadText.replace( '%s', props.attributes.tag );
				}
			}

			if ( props.attributes.logo ) {
				image = el( 'img', {
					key:      'logo-release',
					src:       props.attributes.logo,
					className: 'release-icon'
				} );
			}

			// Output the form to build the release card.
			return el( 'div', {
				key:       'release-card',
				className: 'plugin-card'
			}, [
				el( 'div', {
					key:       'release-required-fields',
					className: 'required-fields'
				},
					[
						el( 'label', {
							key    : 'name-input-label',
							htmlFor: 'name-input-' + props.id
						}, gutenBlocksStrings.release.namePlaceholder ),
						el( 'span', {
							key      : 'name-input-required',
							className: 'required'
						}, ' * ' ),
						el( 'input', {
							id         : 'name-input-' + props.id,
							key        : 'name-input',
							type       : 'text',
							value      : current.name,
							placeholder: gutenBlocksStrings.release.namePlaceholder,
							onChange   : function( event ) { props.setAttributes( { name: event.target.value } ); },
							onBlur     : onNameBlur
						} ),
						el( 'label', {
							key      : 'tag-input-label',
							htmlFor      : 'tag-input-' + props.id
						}, gutenBlocksStrings.release.tagPlaceholder ),
						el( 'span', {
							key      : 'tag-input-required',
							className: 'required'
						}, ' * ' ),
						el( 'input', {
							id         : 'tag-input-' + props.id,
							key        : 'tag-input',
							type       : 'text',
							value      : current.tag,
							placeholder: gutenBlocksStrings.release.tagPlaceholder,
							onChange   : function( event ) { props.setAttributes( { tag: event.target.value } ); }
						} )
					]
				),
				el( 'div', {
					className: 'plugin-card-top',
					key      : 'release-card-top'
				},
					[
						el(
							'h3', {
								key: 'h3-card'
							}, [
								image,
								el( 'label', {
									key      : 'label-input-label',
									htmlFor      : 'label-input-' + props.id,
									className: 'screen-reader-text'
								}, gutenBlocksStrings.release.labelPlaceholder ),
								el( 'input', {
									id         : 'label-input-' + props.id,
									key        : 'label-input',
									type       : 'text',
									value      : current.label,
									placeholder: gutenBlocksStrings.release.labelPlaceholder,
									onChange   : function( event ) { props.setAttributes( { label: event.target.value } ); }
								} )
							]
						),
						el( 'div', {
							className: 'desc column-description',
							key      : 'release-description'
						}, [
								el( 'label', {
									key      : 'notes-input-label',
									htmlFor      : 'notes-input-' + props.id,
									className: 'screen-reader-text'
								}, gutenBlocksStrings.release.notesPlaceholder ),
								el( 'textarea', {
									id         : 'notes-input-' + props.id,
									key        : 'notes-input',
									value      : current.notes,
									placeholder: gutenBlocksStrings.release.notesPlaceholder,
									onChange   : function( event ) { props.setAttributes( { notes: event.target.value } ); }
								} )
							]
						),
						el( 'div', {
							className: 'download wp-block-button',
							key      : 'release-download'
						}, el( 'button', {
							key      : 'download-button',
							className: 'button submit gh-download-button wp-element-button'
							}, [
									el( 'span', {
										key : 'download-icon',
										className: 'dashicons dashicons-download'
									} ),
									el( 'a', {
										key : 'download-link',
										href: download
									}, downloadText )
							]
						) )
					]
				)
			] );
		},

		save: function() {
			return null;
		}
	} );

} )( window.wp || {} );
