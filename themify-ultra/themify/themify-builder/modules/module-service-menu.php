<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Service Menu
 * Description: Display a Service item
 */
class TB_Service_Menu_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Service Menu', 'themify' ),
			'slug' => 'service-menu'
		));
	}

	public function get_title( $module ) {
		return isset( $module['mod_settings']['title_image'] ) ? esc_html( $module['mod_settings']['title_image'] ) : '';
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'style_service_menu',
				'type' => 'layout',
				'label' => __( 'Menu Style', 'themify' ),
				'options' => array(
					array( 'img' => 'image-top.png', 'value' => 'image-top', 'label' => __( 'Image Top', 'themify' ) ),
					array( 'img' => 'image-left.png', 'value' => 'image-left', 'label' => __( 'Image Left', 'themify' ) ),
					array( 'img' => 'image-right.png', 'value' => 'image-right', 'label' => __( 'Image Right', 'themify' ) ),
					array( 'img' => 'image-overlay.png', 'value' => 'image-overlay', 'label' => __( 'Image Overlay', 'themify' ) ),
					array( 'img' => 'image-center.png', 'value' => 'image-center', 'label' => __( 'Centered Image', 'themify' ) )
				)
			),
			array(
				'id' => 'title_service_menu',
				'type' => 'text',
				'label' => __( 'Menu Title', 'themify' ),
				'class' => 'large'
			),
			array(
				'id' => 'description_service_menu',
				'type' => 'textarea',
				'label' => __( 'Description', 'themify' ),
				'class' => 'fullwidth'
			),
			array(
				'id' => 'price_service_menu',
				'type' => 'text',
				'label' => __( 'price', 'themify' ),
				'class' => 'small'
			),
			array(
				'id' => 'image_service_menu',
				'type' => 'image',
				'label' => __( 'Image URL', 'themify' ),
				'class' => 'xlarge'
			),
			array(
				'id' => 'appearance_image_service_menu',
				'type' => 'checkbox',
				'label' => __( 'Image Appearance', 'themify' ),
				'options' => array(
					array( 'name' => 'rounded', 'value' => __( 'Rounded', 'themify' ) ),
					array( 'name' => 'drop-shadow', 'value' => __( 'Drop Shadow', 'themify' ) ),
					array( 'name' => 'bordered', 'value' => __( 'Bordered', 'themify' ) ),
					array( 'name' => 'circle', 'value' => __( 'Circle', 'themify' ), 'help' => __( '(square format image only)', 'themify' ) )
				)
			),
			array(
				'id' => 'image_size_service_menu',
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
				'id' => 'width_service_menu',
				'type' => 'text',
				'label' => __( 'Width', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
				'value' => ''
			),
			array(
				'id' => 'height_service_menu',
				'type' => 'text',
				'label' => __( 'Height', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
				'value' => ''
			),
			array(
				'id' => 'link_service_menu',
				'type' => 'text',
				'label' => __( 'Image Link', 'themify' ),
				'class' => 'fullwidth',
				'binding' => array(
					'empty' => array(
						'hide' => array( 'link_options', 'image_zoom_icon', 'lightbox_size' )
					),
					'not_empty' => array(
						'show' => array( 'link_options', 'image_zoom_icon', 'lightbox_size' )
					)
				)
			),
			array(
				'id' => 'link_options',
				'type' => 'radio',
				'label' => __( 'Open Link In', 'themify' ),
				'options' => array(
					'regular' => __( 'Same window', 'themify' ),
					'lightbox' => __( 'Lightbox ', 'themify' ),
					'newtab' => __( 'New tab ', 'themify' )
				),
				'new_line' => false,
				'default' => 'regular',
				'option_js' => true
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
						'value' => ''
					),
					array(
						'id' => 'lightbox_size_unit_width',
						'type' => 'select',
						'label' => __( 'Units', 'themify' ),
						'options' => array(
							'pixels' => __( 'px ', 'themify' ),
							'percents' => __( '%', 'themify' )
						),
						'default' => 'pixels'
					),
					array(
						'id' => 'lightbox_height',
						'type' => 'text',
						'label' => __( 'Height', 'themify' ),
						'value' => ''
					),
					array(
						'id' => 'lightbox_size_unit_height',
						'type' => 'select',
						'label' => __( 'Units', 'themify' ),
						'options' => array(
							'pixels' => __( 'px ', 'themify' ),
							'percents' => __( '%', 'themify' )
						),
						'default' => 'pixels'
					)
				),
				'wrap_with_class' => 'tf-group-element tf-group-element-lightbox'
			),
			array(
				'id' => 'highlight_service_menu',
				'type' => 'checkbox',
				'label' => __( 'Highlight', 'themify' ),
				'options' => array(
					array( 'name' => 'highlight', 'value' => __( 'Highlight this item', 'themify' ), 'binding' => array(
						'checked' => array(
							'show' => array( 'highlight_text_service_menu', 'highlight_color_service_menu' )
						),
						'not_checked' => array(
							'hide' => array( 'highlight_text_service_menu', 'highlight_color_service_menu' )
						)
					) ),
				),
				'new_line' => false
			),
			array(
				'id' => 'highlight_text_service_menu',
				'type' => 'text',
				'label' => '&nbsp;',
				'after' => __( 'Highlight Text', 'themify' ),
				'class' => 'large'
			),
			array(
				'id' => 'highlight_color_service_menu',
				'type' => 'layout',
				'label' => '&nbsp;',
				'options' => Themify_Builder_Model::get_colors()
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_service_menu',
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
			'title_service_menu' => esc_html__( 'Menu title', 'themify' ),
			'description_service_menu' => esc_html__( 'Description', 'themify' ),
			'price_service_menu' => '$200',
			'style_service_menu' => 'image-left',
			'image_service_menu' => 'https://themify.me/demo/themes/wp-content/uploads/addon-samples/menu-pizza.png',
			'width_service_menu' => 100
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
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-service-menu',
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-service-menu .tb-image-content', '.module-service-menu .tb-image-title', '.module-service-menu .tb-image-title a' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-service-menu .tb-image-content', '.module-service-menu .tb-image-title', '.module-service-menu .tb-image-title a', '.module-service-menu h1', '.module-service-menu h2', '.module-service-menu h3:not(.module-title)', '.module-service-menu h4', '.module-service-menu h5', '.module-service-menu h6' ),
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
						'selector' => '.module-service-menu .tb-image-content'
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
						'selector' => '.module-service-menu .tb-image-content'
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
						'selector' => '.module-service-menu .tb-image-content'
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
				'selector' => '.module-service-menu .tb-image-content'
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-service-menu .tb-image-content'
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
						'selector' => '.module-service-menu .tb-image-content'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-service-menu .tb-image-content'
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
				'selector' => '.module-service-menu a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-service-menu a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-service-menu a'
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-service-menu', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-service-menu', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-service-menu', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-service-menu', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-service-menu', 'all' ),
			// Margin
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_margin',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Margin', 'themify') . '</h4>' ),
			),
			Themify_Builder_Model::get_field_group( 'margin', '.module-service-menu', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-service-menu', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-service-menu', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-service-menu', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-service-menu', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-service-menu', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-service-menu', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-service-menu', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-service-menu', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-service-menu', 'all' )
		);

		$menu_title = array(
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
				'selector' => array( '.module-service-menu .tb-menu-title' )
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-service-menu .tb-menu-title' )
			),
			array(
				'id' => 'font_color_title_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-service-menu .tb-menu-title:hover' )
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
						'selector' => '.module-service-menu .tb-menu-title'
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
						'selector' => '.module-service-menu .tb-menu-title'
					),
					array(
						'id' => 'line_height_title_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$menu_description = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_description',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-service-menu .tb-menu-description'
			),
			array(
				'id' => 'font_color_description',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-service-menu .tb-menu-description'
			),
			array(
				'id' => 'multi_font_size_description',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_description',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-service-menu .tb-menu-description'
					),
					array(
						'id' => 'font_size_description_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_description',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_description',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-service-menu .tb-menu-description'
					),
					array(
						'id' => 'line_height_description_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$price = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_price',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-service-menu .tb-menu-price'
			),
			array(
				'id' => 'font_color_price',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-service-menu .tb-menu-price'
			),
			array(
				'id' => 'multi_font_size_price',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_price',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-service-menu .tb-menu-price'
					),
					array(
						'id' => 'font_size_price_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_price',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_price',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-service-menu .tb-menu-price'
					),
					array(
						'id' => 'line_height_price_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$highlight_text = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_highlight_text',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-service-menu .tb-highlight-text'
			),
			array(
				'id' => 'font_color_highlight_text',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-service-menu .tb-highlight-text'
			),
			array(
				'id' => 'multi_font_size_highlight_text',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_highlight_text',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-service-menu .tb-highlight-text'
					),
					array(
						'id' => 'font_size_highlight_text_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_highlight_text',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_highlight_text',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-service-menu .tb-highlight-text'
					),
					array(
						'id' => 'line_height_highlight_text_unit',
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
					'title' => array(
						'label' => __( 'Menu Title', 'themify' ),
						'fields' => $menu_title
					),
					'caption' => array(
						'label' => __( 'Menu Description', 'themify' ),
						'fields' => $menu_description
					),
					'price' => array(
						'label' => __( 'Price', 'themify' ),
						'fields' => $price
					),
					'highlight_text' => array(
						'label' => __( 'Highlight Text', 'themify' ),
						'fields' => $highlight_text
					),
				)
			),
		);

	}
}
///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Service_Menu_Module' );