<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Divider
 * Description: Display Divider
 */
class TB_Divider_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Divider', 'themify' ),
			'slug' => 'divider'
		));
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_divider',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'style_divider',
				'type' => 'layout',
				'label' => __( 'Divider Style', 'themify' ),
				'options' => array(
					array( 'img' => 'solid.png', 'value' => 'solid', 'label' => __( 'Solid', 'themify' ) ),
					array( 'img' => 'dotted.png', 'value' => 'dotted', 'label' => __( 'Dotted', 'themify' ) ),
					array( 'img' => 'dashed.png', 'value' => 'dashed', 'label' => __( 'Dashed', 'themify' ) ),
					array( 'img' => 'double.png', 'value' => 'double', 'label' => __( 'Double', 'themify' ) )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'stroke_w_divider',
				'type' => 'text',
				'label' => __( 'Stroke Thickness', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
				'value'=> 1,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'color_divider',
				'type' => 'text',
				'label' => __( 'Divider Color', 'themify' ),
				'class' => 'small',
				'colorpicker' => true,
				'value' => '000',
				'render_callback' => array(
					'binding' => 'live',
					'control_type' => 'color'
				)
			),
			array(
				'id' => 'top_margin_divider',
				'type' => 'text',
				'label' => __( 'Top Margin', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'bottom_margin_divider',
				'type' => 'text',
				'label' => __( 'Bottom Margin', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'divider_type',
				'type' => 'radio',
				'label' => __( 'Divider Width', 'themify' ),
				'options' => array(
					'fullwidth' => __( 'Fullwidth ', 'themify' ),
					'custom' => __( 'Custom', 'themify' ),
				),
				'default' => 'fullwidth',
				'option_js' => true,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'divider_width',
				'type' => 'text',
				'label' => __( 'Width', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
				'wrap_with_class' => 'tf-group-element tf-group-element-custom',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'divider_align',
				'type' => 'select',
				'label' => __( 'Alignment', 'themify' ),
				'options' => array(
					'left' => __( 'Left ', 'themify' ),
					'center' => __( 'Center', 'themify' ),
					'right' => __( 'Right', 'themify' ),
				),
				'default' => 'left',
				'wrap_with_class' => 'tf-group-element tf-group-element-custom',
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
				'id' => 'css_divider',
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
			'stroke_w_divider' => 1,
			'color_divider' => '000000',
			'divider_width' => 150
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
		return array();
	}

	protected function _visual_template() { 
		$module_args = $this->get_module_args(); ?>
		<#
		var style = '',
			align = 'custom' === data.divider_type && ! _.isUndefined( data.divider_align ) ? 'divider-' + data.divider_align : '';
		if ( data.stroke_w_divider ) style += 'border-width:'+ data.stroke_w_divider +'px; ';
		if ( data.color_divider ) style += 'border-color:' + themifybuilderapp.Utils.toRGBA( data.color_divider ) + '; ';
		if ( data.top_margin_divider ) style += 'margin-top:' + data.top_margin_divider + 'px; ';
		if ( data.bottom_margin_divider ) style += 'margin-bottom:'+ data.bottom_margin_divider +'px; ';
		if ( 'custom' === data.divider_type && data.divider_width > 0 ) style += 'width:'+ data.divider_width +'px; ';
		if ( _.isUndefined( data.style_divider ) ) data.style_divider = 'solid';
		#>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?> divider-{{ data.divider_type }} {{ data.style_divider }} {{ align }} {{ data.css_divider }}" style="{{ style }}">
			<# if ( data.mod_title_divider ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_divider }}}<?php echo $module_args['after_title']; ?>
			<# } #>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Divider_Module' );
