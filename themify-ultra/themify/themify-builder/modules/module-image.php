<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Image
 * Description: Display Image content
 */
class TB_Image_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Image', 'themify' ),
			'slug' => 'image'
		));
	}

	public function get_title( $module ) {
		return isset( $module['mod_settings']['title_image'] ) ? esc_html( $module['mod_settings']['title_image'] ) : '';
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_image',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'style_image',
				'type' => 'layout',
				'label' => __( 'Image Style', 'themify' ),
				'options' => array(
					array( 'img' => 'image-top.png', 'value' => 'image-top', 'label' => __( 'Image Top', 'themify' ) ),
					array( 'img' => 'image-left.png', 'value' => 'image-left', 'label' => __( 'Image Left', 'themify' ) ),
					array( 'img' => 'image-right.png', 'value' => 'image-right', 'label' => __( 'Image Right', 'themify' ) ),
					array( 'img' => 'image-overlay.png', 'value' => 'image-overlay', 'label' => __( 'Image Overlay', 'themify' ) ),
					array( 'img' => 'image-center.png', 'value' => 'image-center', 'label' => __( 'Centered Image', 'themify' ) )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'url_image',
				'type' => 'image',
				'label' => __( 'Image URL', 'themify' ),
				'class' => 'xlarge',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'appearance_image',
				'type' => 'checkbox',
				'label' => __( 'Image Appearance', 'themify' ),
				'options' => array(
					array( 'name' => 'rounded', 'value' => __( 'Rounded', 'themify' )),
					array( 'name' => 'drop-shadow', 'value' => __( 'Drop Shadow', 'themify' )),
					array( 'name' => 'bordered', 'value' => __( 'Bordered', 'themify' )),
					array( 'name' => 'circle', 'value' => __( 'Circle', 'themify' ), 'help' => __( '(square format image only)', 'themify' ))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'image_size_image',
				'type' => 'select',
				'label' => Themify_Builder_Model::is_img_php_disabled() ? __( 'Image Size', 'themify' ) : false,
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'hide' => ! Themify_Builder_Model::is_img_php_disabled(),
				'options' => themify_get_image_sizes_list( false ),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'image_fullwidth_container',
				'type' => 'multi',
				'label' => __( 'Width', 'themify' ),
				'fields' => array(
					array(
						'id' => 'width_image',
						'type' => 'text',
						'label' => '',
						'class' => 'xsmall',
						'help' => 'px',
						'value' => '',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'auto_fullwidth',
						'type' => 'checkbox',
						'options' => array( array( 'name' => '1', 'value' => __( 'Auto fullwidth image', 'themify' ) ) ),
						'render_callback' => array(
							'binding' => 'live'
						)
					)
				)
			),
			array(
				'id' => 'height_image',
				'type' => 'text',
				'label' => __( 'Height', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
				'value' => '',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'title_image',
				'type' => 'text',
				'label' => __( 'Image Title', 'themify' ),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'link_image',
				'type' => 'text',
				'label' => __( 'Image Link', 'themify' ),
				'class' => 'fullwidth',
				'binding' => array(
					'empty' => array(
						'hide' => array('param_image', 'image_zoom_icon', 'lightbox_size')
					),
					'not_empty' => array(
						'show' => array('param_image', 'image_zoom_icon', 'lightbox_size')
					)
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'param_image',
				'type' => 'radio',
				'label' => __( 'Open Link In', 'themify' ),
				'options' => array(
					'regular' => __( 'Same window', 'themify' ),
					'lightbox' => __( 'Lightbox ', 'themify' ),
					'newtab' => __( 'New tab ', 'themify' )
				),
				'new_line' => false,
				'default' => 'regular',
				'option_js' => true,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'image_zoom_icon',
				'type' => 'checkbox',
				'label' => false,
				'pushed' => 'pushed',
				'options' => array(
					array( 'name' => 'zoom', 'value' => __( 'Show zoom icon', 'themify' ) )
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-lightbox tf-group-element-newtab',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'lightbox_size',
				'type' => 'multi',
				'label' => __( 'Lightbox Dimension', 'themify' ),
				'fields' => array(
					array(
						'id' => 'lightbox_width',
						'type' => 'text',
						'label' => __( 'Width', 'themify' ),
						'value' => '',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'lightbox_size_unit_width',
						'type' => 'select',
						'label' => __( 'Units', 'themify' ),
						'options' => array(
							'pixels' => __( 'px ', 'themify' ),
							'percents' => __( '%', 'themify' )
						),
						'default' => 'pixels',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'lightbox_height',
						'type' => 'text',
						'label' => __( 'Height', 'themify' ),
						'value' => '',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'lightbox_size_unit_height',
						'type' => 'select',
						'label' => __( 'Units', 'themify' ),
						'options' => array(
							'pixels' => __( 'px ', 'themify' ),
							'percents' => __( '%', 'themify' )
						),
						'default' => 'pixels',
						'render_callback' => array(
							'binding' => 'live'
						)
					)
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-lightbox'
			),
			array(
				'id' => 'caption_image',
				'type' => 'textarea',
				'label' => __( 'Image Caption', 'themify' ),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'alt_image',
				'type' => 'text',
				'label' => __( 'Image Alt Tag', 'themify' ),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_image',
				'type' => 'text',
				'label' => __( 'Additional CSS Class', 'themify' ),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __( 'Add additional CSS class(es) for custom styling', 'themify' ) ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
		return $options;
	}

	public function get_default_settings() {
		$settings = array(
			'url_image' => 'https://themify.me/demo/themes/wp-content/uploads/image-placeholder-small.jpg'
		);
		return $settings;
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'themify' ) . '</h4>')
			),
			array(
				'id' => 'multi_Animation Effect',
				'type' => 'multi',
				'label' => __( 'Effect', 'themify' ),
				'fields' => array(
					array(
						'id' => 'animation_effect',
						'type' => 'animation_select',
						'label' => __( 'Effect', 'themify' )
					),
					array(
						'id' => 'animation_effect_delay',
						'type' => 'text',
						'label' => __( 'Delay', 'themify' ),
						'class' => 'xsmall',
						'description' => __( 'Delay (s)', 'themify' ),
					),
					array(
						'id' => 'animation_effect_repeat',
						'type' => 'text',
						'label' => __( 'Repeat', 'themify' ),
						'class' => 'xsmall',
						'description' => __( 'Repeat (x)', 'themify' ),
					),
				)
			)
		);

		return $animation;
	}

	public function get_styling() {
		$general = array(
			// Background
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Background', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'background_image',
				'type' => 'image_and_gradient',
				'label' => __( 'Background Image', 'themify' ),
				'class' => 'xlarge',
				'prop' => 'background-image',
				'selector' => '.module-image',
				'option_js' => true
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-image',
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__( 'Font', 'themify' ).'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-image .image-content', '.module-image .image-title', '.module-image .image-title a' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-image .image-content', '.module-image .image-title', '.module-image .image-title a', '.module-image h1', '.module-image h2', '.module-image h3:not(.module-title)', '.module-image h4', '.module-image h5', '.module-image h6' ),
			),
			array(
				'id' => 'multi_font_size',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-image .image-content'
					),
					array(
						'id' => 'font_size_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-image .image-content'
					),
					array(
						'id' => 'line_height_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_letter_spacing',
				'type' => 'multi',
				'label' => __( 'Letter Spacing', 'themify' ),
				'fields' => array(
					array(
						'id' => 'letter_spacing',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'letter-spacing',
						'selector' => '.module_row'
					),
					array(
						'id' => 'letter_spacing_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units(),
						'default' => 'px',
					)
				)
			),
			array(
				'id' => 'text_align',
				'label' => __( 'Text Align', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_align(),
				'prop' => 'text-align',
				'selector' => '.module-image .image-content'
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-image .image-content'
			),
			array(
				'id' => 'multi_font_style',
				'type' => 'multi',
				'label' => __( 'Font Style', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_style_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_font_style(),
						'prop' => 'font-style',
						'class' => 'inline',
						'selector' => '.module-image .image-content'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-image .image-content'
					),
				)
			),
			// Link
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_link',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Link', 'themify' ) . '</h4>' ),
			),
			array(
				'id' => 'link_color',
				'type' => 'color',
				'label' => __( 'Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-image a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-image a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-image a'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Padding', 'themify' ) . '</h4>' ),
			),
			Themify_Builder_Model::get_field_group( 'padding', '.module-image', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-image', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-image', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-image', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-image', 'all' ),
			// Margin
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_margin',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Margin', 'themify') . '</h4>' ),
			),
			Themify_Builder_Model::get_field_group( 'margin', '.module-image', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-image', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-image', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-image', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-image', 'all' ),
			// Border
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_border',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Border', 'themify' ) . '</h4>' )
			),
			Themify_Builder_Model::get_field_group( 'border', '.module-image', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-image', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-image', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-image', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-image', 'all' )
		);

		$image_title = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_title',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-image .image-title', '.module-image .image-title a' )
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-image .image-title', '.module-image .image-title a' )
			),
			array(
				'id' => 'font_color_title_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-image .image-title:hover', '.module-image .image-title a:hover' )
			),
			array(
				'id' => 'multi_font_size_title',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_title',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-image .image-title'
					),
					array(
						'id' => 'font_size_title_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_title',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_title',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-image .image-title'
					),
					array(
						'id' => 'line_height_title_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$image_caption = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_caption',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-image .image-content .image-caption'
			),
			array(
				'id' => 'font_color_caption',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-image .image-content .image-caption'
			),
			array(
				'id' => 'multi_font_size_caption',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_caption',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-image .image-content .image-caption'
					),
					array(
						'id' => 'font_size_caption_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_caption',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_caption',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-image .image-content .image-caption'
					),
					array(
						'id' => 'line_height_caption_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		return array(
			array(
				'type' => 'tabs',
				'id' => 'module-styling',
				'tabs' => array(
					'general' => array(
					'label' => __( 'General', 'themify' ),
					'fields' => $general
					),
					'module-title' => array(
						'label' => __( 'Module Title', 'themify' ),
						'fields' => Themify_Builder_Model::module_title_custom_style( $this->slug )
					),
					'title' => array(
						'label' => __( 'Image Title', 'themify' ),
						'fields' => $image_title
					),
					'caption' => array(
						'label' => __( 'Image Caption', 'themify' ),
						'fields' => $image_caption
					)
				)
			),
		);

	}

	protected function _visual_template() { 
		$module_args = $this->get_module_args(); ?>
		<# var fullwidth = data.auto_fullwidth && data.auto_fullwidth == '1' ? 'auto_fullwidth' : ''; #>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ fullwidth }} {{ data.style_image }} {{ data.css_image }} <# ! _.isUndefined( data.appearance_image ) ? print( data.appearance_image.split('|').join(' ') ) : ''; #>">
			<# if ( data.mod_title_image ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_image }}}<?php echo $module_args['after_title']; ?>
			<# } #>
			
			<#
			var attr = data.width_image ? 'width="'+ data.width_image +'" ': '';
			attr += data.height_image ? 'height="' + data.height_image +'" ' : '';
			var style = 'width:' + ( data.width_image ? data.width_image + 'px;' : 'auto;' );
			style += 'height:' + ( data.height_image ? data.height_image + 'px;' : 'auto;' );
			style += 'object-fit: cover;'
				
			var image = '<img src="'+ data.url_image +'" style="' + style + '">';
			#>
			<div class="image-wrap">
				<# if ( data.link_image ) { #>
				<a href="{{ data.link_image }}">
					<# if( data.image_zoom_icon == 'zoom' ) { #>
						<span class="zoom fa <# print( data.param_image == 'lightbox' ? 'fa-search' : 'fa-external-link' ) #>"></span>
					<# } #>
					{{{ image }}}
				</a>
				<# } else { #>
					{{{ image }}}
				<# } #>

				<# if ( 'image-overlay' !== data.style_image ) { #>
				</div>
				<# } #>

				<# if( data.title_image || data.caption_image ) { #>
					<div class="image-content">
						<# if ( data.title_image ) { #>
						<h3 class="image-title">
							<# if ( data.link_image ) { #>
							<a href="{{ data.link_image }}">{{{ data.title_image }}}</a>
							<# } else { #>
							{{{ data.title_image }}}
							<# } #>
						</h3>
						<# } #>

						<# if( data.caption_image ) { #>
						<div class="image-caption">{{{ data.caption_image }}}</div>
						<# } #>
					</div>
				<# } #>
			<# if ( 'image-overlay' === data.style_image ) { #>
				</div>
			<# } #>

		</div>
	<?php
	}
}
///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Image_Module' );
