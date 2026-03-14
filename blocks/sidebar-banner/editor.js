( function ( blocks, element, blockEditor, components, data ) {
	var el = element.createElement;
	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var RichText = blockEditor.RichText;
	var MediaUpload = blockEditor.MediaUpload;
	var PanelBody = components.PanelBody;
	var RangeControl = components.RangeControl;
	var TextControl = components.TextControl;
	var Button = components.Button;
	var ColorPicker = components.ColorPicker;

	blocks.registerBlockType( 'hub21-base/sidebar-banner', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			var gifUrl = attributes.gifUrl || '';
			var defaultGif = '/wp-content/uploads/2025/08/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-2.gif';
			var displayGif = gifUrl || defaultGif;

			return el(
				element.Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: 'Banner Settings', initialOpen: true },
						el( RangeControl, {
							label: 'Banner Height (px)',
							value: attributes.bannerHeight,
							onChange: function ( val ) { setAttributes( { bannerHeight: val } ); },
							min: 60,
							max: 300,
							step: 10,
						} ),
						el( RangeControl, {
							label: 'Tint Opacity (%)',
							value: attributes.tintOpacity,
							onChange: function ( val ) { setAttributes( { tintOpacity: val } ); },
							min: 0,
							max: 100,
							step: 5,
						} ),
						el( 'div', { style: { marginBottom: '16px' } },
							el( 'label', { style: { display: 'block', marginBottom: '8px', fontWeight: 600 } }, 'Tint Color' ),
							el( ColorPicker, {
								color: attributes.tintColor,
								onChangeComplete: function ( val ) { setAttributes( { tintColor: val.hex } ); },
								disableAlpha: true,
							} )
						),
						el( MediaUpload, {
							onSelect: function ( media ) { setAttributes( { gifUrl: media.url } ); },
							allowedTypes: [ 'image' ],
							render: function ( obj ) {
								return el(
									'div',
									null,
									el( 'label', { style: { display: 'block', marginBottom: '8px', fontWeight: 600 } }, 'Background Image' ),
									el( Button, {
										onClick: obj.open,
										variant: 'secondary',
										style: { marginBottom: '8px' },
									}, gifUrl ? 'Replace Image' : 'Select Image' ),
									gifUrl ? el( Button, {
										onClick: function () { setAttributes( { gifUrl: '' } ); },
										variant: 'link',
										isDestructive: true,
									}, 'Reset to default' ) : null
								);
							},
						} ),
						el( 'div', { style: { marginTop: '16px', padding: '12px', background: '#f0f0f0', borderRadius: '4px', fontSize: '12px' } },
							el( 'strong', null, 'Tip: ' ),
							'Use ',
							el( 'code', null, '{first_name}' ),
							' in the heading to show the logged-in user\'s name.'
						)
					)
				),
				el(
					'div',
					blockProps,
					el(
						'div',
						{
							className: 'sidebar-header-banner',
							style: { height: attributes.bannerHeight + 'px' },
						},
						el( 'img', {
							src: displayGif,
							alt: '',
							className: 'sidebar-header-banner-bg',
						} ),
						el( 'div', {
							className: 'sidebar-header-banner-tint',
							style: {
								background: attributes.tintColor,
								opacity: attributes.tintOpacity / 100,
							},
						} ),
						el( 'div', { className: 'sidebar-header-banner-glass' } ),
						el(
							'div',
							{ className: 'sidebar-header-banner-content' },
							el( RichText, {
								tagName: 'h2',
								className: 'sidebar-header-banner-heading',
								value: attributes.heading,
								onChange: function ( val ) { setAttributes( { heading: val } ); },
								placeholder: 'Welcome to Hub 21, {first_name}!',
								allowedFormats: [ 'core/bold', 'core/italic' ],
							} )
						)
					)
				)
			);
		},

		save: function () {
			return null; // Dynamic block, rendered by PHP
		},
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.data
);
