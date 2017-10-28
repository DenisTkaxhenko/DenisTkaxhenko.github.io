<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Widgetized
 * Description: Display any registered sidebar
 */
class TB_Widgetized_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Widgetized', 'themify' ),
			'slug' => 'widgetized'
		));

		add_action( 'themify_builder_lightbox_fields', array( $this, 'widgetized_fields' ), 10, 2 );
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_widgetized',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large'
			),
			array(
				'id' => 'sidebar_widgetized',
				'type' => 'widgetized_select',
				'label' => __( 'Widgetized Area', 'themify' ),
				'class' => 'large',
				'render_callback' => array(
					'control_type' => 'select'
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>' )
			),
			array(
				'id' => 'custom_css_widgetized',
				'type' => 'text',
				'label' => __( 'Additional CSS Class', 'themify' ),
				'help' => sprintf( '<br/><small>%s</small>', __( 'Add additional CSS class(es) for custom styling', 'themify' ) ),
				'class' => 'large exclude-from-reset-field'
			)
		);
		return $options;
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
				'selector' => '.module-widgetized .widget',
				'option_js' => true
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-widgetized .widget',
			),
			// Background repeat
			array(
				'id' => 'background_repeat',
				'label' => __( 'Background Repeat', 'themify' ),
				'type' => 'select',
				'default' => '',
				'meta' => Themify_Builder_Model::get_background_options(),
				'prop' => 'background-repeat',
				'selector' => '.module-widgetized .widget',
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
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-widgetized',
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-widgetized',
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
						'selector' => '.module-widgetized',
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
						'selector' => '.module-widgetized',
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
				'selector' => '.module-widgetized',
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-widgetized'
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
						'selector' => '.module-wedgetized'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-wedgetized'
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
				'selector' => array( '.module-widgetized a' ),
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-widgetized a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => array( '.module-widgetized a' ),
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-widgetized .widget', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-widgetized .widget', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-widgetized .widget', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-widgetized .widget', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-widgetized .widget', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module-widgetized .widget', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-widgetized .widget', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-widgetized .widget', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-widgetized .widget', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-widgetized .widget', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-widgetized .widget', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-widgetized .widget', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-widgetized .widget', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-widgetized .widget', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-widgetized .widget', 'all' )
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
			)
		);
	}

	function widgetized_fields($field, $mod_name) {
		global $wp_registered_sidebars;
		$output = '';

		if ( $mod_name != 'widgetized' ) return;

		switch ( $field['type'] ) {
			case 'widgetized_select':
				$output .= '<select name="'. esc_attr( $field['id'] ) .'" id="'. esc_attr( $field['id'] ) .'" class="tfb_lb_option"'. themify_builder_get_control_binding_data( $field ) .'>';
				$output .= '<option></option>';
				foreach ( $wp_registered_sidebars as $k => $v ) {
					$output .= '<option value="'.esc_attr( $v['id'] ).'">'.esc_html( $v['name'] ).'</option>';
				}
				$output .= '</select>';
			break;
		}
		echo $output;
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Widgetized_Module' );
