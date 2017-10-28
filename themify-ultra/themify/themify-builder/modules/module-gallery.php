<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Gallery
 * Description: Display WP Gallery Images
 */
class TB_Gallery_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Gallery', 'themify' ),
			'slug' => 'gallery'
		));
	}

	public function get_options() {
		$columns = range( 0, 9 );
		unset( $columns[0] );
		$options = array(
			array(
				'id' => 'mod_title_gallery',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large'
			),
			array(
				'id' => 'layout_gallery',
				'type' => 'radio',
				'label' => __( 'Gallery Layout', 'themify' ),
				'options' => array(
					'grid' => __( 'Grid', 'themify' ),
					'showcase' => __( 'Showcase', 'themify' ),
					'lightboxed' => __( 'Lightboxed', 'themify' ),
				),
				'default' => 'grid',
				'option_js' => true,
			),
			array(
				'id' => 'gallery_columns',
				'type' => 'select',
				'label' => false,
				'pushed' => 'pushed',
				'after' => __( 'Columns', 'themify' ),
				'options' => $columns,
				'wrap_with_class' => 'tf-group-element tf-group-element-grid'
			),
			array(
				'id' => 'layout_masonry',
				'type' => 'checkbox',
				'label' => false,
				'pushed' => 'pushed',
				'options' => array(
					array( 'name' => 'masonry', 'value' => __( 'Use Masonry', 'themify' ) )
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid',
			),
			array(
				'id' => 'thumbnail_gallery',
				'type' => 'image',
				'label' => __( 'Thumbnail', 'themify' ),
				'class' => 'large',
				'wrap_with_class' => 'tf-group-element tf-group-element-lightboxed'
			),
			array(
				'id' => 'shortcode_gallery',
				'type' => 'textarea',
				'class' => 'tf-thumbs-preview tf-hide tf-shortcode-input',
				'label' => __( 'Insert Gallery Shortcode', 'themify' ),
				'help' => sprintf('<a href="#" class="builder_button tf-gallery-btn">%s</a>', __( 'Insert Gallery', 'themify' )),
				'render_callback' => array(
					'control_type' => 'textonchange'
				)
			),
			array(
				'id' => 'gallery_pagination',
				'type' => 'checkbox',
				'label' => __( 'Pagination', 'themify' ),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid',
				'options' => array( array( 'name' => 'pagination', 'value' => '' ) ),
				'option_js' => true,
			),
			array(
				'id' => 'gallery_per_page',
				'type' => 'text',
				'label' => __( 'Images per page', 'themify' ),
				'wrap_with_class' => 'ui-helper-hidden tf-group-element tf-checkbox-element tf-checkbox-element-pagination',
				'class' => 'xsmall',
			),
			array(
				'id' => 'gallery_image_title',
				'type' => 'checkbox',
				'label' => __( 'Image Title', 'themify' ),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid tf-group-element-lightboxed',
				'options' => array(array( 'value' => __( 'Display library image title', 'themify' ), 'name' =>'library' ) ),
			),
			array(
				'id' => 'gallery_exclude_caption',
				'type' => 'checkbox',
				'label' => __( 'Exclude Caption', 'themify' ),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid tf-group-element-lightboxed',
				'options' => array(array( 'value' => __( 'Hide Image Caption', 'themify' ), 'name' =>'yes' ) ),
			),
			array(
				'id' => 's_image_w_gallery',
				'type' => 'text',
				'label' => __( 'Showcase Image Width', 'themify' ),
				'class' => 'xsmall',
				'hide' => Themify_Builder_Model::is_img_php_disabled(),
				'help' => 'px',
				'wrap_with_class' => 'tf-group-element tf-group-element-showcase'
			),
			array(
				'id' => 's_image_h_gallery',
				'type' => 'text',
				'label' => __( 'Showcase Image Height', 'themify' ),
				'class' => 'xsmall',
				'hide' => Themify_Builder_Model::is_img_php_disabled(),
				'help' => 'px',
				'wrap_with_class' => 'tf-group-element tf-group-element-showcase'
			),
			array(
				'id' => 's_image_size_gallery',
				'type' => 'select',
				'label' => Themify_Builder_Model::is_img_php_disabled() ? __( 'Main Image Size', 'themify' ) : false,
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'hide' => ! Themify_Builder_Model::is_img_php_disabled(),
				'options' => themify_get_image_sizes_list( false )
			),
			array(
				'id' => 'thumb_w_gallery',
				'type' => 'text',
				'label' => __( 'Thumbnail Width', 'themify' ),
				'class' => 'xsmall',
				'hide' => Themify_Builder_Model::is_img_php_disabled(),
				'help' => 'px'
			),
			array(
				'id' => 'thumb_h_gallery',
				'type' => 'text',
				'label' => __( 'Thumbnail Height', 'themify' ),
				'class' => 'xsmall',
				'hide' => Themify_Builder_Model::is_img_php_disabled(),
				'help' => 'px'
			),
			array(
				'id' => 'image_size_gallery',
				'type' => 'select',
				'label' => Themify_Builder_Model::is_img_php_disabled() ? __( 'Image Size', 'themify' ) : false,
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'hide' => ! Themify_Builder_Model::is_img_php_disabled(),
				'options' => themify_get_image_sizes_list( false )
			),
			array(
				'id' => 'link_opt',
				'type' => 'select',
				'label' => __( 'Link to', 'themify' ),
				'options' => array(
					'post' => __( 'Attachment Page','themify' ),
					'file' => __( 'Media File','themify' ),
					'none' => __( 'None','themify' )
				),
				'default' => __( 'Media File','themify' ),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid',
				'binding' => array(
					'file' => array( 'show' => array( 'link_image_size' ) ),
					'post' => array( 'hide' => array( 'link_image_size' ) ),
					'none' => array( 'hide' => array( 'link_image_size' ) ),
				),
			),
			array(
				'id' => 'link_image_size',
				'type' => 'select',
				'label' => __( 'Link to Image Size', 'themify' ),
				'options' => themify_get_image_sizes_list( false ),
				'default' => __( 'Original Image', 'themify' ),
				'wrap_with_class' => 'tf-group-element tf-group-element-grid'
			),
			array(
				'id' => 'appearance_gallery',
				'type' => 'checkbox',
				'label' => __( 'Image Appearance', 'themify' ),
				'options' => array(
					array( 'name' => 'rounded', 'value' => __( 'Rounded', 'themify' )),
					array( 'name' => 'drop-shadow', 'value' => __( 'Drop Shadow', 'themify' )),
					array( 'name' => 'bordered', 'value' => __( 'Bordered', 'themify' )),
					array( 'name' => 'circle', 'value' => __( 'Circle', 'themify' ), 'help' => __( '(square format image only)', 'themify' ))
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>' )
			),
			array(
				'id' => 'css_gallery',
				'type' => 'text',
				'label' => __( 'Additional CSS Class', 'themify' ),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __( 'Add additional CSS class(es) for custom styling', 'themify' ) )
			)
		);
		return $options;
	}

	public function get_default_settings() {
		$settings = array(
			'gallery_columns' => 4
		);
		return $settings;
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'themify' ) . '</h4>' )
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
				'selector' => '.module-gallery',
				'option_js' => true
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-gallery',
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />' )
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' ),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-gallery',
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-gallery',
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
						'selector' => '.module-gallery',
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
						'selector' => '.module-gallery',
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
				'selector' => '.module-gallery',
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-gallery'
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
						'selector' => '.module-gallery'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-gallery'
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
				'meta' => array( 'html' => '<h4>' . __( 'Link', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'link_color',
				'type' => 'color',
				'label' => __( 'Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-gallery a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-gallery a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-gallery a'
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-gallery', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-gallery', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-gallery', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-gallery', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-gallery', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module-gallery', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-gallery', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-gallery', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-gallery', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-gallery', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-gallery', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-gallery', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-gallery', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-gallery', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-gallery', 'all' )
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
					)
				)
			),
		);

	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Gallery_Module' );