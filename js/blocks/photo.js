/**
 * Photo (url) block
 *
 * NB: this block will disappear as soon as https://github.com/WordPress/gutenberg/issues/1536
 * is fixed.
 */

/* global gutenBlocksStrings */
( function( wp ) {
	var el                = wp.element.createElement,
	    registerBlockType = wp.blocks.registerBlockType,
	    BlockControls     = wp.blocks.BlockControls,
	    AlignmentToolbar  = wp.blocks.AlignmentToolbar,
	    Editable          = wp.blocks.Editable,
	    EditToolbar       = wp.components.Toolbar;

	registerBlockType( 'gutenblocks/photo', {

		// Block Title
		title: gutenBlocksStrings.photo.title,

		// Block Icon
		icon: 'camera',

		// Block Category
		category: 'common',

		// Block Attributes
		attributes: {
			src: {
				type:      'string',
				source:    'attribute',
				selector:  'img',
				attribute: 'src'
			},
			width: {
				type: 'number'
			},
			height: {
				type: 'number'
			},
			caption: {
				type:     'string',
				source:   'children',
				selector: 'figcaption'
			},
			alignment: {
				type: 'string'
			}
		},

		edit: function( props ) {
			var alignment = props.attributes.alignment,
			    focus     = props.focus;

			var onChangeAlignment = function( newAlignment ) {
				props.setAttributes( { alignment: newAlignment } );
			};

			var onChangeInput = function( event ) {
				event.preventDefault();

				props.setAttributes( {
					src:       event.target.value,
					alignment: 'none'
				} );
			};

			var onUrlSubmit = function( event ) {
				event.preventDefault();

				var photo = new Image();
				photo.onload = function() {

					if ( ! props.attributes.src ) {
						return;
					}

					props.setAttributes( {
						width:  photo.width,
						height: photo.height
					} );
				};

				photo.src = props.attributes.src;
			};

			var onChangeCaption = function( newCaption ) {
				props.setAttributes( { caption: newCaption } );
			};

			var onClickEdit = function() {
				props.setAttributes( {
					width:  null,
					height: null,
					src:    null
				} );
			};

			// Output the form to insert a photo.
			if ( ! props.attributes.width || ! props.attributes.src ) {
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
										key: 'block-label',
										for: 'url-input-' + props.id // jshint ignore:line
									}, gutenBlocksStrings.photo.title
								)
							),
							el(
								'input', {
									key:         'url-input',
									type:        'url',
									id:          'url-input-' + props.id,
									className:   'components-placeholder__input',
									placeholder: gutenBlocksStrings.photo.inputPlaceholder,
									onChange:    onChangeInput
								}
							),
							el(
								'button', {
									key:       'url-button',
									type:      'submit',
									className: 'button-secondary'
								}, gutenBlocksStrings.photo.buttonPlaceholder
							)
						]
					)
				);
			}

			// Output the photo.
			return [
				el(
					'figure', {
						key:       'preview-photo',
						className: null,
						style:     { textAlign: props.attributes.alignment }
					}, [
							el(
								'img', {
									key:    'photo-preview',
									src:    props.attributes.src,
									style:  { maxWidth: '100%' }
								}
							),
							el(
								Editable, {
									key:        'photo-caption',
									tagName:    'figcaption',
									placeholder: gutenBlocksStrings.photo.captionPlaceholder,
									value:       props.attributes.caption,
									onChange:    onChangeCaption
								}
							)
						]
				),
				!! focus && el(
					BlockControls,
					{ key: 'edit-photo' },
					[
						el(
							AlignmentToolbar,
							{
								key:      'aligncontrol',
								value:    alignment,
								onChange: onChangeAlignment
							}
						), el(
							EditToolbar,
							{
								key: 'editcontrol',
								controls: [
									{
										icon:    'edit',
										title:   gutenBlocksStrings.photo.editBubble,
										onClick: onClickEdit
									}
								]
							}
						)
					]
				)
			];
		},

		save: function( props ) {
			if ( ! props || ! props.attributes.src || ! props.attributes.width ) {
				return;
			}

			var align     = props.attributes.alignment,
			    caption   = props.attributes.caption,
			    elements  = [
			    	el( 'img', {
			    		key:    'photo-save',
						src:    props.attributes.src,
						width:  props.attributes.width,
						height: props.attributes.height,
						style:  { maxWidth: '100%' }
					} )
				];

			if ( caption && caption.length > 0 ) {
				elements.push( el( 'figcaption', { key: 'photo-caption' }, caption ) );
			}

			return el(
				'figure', {
					key:      'save-photo',
					className: align ? 'align' + align : null,
					style:     align ? { textAlign: align } : 'none'
				}, elements
			);
		}
	} );

} )( window.wp || {} );
