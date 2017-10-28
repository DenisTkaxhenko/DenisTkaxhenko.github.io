<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Accordion
 * Description: Display Accordion content
 */

class TB_Accordion_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct( array(
			'name' => __( 'Accordion', 'themify' ),
			'slug' => 'accordion'
		));
	}

	public function get_title( $module ) {
		$text = isset( $module['mod_settings']['mod_title_accordion'] ) ? $module['mod_settings']['mod_title_accordion'] : '';
		$return = wp_trim_words( $text, 100 );
		return $return;
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_accordion',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'content_accordion',
				'type' => 'builder',
				'options' => array(
					array(
						'id' => 'title_accordion',
						'type' => 'text',
						'label' => __( 'Accordion Title', 'themify' ),
						'class' => 'large',
						'render_callback' => array(
							'repeater' => 'content_accordion',
							'binding' => 'live'
						)
					),
					array(
						'id' => 'text_accordion',
						'type' => 'wp_editor',
						'label' => false,
						'class' => 'fullwidth',
						'rows' => 6,
						'render_callback' => array(
							'repeater' => 'content_accordion',
							'binding' => 'live'
						)
					),
					array(
						'id' => 'default_accordion',
						'type' => 'radio',
						'label' => __( 'Default', 'themify' ),
						'default' => 'toggle',
						'options' => array(
							'closed' => __( 'closed', 'themify' ),
							'open' => __( 'open', 'themify' )
						),
						'render_callback' => array(
							'repeater' => 'content_accordion',
							'binding' => 'live'
						)
					)
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'layout_accordion',
				'type' => 'layout',
				'label' => __( 'Accordion layout', 'themify' ),
				'options' => array(
					array('img' => 'accordion-default.png', 'value' => 'default', 'label' => __( 'Contiguous Panels', 'themify' )),
					array('img' => 'accordion-separate.png', 'value' => 'separate', 'label' => __( 'Separated Panels', 'themify' ))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'expand_collapse_accordion',
				'type' => 'radio',
				'label' => __( 'Behavior', 'themify' ),
				'default' => 'toggle',
				'options' => array(
					'toggle' => __( 'Toggle <small>(only clicked item is toggled)</small>', 'themify' ),
					'accordion' => __( 'Accordion <small>(collapse all, but keep clicked item expanded)</small>', 'themify' )
				),
				'break' => true,
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'color_accordion',
				'type' => 'layout',
				'label' => __('Accordion Color', 'themify'),
				'options' => array(
					array('img' => 'color-default.png', 'value' => 'default', 'label' => __('default', 'themify')),
					array('img' => 'color-black.png', 'value' => 'black', 'label' => __('black', 'themify')),
					array('img' => 'color-grey.png', 'value' => 'gray', 'label' => __('gray', 'themify')),
					array('img' => 'color-blue.png', 'value' => 'blue', 'label' => __('blue', 'themify')),
					array('img' => 'color-light-blue.png', 'value' => 'light-blue', 'label' => __('light-blue', 'themify')),
					array('img' => 'color-green.png', 'value' => 'green', 'label' => __('green', 'themify')),
					array('img' => 'color-light-green.png', 'value' => 'light-green', 'label' => __('light-green', 'themify')),
					array('img' => 'color-purple.png', 'value' => 'purple', 'label' => __('purple', 'themify')),
					array('img' => 'color-light-purple.png', 'value' => 'light-purple', 'label' => __('light-purple', 'themify')),
					array('img' => 'color-brown.png', 'value' => 'brown', 'label' => __('brown', 'themify')),
					array('img' => 'color-orange.png', 'value' => 'orange', 'label' => __('orange', 'themify')),
					array('img' => 'color-yellow.png', 'value' => 'yellow', 'label' => __('yellow', 'themify')),
					array('img' => 'color-red.png', 'value' => 'red', 'label' => __('red', 'themify')),
					array('img' => 'color-pink.png', 'value' => 'pink', 'label' => __('pink', 'themify')),
					array('img' => 'color-transparent.png', 'value' => 'transparent', 'label' => __('Transparent', 'themify'))
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'accordion_appearance_accordion',
				'type' => 'checkbox',
				'label' => __( 'Accordion Appearance', 'themify' ),
				'options' => Themify_Builder_Model::get_appearance(),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'multi_accordion_icon',
				'type' => 'multi',
				'label' => __( 'Icon', 'themify' ),
				'fields' => array(
					array(
						'id' => 'icon_accordion',
						'type' => 'icon',
						'label' => __( 'Closed Accordion Icon', 'themify' ),
						'class' => 'large',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
					array(
						'id' => 'icon_active_accordion',
						'type' => 'icon',
						'label' => __( 'Opened Accordion Icon', 'themify' ),
						'class' => 'large',
						'render_callback' => array(
							'binding' => 'live'
						)
					),
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_accordion',
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
			'content_accordion' => array(
				array( 'title_accordion' => esc_html__( 'Accordion Title', 'themify' ), 'text_accordion' => esc_html__( 'Accordion content', 'themify' ) )
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
				'id' => 'background_color',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => array( ' .ui.module-accordion .accordion-content', ' .ui.module-accordion .accordion-title a' )
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
				'selector' => ' .ui.module-accordion',
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( ' .ui.module-accordion', ' .ui.module-accordion h1', ' .ui.module-accordion h2', ' .ui.module-accordion h3', ' .ui.module-accordion h4', ' .ui.module-accordion h5', ' .ui.module-accordion h6' ),
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
						'selector' => ' .ui.module-accordion',
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
						'selector' => ' .ui.module-accordion',
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
				'selector' => ' .ui.module-accordion',
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.ui.module-accordion'
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
						'selector' => '.ui.module-accordion'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.ui.module-accordion'
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
				'selector' => ' .ui.module-accordion a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => ' .ui.module-accordion a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => ' .ui.module-accordion a'
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
			Themify_Builder_Model::get_field_group( 'padding', array( ' .ui.module-accordion .accordion-content', ' .ui.module-accordion .accordion-title a' ), 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', array( ' .ui.module-accordion .accordion-content', ' .ui.module-accordion .accordion-title a' ), 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', array( ' .ui.module-accordion .accordion-content', ' .ui.module-accordion .accordion-title a' ), 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', array( ' .ui.module-accordion .accordion-content', ' .ui.module-accordion .accordion-title a' ), 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', array( ' .ui.module-accordion .accordion-content', ' .ui.module-accordion .accordion-title a' ), 'all' ),
			// Margin
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
			),
			array(
				'id' => 'separator_margin',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__( 'Margin', 'themify' ).'</h4>'),
			),
			Themify_Builder_Model::get_field_group( 'margin', ' .ui.module-accordion', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', ' .ui.module-accordion', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', ' .ui.module-accordion', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', ' .ui.module-accordion', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', ' .ui.module-accordion', 'all' ),
		);

		$accordion_title = array(
			// Background
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Background', 'themify' ) . '</h4>' ),
			),
			array(
				'id' => 'background_color_title',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => ' .ui.module-accordion .accordion-title a'
			),
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
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
				'selector' => ' .ui.module-accordion .accordion-title'
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( ' .ui.module-accordion .accordion-title', ' .ui.module-accordion .accordion-title a')
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
						'selector' => ' .ui.module-accordion .accordion-title',
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
						'selector' => ' .ui.module-accordion .accordion-title',
					),
					array(
						'id' => 'line_height_title_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$accordion_icon = array(
			array(
				'id' => 'icon_color',
				'type' => 'color',
				'label' => __( 'Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( ' .ui.module-accordion .accordion-title .accordion-active-icon' )
			),
			array(
				'id' => 'icon_active_color',
				'type' => 'color',
				'label' => __( 'Closed Icon Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( ' .ui.module-accordion .accordion-title .accordion-icon' )
			),
			array(
				'id' => 'multi_icon_size',
				'type' => 'multi',
				'label' => __( 'Icon Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'icon_size',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => array( ' .ui.module-accordion .accordion-title i' ),
					),
					array(
						'id' => 'icon_size_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$accordion_content = array(
			// Background
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Background', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'background_color_content',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => ' .ui.module-accordion .accordion-content'
			),
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_content',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => ' .ui.module-accordion .accordion-content, .ui.module-accordion .accordion-content *',
			),
			array(
				'id' => 'font_color_content',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( ' .ui.module-accordion .accordion-content', ' .ui.module-accordion .accordion-content h1', ' .ui.module-accordion .accordion-content h2', ' .ui.module-accordion .accordion-content h3', ' .ui.module-accordion .accordion-content h4', ' .ui.module-accordion .accordion-content h5', ' .ui.module-accordion .accordion-content h6' ),
			),
			array(
				'id' => 'multi_font_size_content',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_content',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => ' .ui.module-accordion .accordion-content',
					),
					array(
						'id' => 'font_size_content_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_content',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_content',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => ' .ui.module-accordion .accordion-content',
					),
					array(
						'id' => 'line_height_content_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			// Multi columns
			array(
				'id' => 'separator_multi_columns',
				'type' => 'separator',
				'meta' => array( 'html' => '<hr><h4>' . __( 'Multi-columns', 'themify' ) . '</h4>' )
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
				'selector' => ' .accordion-content'
			),
			array(
				'id' => 'column_gap',
				'type' => 'text',
				'label' => __( 'Column Gap', 'themify' ),
				'class' => 'style_field_px xsmall',
				'prop' => 'column-gap',
				'selector' => ' .accordion-content'
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
						'selector' => ' .accordion-content',
					),
					array(
						'id' => 'column_divider_width',
						'type' => 'text',
						'description' => 'px',
						'class' => 'style_field_px xsmall',
						'prop' => 'column-rule-width',
						'selector' => ' .accordion-content',
					),
					array(
						'id' => 'column_divider_style',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_border_styles(),
						'class' => 'style_field_select',
						'prop' => 'column-rule-style',
						'selector' => ' .accordion-content',
					)
				)
			),
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
			Themify_Builder_Model::get_field_group( 'border', ' .ui.module-accordion .accordion-content', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', ' .ui.module-accordion .accordion-content', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', ' .ui.module-accordion .accordion-content', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', ' .ui.module-accordion .accordion-content', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', ' .ui.module-accordion .accordion-content', 'all' )
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
						'label' => __( 'Accordion Title', 'themify' ),
						'fields' => $accordion_title
					),
					'icon' => array(
						'label' => __( 'Accordion Icon', 'themify' ),
						'fields' => $accordion_icon
					),
					'content' => array(
						'label' => __( 'Accordion Content', 'themify' ),
						'fields' => $accordion_content
					),
				)
			),
		);

	}

	protected function _visual_template() { 
		$module_args = $this->get_module_args(); ?>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?> {{ data.css_accordion }}" data-behavior="{{ data.expand_collapse_accordion }}">
			<# if ( data.mod_title_accordion ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_accordion }}}<?php echo $module_args['after_title']; ?>
			<# }

			if ( data.content_accordion ) { #>
				<ul class="module-<?php echo esc_attr( $this->slug ); ?> ui {{ data.layout_accordion }} {{ data.color_accordion }} <# ! _.isUndefined( data.accordion_appearance_accordion ) ? print( data.accordion_appearance_accordion.split('|').join(' ') ) : ''; #>">
					<#
					_.each( data.content_accordion, function( item ) { #>
						<li class="<# 'open' === item.default_accordion ? print('builder-accordion-active') : ''; #>">
							
							<div class="accordion-title">
								<a href="#">
									<# if ( data.icon_accordion ) { #>
										<i class="accordion-icon fa {{ data.icon_accordion }}"></i>
									<# } #>
									
									<# if ( data.icon_active_accordion ) { #>
										<i class="accordion-active-icon fa {{ data.icon_active_accordion }}"></i>
									<# } #>

									{{{ item.title_accordion }}}
								</a>
							</div>

							<div class="accordion-content <# 'open' !== item.default_accordion ? print('default-closed') : ''; #> clearfix">
								{{{ item.text_accordion }}}
							</div>
						</li>
					<# } ); #>
				</ul>
			<# } #>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Accordion_Module' );
