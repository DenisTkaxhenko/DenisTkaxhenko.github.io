<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Button
 * Description: Display Button content
 */

class TB_Buttons_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct( array(
			'name' => __( 'Button', 'themify' ),
			'slug' => 'buttons'
		));
	}

	public function get_title( $module ) {
		$text = isset( $module['mod_settings']['mod_title_button'] ) ? $module['mod_settings']['mod_title_button'] : '';
		$return = wp_trim_words( $text, 100 );
		return $return;
	}

	public function get_options() {
		return  array(
			array(
				'id' => 'buttons_size',
				'type' => 'radio',
				'label' => __( 'Size', 'themify' ),
				'options' => array(
					'normal' => __( 'Normal', 'themify' ),
					'small' => __( 'Small', 'themify' ),
					'large' => __( 'Large', 'themify' ),
					'xlarge' => __( 'xLarge', 'themify' )
				),
				'default' => 'normal',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id'=>'buttons_style',
				'type' => 'radio',
				'label' => __( 'Button Style', 'themify' ),
				'options' => array(
					'circle' => __( 'Circle', 'themify' ),
					'rounded' => __( 'Rounded', 'themify' ),
					'squared' => __( 'Squared', 'themify' ),
					'outline' => __( 'Outlined', 'themify' )
				),
				'default' => 'rounded',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'content_button',
				'type' => 'builder',
				'new_row_text'=>__( 'Add new button','themify' ),
				'options' => array(
					array(
						'id' => 'label',
						'type' => 'text',
						'label' => __( 'Text', 'themify' ),
						'class' => 'fullwidth',
						'render_callback' => array(
							'repeater' => 'content_button',
							'binding' => 'live'
						)
					),
					array(
						'id' => 'link',
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
						),
						'render_callback' => array(
							'repeater' => 'content_button',
							'binding' => 'live'
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
						'option_js' => true,
						'wrap_with_class' => 'link_options',
						'render_callback' => array(
							'repeater' => 'content_button',
							'binding' => 'live'
						)
					),
					array(
						'id' => 'lightbox_size',
						'type' => 'multi',
						'label' => __( 'Lightbox Dimension', 'themify' ),
						'options' => array(
							array(
								'id' => 'lightbox_width',
								'type' => 'text',
								'label' => __( 'Width', 'themify' ),
								'value' => '',
								'render_callback' => array(
									'repeater' => 'content_button',
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
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							),
							array(
								'id' => 'lightbox_height',
								'type' => 'text',
								'label' => __( 'Height', 'themify' ),
								'value' => '',
								'render_callback' => array(
									'repeater' => 'content_button',
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
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							)
						),
						'wrap_with_class' => 'tf-group-element tf-group-element-lightbox lightbox_size'
					),
					array(
						'id' => 'button_container',
						'type' => 'multi',
						'label' => __( 'Color', 'themify' ),
						'wrap_with_class' => 'fullwidth',
						'options' => array(
							array(
								'id' => 'button_color_bg',
								'type' => 'layout',
								'label' =>'',
								'options' => Themify_Builder_Model::get_colors(),
								'bottom' => false,
								'wrap_with_class' => 'fullwidth',
								'render_callback' => array(
									'repeater' => 'content_button',
									'binding' => 'live'
								)
							)
						)
					),
					array(
						'id'=>'button_single_style',
						'type' => 'radio',
						'label' => __( 'Button Style', 'themify' ),
						'options' => array(
							'default'=> __( 'Default', 'themify' ),
							'circle'=> __( 'Circle', 'themify' ),
							'rounded'=>__( 'Rounded', 'themify' ),
							'squared'=> __( 'Squared', 'themify' ),
							'outline'=> __( 'Outlined', 'themify' )
						),
						'default' => 'default',
						'render_callback' => array(
							'repeater' => 'content_button',
							'binding' => 'live'
						)
					),
					array(
						'id' => 'icon_container',
						'type' => 'multi',
						'label' => __( 'Icon', 'themify' ),
						'wrap_with_class' => 'fullwidth',
						'options' => array(
							array(
								'id' => 'icon',
								'type' => 'text',
								'iconpicker' => true,
								'label' => '',
								'class' => 'fullwidth themify_field_icon',
								'wrap_with_class' => 'fullwidth',
								'render_callback' => array(
									'repeater' => 'content_button',
									'binding' => 'live',
									'control_type' => 'textonchange'
								)
							)
						)
					)
				),
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
				'id' => 'css_button',
				'type' => 'text',
				'label' => __( 'Additional CSS Class', 'themify' ),
				'class' => 'large exclude-from-reset-field',
				'help' => sprintf( '<br/><small>%s</small>', __( 'Add additional CSS class(es) for custom styling', 'themify' ) ),
				'render_callback' => array(
					'binding' => 'live'
				)
			)
		);
	}

	public function get_default_settings() {
		$settings = array(
			'content_button' => array(
				array( 
					'label' => esc_html__( 'Button Text', 'themify' ), 
					'link' => 'https://themify.me/',
					'link_options' => 'regular'
				)
			)
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
				'selector' => '.module.module-buttons',
				'option_js' => true
			),
			array(
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.module.module-buttons',
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />')
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
				'selector' => ' div.module-buttons'
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( 'div.module-buttons')
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
						'selector' => array(' div.module-buttons i',' div.module-buttons a',' div.module-buttons span'),
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
						'selector' => array(' div.module-buttons i',' div.module-buttons a',' div.module-buttons span'),
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
				'selector' => ' div.module-buttons'
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => 'div.module-buttons'
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
						'selector' => 'div.module-button'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => 'div.module-button'
					),
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
			Themify_Builder_Model::get_field_group( 'padding', '.module.module-buttons', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module.module-buttons', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module.module-buttons', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module.module-buttons', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module.module-buttons', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module.module-buttons', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module.module-buttons', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module.module-buttons', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module.module-buttons', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module.module-buttons', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module.module-buttons', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module.module-buttons', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module.module-buttons', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module.module-buttons', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module.module-buttons', 'all' )
		);

		$button_link = array(
			// Background
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Background', 'themify' ) . '</h4>'),
			),
			array(
				'id' => 'button_background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => ' .module-buttons .module-buttons-item a'
			),
			array(
				'id' => 'button_hover_background_color',
				'type' => 'color',
				'label' => __( 'Background Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => ' .module-buttons .module-buttons-item a:hover'
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
				'selector' => ' .module-buttons .module-buttons-item a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => ' .module-buttons .module-buttons-item a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => array(' .module-buttons .module-buttons-item a span',' .module-buttons .module-buttons-item a i')
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
			Themify_Builder_Model::get_field_group( 'padding', ' .module-buttons .module-buttons-item a', 'top', 'link' ),
			Themify_Builder_Model::get_field_group( 'padding', ' .module-buttons .module-buttons-item a', 'right', 'link' ),
			Themify_Builder_Model::get_field_group( 'padding', ' .module-buttons .module-buttons-item a', 'bottom', 'link' ),
			Themify_Builder_Model::get_field_group( 'padding', ' .module-buttons .module-buttons-item a', 'left', 'link' ),
			Themify_Builder_Model::get_field_group( 'padding', ' .module-buttons .module-buttons-item a', 'all', 'link' ),
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
			Themify_Builder_Model::get_field_group( 'margin', ' .module-buttons .module-buttons-item a', 'top', 'link' ),
			Themify_Builder_Model::get_field_group( 'margin', ' .module-buttons .module-buttons-item a', 'right', 'link' ),
			Themify_Builder_Model::get_field_group( 'margin', ' .module-buttons .module-buttons-item a', 'bottom', 'link' ),
			Themify_Builder_Model::get_field_group( 'margin', ' .module-buttons .module-buttons-item a', 'left', 'link' ),
			Themify_Builder_Model::get_field_group( 'margin', ' .module-buttons .module-buttons-item a', 'all', 'link' ),
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
			Themify_Builder_Model::get_field_group( 'border', ' .module-buttons .module-buttons-item a', 'top', 'link' ),
			Themify_Builder_Model::get_field_group( 'border', ' .module-buttons .module-buttons-item a', 'right', 'link' ),
			Themify_Builder_Model::get_field_group( 'border', ' .module-buttons .module-buttons-item a', 'bottom', 'link' ),
			Themify_Builder_Model::get_field_group( 'border', ' .module-buttons .module-buttons-item a', 'left', 'link' ),
			Themify_Builder_Model::get_field_group( 'border', ' .module-buttons .module-buttons-item a', 'all', 'link' )
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
					'button_link' => array(
						'label' => __( 'Button Link', 'themify' ),
						'fields' => $button_link
					)
				)
			),
		);

	}

	protected function _visual_template() { ?>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ data.css_button }}">
			<# if ( data.content_button ) { #>
				<div class="module-<?php echo esc_attr( $this->slug ); ?> {{ data.buttons_size }}">
					<# _.each( data.content_button, function( item ) { #>
						<# var buttonStyle = item.button_single_style && item.button_single_style !== 'default'
							? item.button_single_style : data.buttons_style; #>
						
						<div class="module-buttons-item {{ buttonStyle }}">
							<# if ( item.link ) { #>
							<a class="ui builder_button {{ item.button_color_bg }}" href="{{ item.link }}">
							<# } #>
							
							<# if ( item.icon ) { #>
							<i class="fa {{ item.icon }}"></i>
							<# } #>

							<span>{{ item.label }}</span>

							<# if ( item.link ) { #>
							</a>
							<# } #>
						</div>

					<# } ); #>
				</div>
			<# } #>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Buttons_Module' );