<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Box
 * Description: Display box content
 */
class TB_Box_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __('Box', 'themify'),
			'slug' => 'box'
		));
	}

	public function get_title( $module ) {
		$text = isset( $module['mod_settings']['content_box'] ) ? $module['mod_settings']['content_box'] : '';
		$return = wp_trim_words( $text, 100 );
		return $return;
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_box',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'content_box',
				'type' => 'wp_editor',
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'color_box',
				'type' => 'layout',
				'label' => __( 'Box Color', 'themify' ),
				'options' => Themify_Builder_Model::get_colors(),
				'bottom' => true,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'appearance_box',
				'type' => 'checkbox',
				'label' => __( 'Appearance', 'themify' ),
				'options' => Themify_Builder_Model::get_appearance(),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>' )
			),
			array(
				'id' => 'add_css_box',
				'type' => 'text',
				'label' => __('Additional CSS Class', 'themify'),
				'help' => sprintf( '<br/><small>%s</small>', __('Add additional CSS class(es) for custom styling', 'themify') ),
				'class' => 'large exclude-from-reset-field',
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
		return $options;
	}

	public function get_default_settings() {
		$settings = array(
			'content_box' => esc_html__( 'Box content', 'themify' )
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
				'label' => __('Effect', 'themify'),
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
				'selector' => '.module-box .module-box-content',
				'option_js' => true
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-box .module-box-content',
			),
			// Background repeat
			array(
				'id'		=> 'background_repeat',
				'label'		=> __( 'Background Repeat', 'themify' ),
				'type'		=> 'select',
				'default'	=> '',
				'meta'		=> Themify_Builder_Model::get_background_options(),
				'prop' => 'background-repeat',
				'selector' => '.module-box .module-box-content',
				'wrap_with_class' => 'tf-group-element tf-group-element-image',
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
				'selector' => array('.module-box','.module-box h1','.module-box h2','.module-box h3:not(.module-title)','.module-box h4','.module-box h5','.module-box h6')
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' =>array('.module-box .module-box-content','.module-box h1','.module-box h2','.module-box h3:not(.module-title)','.module-box h4','.module-box h5','.module-box h6')
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
						'selector' => '.module-box'
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
				'label' => __('Line Height', 'themify'),
				'fields' => array(
					array(
						'id' => 'line_height',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-box'
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
				'selector' => '.module-box'
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-box'
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
						'selector' => '.module-box'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-box'
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
				'selector' => '.module-box a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-box a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-box a'
			),
			// Multi columns
			array(
				'id' => 'separator_multi_columns',
				'type' => 'separator',
				'meta' => array( 'html' => '<hr><h4>' . __( 'Multi-columns', 'themify' ) . '</h4>' ),
			),
			array(
				'id' => 'column_count',
				'type' => 'select',
				'label' =>__( 'Column Count', 'themify' ),
				'meta' => array(
					array( 'value' => '', 'name' => '' ),
					array( 'value' => 1, 'name' => 1 ),
					array( 'value' => 2, 'name' => 2 ),
					array( 'value' => 3, 'name' => 3 ),
					array( 'value' => 4, 'name' => 4 ),
					array( 'value' => 5, 'name' => 5 ),
					array( 'value' => 6, 'name' => 6 )
				),
				'prop' => 'column-count',
				'selector' => ' .module-box-content'
			),
			array(
				'id' => 'column_gap',
				'type' => 'text',
				'label' => __( 'Column Gap', 'themify' ),
				'class' => 'style_field_px xsmall',
				'prop' => 'column-gap',
				'selector' => ' .module-box-content'
			),
			array(
				'id' => 'column_divider',
				'type' => 'multi',
				'label' => __( 'Column Divider', 'themify' ),
				'fields' => array(
					array(
						'id' => 'column_divider_color',
						'type' => 'color',
						'class' => 'small',
						'prop' => 'column-rule-color',
						'selector' => ' .module-box-content',
					),
					array(
						'id' => 'column_divider_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_field_px xsmall',
						'prop' => 'column-rule-width',
						'selector' => ' .module-box-content',
					),
					array(
						'id' => 'column_divider_style',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_border_styles(),
						'class' => 'style_field_select',
						'prop' => 'column-rule-style',
						'selector' => ' .module-box-content',
					)
				)
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Padding', 'themify' ) . '</h4>' )
			),
			Themify_Builder_Model::get_field_group( 'padding', '.module-box .module-box-content', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-box .module-box-content', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-box .module-box-content', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-box .module-box-content', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-box .module-box-content', 'all' ),
			// Margin
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_margin',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Margin', 'themify' ) . '</h4>' )
			),
			Themify_Builder_Model::get_field_group( 'margin', '.module-box', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-box', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-box', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-box', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-box', 'all' ),
			// Border
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_border',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Border', 'themify' ) . '</h4>' ),
			),
			Themify_Builder_Model::get_field_group( 'border', '.module-box .module-box-content', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-box .module-box-content', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-box .module-box-content', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-box .module-box-content', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-box .module-box-content', 'all' )
		);

		$heading = array();

		while( count( $heading ) < 6 ) {
			$size = count( $heading ) + 1;
			$selector = '.module.module-box h' . $size;
			$heading[] = array(
				// Font H1
				array(
					'id' => 'separator_font',
					'type' => 'separator',
					'meta' => array( 'html' => '<h4>' . sprintf( __( 'Heading %s Font', 'themify'), $size ) . '</h4>' ),
				),
				array(
					'id' => 'font_family_h' . $size,
					'type' => 'font_select',
					'label' => __( 'Font Family', 'themify' ),
					'class' => 'font-family-select',
					'prop' => 'font-family',
					'selector' => $selector
				),
				array(
					'id' => 'font_color_h' . $size,
					'type' => 'color',
					'label' => __( 'Font Color', 'themify' ),
					'class' => 'small',
					'prop' => 'color',
					'selector' => $selector
				),
				array(
					'id' => 'multi_font_size_h' . $size,
					'type' => 'multi',
					'label' => __( 'Font Size', 'themify' ),
					'fields' => array(
						array(
							'id' => 'font_size_h' . $size,
							'type' => 'text',
							'class' => 'xsmall',
							'prop' => 'font-size',
							'selector' => $selector
						),
						array(
							'id' => 'font_size_h' . $size . '_unit',
							'type' => 'select',
							'meta' => Themify_Builder_Model::get_css_units()
						)
					)
				),
				array(
					'id' => 'multi_line_height_h' . $size,
					'type' => 'multi',
					'label' => __('Line Height', 'themify'),
					'fields' => array(
						array(
							'id' => 'line_height_h' . $size,
							'type' => 'text',
							'class' => 'xsmall',
							'prop' => 'line-height',
							'selector' => $selector
						),
						array(
							'id' => 'line_height_h' . $size . '_unit',
							'type' => 'select',
							'meta' => Themify_Builder_Model::get_css_units()
						)
					)
				),
				Themify_Builder_Model::get_field_group( 'margin', $selector, 'top', 'h' . $size ),
				Themify_Builder_Model::get_field_group( 'margin', $selector, 'bottom', 'h' . $size )
			);
		}

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
					'heading' => array(
						'label' => __( 'Heading', 'themify' ),
						'fields' => call_user_func_array( 'array_merge', $heading )
					)
				)
			),
		);

	}

	protected function _visual_template() { 
		$module_args = $this->get_module_args(); ?>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?>">
			<# if ( data.mod_title_box ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_box }}}<?php echo $module_args['after_title']; ?>
			<# } #>
			
			<div class="ui module-<?php echo esc_attr( $this->slug ); ?>-content {{ data.color_box }} {{ data.add_css_box }} {{ data.background_repeat }} <# ! _.isUndefined( data.appearance_box ) ? print( data.appearance_box.split('|').join(' ') ) : ''; #>">
				{{{ data.content_box }}}
			</div>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Box_Module' );