<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Fancy Heading
 * Description: Heading with fancy styles
 */
class TB_Fancy_Heading_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Fancy Heading', 'themify' ),
			'slug' => 'fancy-heading'
		));
	}

	public function get_title( $module ) {
		return isset( $module['mod_settings']['heading'] ) ? esc_html( $module['mod_settings']['heading'] ) : '';
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'heading',
				'type' => 'text',
				'label' => __( 'Heading', 'themify' ),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'sub_heading',
				'type' => 'text',
				'label' => __( 'Sub Heading', 'themify' ),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'heading_tag',
				'label' => __( 'HTML Tag', 'themify' ),
				'type' => 'select',
				'options' => array(
					'h1' => __( 'h1', 'themify' ),
					'h2' => __( 'h2', 'themify' ),
					'h3' => __( 'h3', 'themify' )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'text_alignment',
				'label' => __( 'Text Alignment', 'themify' ),
				'type' => 'select',
				'options' => array(
					'themify-text-center' => __( 'Center', 'themify' ),
					'themify-text-left' => __( 'Left', 'themify' ),
					'themify-text-right' => __( 'Right', 'themify' )
				),
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
				'id' => 'css_class',
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
			'heading' => esc_html__( 'Heading', 'themify' ),
			'sub_heading' => esc_html__( 'Sub Heading', 'themify' ),
			'heading_tag' => 'h1',
			'text_alignment' => 'themify-text-center',
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
				'label' => __('Effect', 'themify' ),
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
				'selector' => '.module-fancy-heading',
				'option_js' => true,
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-fancy-heading',
			),
			// Background repeat
			array(
				'id' 		=> 'background_repeat',
				'label'		=> __( 'Background Repeat', 'themify' ),
				'type' 		=> 'select',
				'default'	=> '',
				'meta'		=> Themify_Builder_Model::get_background_options(),
				'prop' => 'background-repeat',
				'selector' => '.module-fancy-heading',
				'wrap_with_class' => 'tf-group-element tf-group-element-image',
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-fancy-heading', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-fancy-heading', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-fancy-heading', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-fancy-heading', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-fancy-heading', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading', 'all' ),
			// Border
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_border',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Border', 'themify' ) . '</h4>' )
			),
			Themify_Builder_Model::get_field_group( 'border', '.module-fancy-heading', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-fancy-heading', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-fancy-heading', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-fancy-heading', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-fancy-heading', 'all' )
		);

		$heading = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__('Font', 'themify').'</h4>'),
			),
			array(
				'id' => 'font_family',
				'type' => 'font_select',
				'label' => __('Font Family', 'themify'),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module .main-head' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __('Font Color', 'themify'),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module .main-head' )
			),
			array(
				'id' => 'multi_font_size',
				'type' => 'multi',
				'label' => __('Font Size', 'themify'),
				'fields' => array(
					array(
						'id' => 'font_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => array( '.module .main-head' )
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
				'label' => __(' Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => array( '.module .main-head' )
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
			// Main Heading Margin
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading .fancy-heading .main-head', 'top', 'main' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading .fancy-heading .main-head', 'bottom', 'main' )
		);

		$subheading = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__(' Font', 'themify' ).'</h4>'),
			),
			array(
				'id' => 'font_family_subheading',
				'type' => 'font_select',
				'label' => __(' Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array( '.module .sub-head' )
			),
			array(
				'id' => 'font_color_subheading',
				'type' => 'color',
				'label' => __(' Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module .sub-head' )
			),
			array(
				'id' => 'multi_font_size_subheading',
				'type' => 'multi',
				'label' => __(' Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_subheading',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => array( '.module .sub-head' )
					),
					array(
						'id' => 'font_size_subheading_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_subheading',
				'type' => 'multi',
				'label' => __(' Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_subheading',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => array( '.module .sub-head' )
					),
					array(
						'id' => 'line_height_subheading_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			// Sub Heading Margin
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading .fancy-heading .sub-head', 'top', 'sub' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-fancy-heading .fancy-heading .sub-head', 'bottom', 'sub' )
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
					'heading' => array(
						'label' => __( 'Heading', 'themify' ),
						'fields' => $heading
					),
					'subheading' => array(
						'label' => __( 'Sub Heading', 'themify' ),
						'fields' => $subheading
					),
				)
			),
		);
	}

	protected function _visual_template() { 
		$module_args = $this->get_module_args(); ?>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ data.css_class }}">
			<# 
			var heading_tag = _.isUndefined( data.heading_tag ) ? 'h1' : data.heading_tag,
				text_alignment = _.isUndefined( data.text_alignment ) ? 'themify-text-center' : data.text_alignment;
			#>
			<{{ heading_tag }} class="fancy-heading {{ text_alignment }}">
				<span class="main-head">{{{ data.heading }}}</span>
				<span class="sub-head">{{{ data.sub_heading }}}</span>
			</{{ heading_tag }}>
		</div>
	<?php
	}
}
///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Fancy_Heading_Module' );