<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Callout
 * Description: Display Callout content
 */

class TB_Callout_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Callout', 'themify' ),
			'slug' => 'callout'
		));
	}

	public function get_title( $module ) {
		$text = isset( $module['mod_settings']['heading_callout'] ) ? $module['mod_settings']['heading_callout'] : '';
		$return = wp_trim_words( $text, 100 );
		return $return;
	}

	public function get_plain_text( $module ) {
		$text = '';
		if( isset( $module['heading_callout'] ) )
			$text .= $module['heading_callout'];
		if( isset( $module['text_callout'] ) )
			$text .= $module['text_callout'];
		return $text;
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_callout',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'layout_callout',
				'type' => 'layout',
				'label' => __( 'Callout Style', 'themify' ),
				'options' => array(
					array('img' => 'callout-button-right.png', 'value' => 'button-right', 'label' => __( 'Button Right', 'themify' )),
					array('img' => 'callout-button-left.png', 'value' => 'button-left', 'label' => __( 'Button Left', 'themify' )),
					array('img' => 'callout-button-bottom.png', 'value' => 'button-bottom', 'label' => __( 'Button Bottom', 'themify' )),
					array('img' => 'callout-button-bottom-center.png', 'value' => 'button-bottom-center', 'label' => __( 'Button Bottom Center', 'themify' ))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'heading_callout',
				'type' => 'text',
				'label' => __( 'Callout Heading', 'themify' ),
				'class' => 'xlarge',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'text_callout',
				'type' => 'textarea',
				'label' => __( 'Callout Text', 'themify' ),
				'class' => 'fullwidth',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'color_callout',
				'type' => 'layout',
				'label' => __('Callout Color', 'themify'),
				'options' => array(
					array('img' => 'color-default.png', 'value' => 'default', 'label' => __('Default', 'themify')),
					array('img' => 'color-black.png', 'value' => 'black', 'label' => __('Black', 'themify')),
					array('img' => 'color-grey.png', 'value' => 'gray', 'label' => __('Gray', 'themify')),
					array('img' => 'color-blue.png', 'value' => 'blue', 'label' => __('Blue', 'themify')),
					array('img' => 'color-light-blue.png', 'value' => 'light-blue', 'label' => __('Light-blue', 'themify')),
					array('img' => 'color-green.png', 'value' => 'green', 'label' => __('Green', 'themify')),
					array('img' => 'color-light-green.png', 'value' => 'light-green', 'label' => __('Light-green', 'themify')),
					array('img' => 'color-purple.png', 'value' => 'purple', 'label' => __('Purple', 'themify')),
					array('img' => 'color-light-purple.png', 'value' => 'light-purple', 'label' => __('Light-purple', 'themify')),
					array('img' => 'color-brown.png', 'value' => 'brown', 'label' => __('Brown', 'themify')),
					array('img' => 'color-orange.png', 'value' => 'orange', 'label' => __('Orange', 'themify')),
					array('img' => 'color-yellow.png', 'value' => 'yellow', 'label' => __('Yellow', 'themify')),
					array('img' => 'color-red.png', 'value' => 'red', 'label' => __('Red', 'themify')),
					array('img' => 'color-pink.png', 'value' => 'pink', 'label' => __('Pink', 'themify')),
					array('img' => 'color-transparent.png', 'value' => 'transparent', 'label' => __('Transparent', 'themify'))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'appearance_callout',
				'type' => 'checkbox',
				'label' => __( 'Callout Appearance', 'themify' ),
				'options' => Themify_Builder_Model::get_appearance(),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'action_btn_link_callout',
				'type' => 'text',
				'label' => __( 'Action Button Link', 'themify' ),
				'class' => 'xlarge',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'open_link_new_tab_callout',
				'type' => 'select',
				'label' => __( 'Open link in a new tab', 'themify' ),
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'options' => array(
					'yes' => __( 'Yes', 'themify' ),
					'no' => __( 'No', 'themify' )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'action_btn_text_callout',
				'type' => 'text',
				'label' => __( 'Action Button Text', 'themify' ),
				'class' => 'medium',
				'render_callback' => array(
					'binding' => 'live'
				)
				//'help' => __( 'If button text is empty = default text: More' ,'themify' ),
				//'break' => true
			),
			array(
				'id' => 'action_btn_color_callout',
				'type' => 'layout',
				'label' => __('Action Button Color', 'themify'),
				'options' => array(
					array('img' => 'color-default.png', 'value' => 'default', 'label' => __('Default', 'themify')),
					array('img' => 'color-black.png', 'value' => 'black', 'label' => __('Black', 'themify')),
					array('img' => 'color-grey.png', 'value' => 'gray', 'label' => __('Gray', 'themify')),
					array('img' => 'color-blue.png', 'value' => 'blue', 'label' => __('Blue', 'themify')),
					array('img' => 'color-light-blue.png', 'value' => 'light-blue', 'label' => __('Light-blue', 'themify')),
					array('img' => 'color-green.png', 'value' => 'green', 'label' => __('Green', 'themify')),
					array('img' => 'color-light-green.png', 'value' => 'light-green', 'label' => __('Light-green', 'themify')),
					array('img' => 'color-purple.png', 'value' => 'purple', 'label' => __('Purple', 'themify')),
					array('img' => 'color-light-purple.png', 'value' => 'light-purple', 'label' => __('Light-purple', 'themify')),
					array('img' => 'color-brown.png', 'value' => 'brown', 'label' => __('Brown', 'themify')),
					array('img' => 'color-orange.png', 'value' => 'orange', 'label' => __('Orange', 'themify')),
					array('img' => 'color-yellow.png', 'value' => 'yellow', 'label' => __('Yellow', 'themify')),
					array('img' => 'color-red.png', 'value' => 'red', 'label' => __('Red', 'themify')),
					array('img' => 'color-pink.png', 'value' => 'pink', 'label' => __('Pink', 'themify')),
					array('img' => 'color-transparent.png', 'value' => 'transparent', 'label' => __('Transparent', 'themify'))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'action_btn_appearance_callout',
				'type' => 'checkbox',
				'label' => __( 'Action Button Appearance', 'themify' ),
				'options' => Themify_Builder_Model::get_appearance(),
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
				'id' => 'css_callout',
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
			'heading_callout' => esc_html__( 'Callout Heading', 'themify' ),
			'text_callout' => esc_html__( 'Callout Text', 'themify' ),
			'action_btn_text_callout' => esc_html__( 'Action button', 'themify' ),
			'action_btn_link_callout' => 'https://themify.me/',
			'action_btn_color_callout' => 'blue'
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
				'selector' => '.module-callout',
				'option_js' => true
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-callout',
			),
			// Background repeat
			array(
				'id'		=> 'background_repeat',
				'label'		=> __( 'Background Repeat', 'themify' ),
				'type'		=> 'select',
				'default'	=> '',
				'meta'		=> Themify_Builder_Model::get_background_options(),
				'prop' => 'background-repeat',
				'selector' => '.module-callout',
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
				'selector' => array( '.module-callout', '.module-callout .callout-button' ),
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-callout', '.module-callout h1', '.module-callout h2', '.module-callout h3', '.module-callout h4', '.module-callout h5', '.module-callout h6', '.module-callout .callout-button' ),
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
						'selector' => '.module-callout',
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
						'selector' => '.module-callout',
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
				'selector' => '.module-callout',
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-callout'
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
						'selector' => '.module-callout'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-callout'
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
				'meta' => array( 'html' => '<h4>' . __( 'Link', 'themify' ) . '</h4>'),
			),
			array(
				'id' => 'link_color',
				'type' => 'color',
				'label' => __( 'Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-callout a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-callout a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-callout a'
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_padding',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Padding', 'themify' ) . '</h4>')
			),
			Themify_Builder_Model::get_field_group( 'padding', '.module-callout', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-callout', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-callout', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-callout', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-callout', 'all' ),
			// Margin
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_margin',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Margin', 'themify' ) . '</h4>' ),
			),
			Themify_Builder_Model::get_field_group( 'margin', '.module-callout', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-callout', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-callout', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-callout', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-callout', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-callout', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-callout', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-callout', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-callout', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-callout', 'all' )
		);

		$callout_button = array(
			// Background
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Background', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'background_color_button',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-callout .callout-button a'
			),
			array(
				'id' => 'background_color_button_hover',
				'type' => 'color',
				'label' => __( 'Background Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module-callout .callout-button a:hover'
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
				'id' => 'font_family_button',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-callout .callout-button a'
			),
			array(
				'id' => 'font_color_button',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-callout .callout-button a'
			),
			array(
				'id' => 'font_color_button_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-callout .callout-button a:hover'
			),
			array(
				'id' => 'multi_font_size_button',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_button',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-callout .callout-button a'
					),
					array(
						'id' => 'font_size_button_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_button',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_button',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-callout .callout-button a'
					),
					array(
						'id' => 'line_height_button_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			)
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
					'button' => array(
						'label' => __( 'Callout Button', 'themify' ),
						'fields' => $callout_button
					)
				)
			),
		);

	}

	protected function _visual_template() {
		$module_args = $this->get_module_args(); ?>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?> ui {{ data.layout_callout }} {{ data.color_callout }} {{ data.css_callout }} {{ data.background_repeat }} <# ! _.isUndefined( data.appearance_callout ) ? print( data.appearance_callout.split('|').join(' ') ) : ''; #>">
			<# if ( data.mod_title_callout ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_callout }}}<?php echo $module_args['after_title']; ?>
			<# } #>
			
			<div class="callout-inner">
				<div class="callout-content">
					<h3 class="callout-heading">{{{ data.heading_callout }}}</h3>
					{{{ data.text_callout }}}
				</div>
				
				<# if ( data.action_btn_text_callout ) { #>
					<div class="callout-button">
						<a href="{{ data.action_btn_link_callout }}" class="ui builder_button {{ data.action_btn_color_callout }} <# ! _.isUndefined( data.action_btn_appearance_callout ) ? print( data.action_btn_appearance_callout.split('|').join(' ') ) : ''; #>">
							{{{ data.action_btn_text_callout }}}
						</a>
					</div>
				<# } #>
			</div>			
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Callout_Module' );