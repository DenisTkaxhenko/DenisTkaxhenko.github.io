<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Feature
 * Description: Display Feature content
 */
class TB_Feature_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Feature', 'themify' ),
			'slug' => 'feature'
		));
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_feature',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large'
			),
			array(
				'id' => 'title_feature',
				'type' => 'text',
				'label' => __( 'Feature Title', 'themify' ),
				'class' => 'Large'
			),
			array(
				'id' => 'content_feature',
				'type' => 'wp_editor',
				'class' => 'fullwidth'
			),
			array(
				'id' => 'layout_feature',
				'type' => 'layout',
				'label' => __( 'Layout', 'themify' ),
				'options' => array(
					array( 'img' => 'icon-left.png', 'value' => 'icon-left', 'label' => __( 'Icon Left', 'themify' ) ),
					array( 'img' => 'icon-right.png', 'value' => 'icon-right', 'label' => __( 'Icon Right', 'themify' ) ),
					array( 'img' => 'icon-top.png', 'value' => 'icon-top', 'label' => __( 'Icon Top', 'themify' ) )
				)
			),
			array(
				'id' => 'multi_circle_feature',
				'type' => 'multi',
				'label' => __( 'Circle', 'themify' ),
				'fields' => array(
					array(
						'id' => 'circle_percentage_feature',
						'type' => 'text',
						'label' => __( 'Percentage', 'themify' ),
					),
					array(
						'id' => 'circle_stroke_feature',
						'type' => 'text',
						'label' => __( 'Stroke', 'themify' ),
						'class' => 'large',
						'after' => 'px'
					),
					array(
						'id' => 'circle_color_feature',
						'type' => 'text',
						'colorpicker' => true,
						'class' => 'large',
						'label' => __( 'Color', 'themify' ),
					),
					array(
						'id' => 'circle_size_feature',
						'type' => 'select',
						'label' => __( 'Size', 'themify' ),
						'options' => array(
							'small' => __( 'Small', 'themify' ),
							'medium' => __( 'Medium', 'themify' ),
							'large' => __( 'Large', 'themify' )
						)
					),
				)
			),
			array(
				'id' => 'icon_type_feature',
				'type' => 'radio',
				'label' => __( 'Icon Type', 'themify' ),
				'options' => array(
					'icon' => __( 'Icon', 'themify' ),
					'image_icon' => __( 'Image', 'themify' ),
				),
				'default' => 'icon',
				'option_js' => true
			),
			array(
				'id' => 'image_feature',
				'type' => 'image',
				'label' => __( 'Image URL', 'themify' ),
				'class' => 'xlarge',
				'wrap_with_class' => 'tf-group-element tf-group-element-image_icon'
			),
			array(
				'id' => 'multi_icon_feature',
				'type' => 'multi',
				'label' => '&nbsp;',
				'fields' => array(
					array(
						'id' => 'icon_feature',
						'type' => 'icon',
						'label' => __( 'Icon', 'themify' ),
						'wrap_with_class' => 'tf-group-element tf-group-element-icon'
					),
					array(
						'id' => 'icon_color_feature',
						'type' => 'text',
						'colorpicker' => true,
						'label' => __( 'Color', 'themify' ),
						'class' => 'medium',
						'wrap_with_class' => 'tf-group-element tf-group-element-icon'
					),
					array(
						'id' => 'icon_bg_feature',
						'type' => 'text',
						'colorpicker' => true,
						'label' => __( 'Background', 'themify' ),
						'class' => 'medium',
						'wrap_with_class' => 'tf-group-element tf-group-element-icon'
					),
				)
			),
			array(
				'id' => 'link_feature',
				'type' => 'text',
				'label' => __( 'Link', 'themify' ),
				'class' => 'fullwidth',
				'binding' => array(
					'empty' => array(
						'hide' => array( 'link_options', 'lightbox_size' )
					),
					'not_empty' => array(
						'show' => array( 'link_options', 'lightbox_size' )
					)
				)
			),
			array(
				'id' => 'overlap_image_feature',
				'type' => 'image',
				'label' => __( 'Cover Image', 'themify' ),
				'class' => 'xlarge',
				'binding' => array(
					'empty' => array( 'hide' => array( 'overlap_image_size' ) ),
					'not_empty' => array( 'show' => array( 'overlap_image_size' ) ),
				)
			),
			array(
				'id' => 'overlap_image_size',
				'type' => 'multi',
				'label' => '&nbsp;',
				'fields' => array(
					array(
						'id' => 'overlap_image_width',
						'type' => 'text',
						'label' => __( 'Width', 'themify' ),
						'value' => ''
					),
					array(
						'id' => 'overlap_image_height',
						'type' => 'text',
						'label' => __( 'Height', 'themify' ),
						'value' => ''
					),
				),
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
				'option_js' => true,
				'wrap_with_class' => 'link_options'
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
				'wrap_with_class' => 'tf-group-element tf-group-element-lightbox lightbox_size'
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'css_feature',
				'type' => 'text',
				'label' => __( 'Additional CSS Class', 'themify' ),
				'help' => sprintf( '<br/><small>%s</small>', __( 'Add additional CSS class(es) for custom styling', 'themify' ) ),
				'class' => 'large exclude-from-reset-field'
			)
		);
		return $options;
	}

	public function get_default_settings() {
		$settings = array(
			'title_feature' => esc_html__( 'Feature title', 'themify' ),
			'content_feature' => esc_html__( 'Feature content', 'themify' ),
			'circle_percentage_feature' => '100',
			'circle_stroke_feature' => '1',
			'icon_feature' => 'fa-home',
			'layout_feature' => 'icon-top',
			'circle_size_feature' => 'small',
			'circle_color_feature' => 'de5d5d'
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
				'meta' => array( 'html' => '<h4>' . __( 'Background', 'themify' ) . '</h4>' ),
			),
			array(
				'id' => 'background_image',
				'type' => 'image_and_gradient',
				'label' => __( 'Background Image', 'themify' ),
				'class' => 'xlarge',
				'prop' => 'background-image',
				'selector' => '.module-feature',
				'option_js' => true
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-feature',
			),
			// Background repeat
			array(
				'id' => 'background_repeat',
				'label' => __( 'Background Repeat', 'themify' ),
				'type' => 'select',
				'default' => '',
				'meta' => Themify_Builder_Model::get_background_options(),
				'prop' => 'background-repeat',
				'selector' => '.module-feature',
				'wrap_with_class' => 'tf-group-element tf-group-element-image'
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
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
				'selector' => array( '.module-feature', '.module-feature .module-feature-title','.module-feature h1','.module-feature h2','.module-feature h3:not(.module-title)','.module-feature h4','.module-feature h5','.module-feature h6'),
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-feature', '.module-feature h1', '.module-feature h2', '.module-feature h3', '.module-feature h4', '.module-feature h5', '.module-feature h6', '.module-feature .module-feature-title' )
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
						'selector' => '.module-feature'
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
						'selector' => '.module-feature'
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
				'selector' => '.module-feature'
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-feature'
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
						'selector' => '.module-feature'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-feature'
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
				'selector' => '.module-feature a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-feature a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-feature a'
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-feature', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-feature', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-feature', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-feature', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-feature', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module-feature', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-feature', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-feature', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-feature', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-feature', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-feature', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-feature', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-feature', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-feature', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-feature', 'all' )
		);

		$feature_title = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' ),
			),
			array(
				'id' => 'font_family_title',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module-feature .module-feature-title:not(.module-title)', '.module-feature .module-feature-title a' )
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-feature .module-feature-title:not(.module-title)', '.module-feature .module-feature-title a' )
			),
			array(
				'id' => 'font_color_title_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-feature .module-feature-title:not(.module-title):hover', '.module-feature .module-feature-title a:hover' )
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
						'selector' => '.module-feature .module-feature-title:not(.module-title)'
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
						'selector' => '.module-feature .module-feature-title:not(.module-title)'
					),
					array(
						'id' => 'line_height_title_unit',
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
						'label' => __( 'Feature Title', 'themify' ),
						'fields' => $feature_title
					)
				)
			),
		);

	}
}
///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Feature_Module' );