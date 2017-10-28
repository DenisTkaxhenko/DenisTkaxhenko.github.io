<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Tab
 * Description: Display Tab content
 */
class TB_Tab_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Tab', 'themify' ),
			'slug' => 'tab'
		));
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_tab',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large',
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'tab_content_tab',
				'type' => 'builder',
				'options' => array(
					array(
						'id' => 'tab_title_multi',
						'type' => 'multi',
						'label' => '',
						'options' => array(
							array(
								'id' => 'title_tab',
								'type' => 'text',
								'label' => __( 'Tab Title', 'themify' ),
								'class' => 'fullwidth',
								'render_callback' => array(
									'repeater' => 'tab_content_tab',
									'binding' => 'live'
								)
							),
							array(
								'id' => 'icon_tab',
								'type' => 'text',
								'label' => __( 'Icon', 'themify' ),
								'iconpicker' => true,
								'class' => 'large',
								'render_callback' => array(
									'repeater' => 'tab_content_tab',
									'binding' => 'live',
									'control_type' => 'textonchange'
								)
							),
						)
					),
					array(
						'id' => 'text_tab',
						'type' => 'wp_editor',
						'label' => false,
						'class' => 'fullwidth',
						'rows' => 6,
						'render_callback' => array(
							'repeater' => 'tab_content_tab',
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
				'id' => 'layout_tab',
				'type' => 'layout',
				'label' => __( 'Tab Layout', 'themify' ),
				'options' => array(
					array( 'img' => 'tab-frame.png', 'value' => 'tab-frame', 'label' => __( 'Tab Frame', 'themify' ) ),
					array( 'img' => 'tab-window.png', 'value' => 'panel', 'label' => __( 'Tab Window', 'themify' ) ),
					array( 'img' => 'tab-vertical.png', 'value' => 'vertical', 'label' => __( 'Tab Vertical', 'themify' ) ),
					array( 'img' => 'tab-top.png', 'value' => 'minimal', 'label' => __( 'Tab Top', 'themify' ) )
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'style_tab',
				'type' => 'select',
				'label' => __( 'Tab Icon', 'themify' ),
				'options' => array(
					'default' => __( 'Icon beside the title', 'themify' ),
					'icon-top' => __( 'Icon above the title', 'themify' ),
					'icon-only' => __( 'Just icons', 'themify' ),
				),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'color_tab',
				'type' => 'layout',
				'label' => __( 'Tab Color', 'themify' ),
				'options' => Themify_Builder_Model::get_colors(),
				'render_callback' => array(
					'binding' => 'live'
				)
			),
			array(
				'id' => 'tab_appearance_tab',
				'type' => 'checkbox',
				'label' => __( 'Tab Appearance', 'themify' ),
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
				'id' => 'css_tab',
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
			'tab_content_tab' => array(
				array( 'title_tab' => esc_html__( 'Tab Title', 'themify' ), 'text_tab' => esc_html__( 'Tab content', 'themify' ) )
			),
			'layout_tab' => 'minimal'
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
						'label' => __( 'Delays', 'themify' ),
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
				'selector' => '.ui.module-tab'
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
				'selector' => '.ui.module-tab'
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.ui.module-tab', '.ui.module-tab .tab-content', '.ui.module-tab h1', '.ui.module-tab h2', '.ui.module-tab h3:not(.module-title)', '.ui.module-tab h4', '.ui.module-tab h5', '.ui.module-tab h6' )
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
						'selector' => '.ui.module-tab'
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
						'selector' => '.ui.module-tab'
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
				'selector' => '.ui.module-tab'
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.ui.module-tab'
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
						'selector' => '.ui.module-tab'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.ui.module-tab'
					),
				)
			),
			// Link
			array(
				'type' => 'separator',
				'meta' => array('html'=>'<hr />')
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
				'selector' => '.ui.module-tab a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.ui.module-tab a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.ui.module-tab a'
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
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.ui.module-tab', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.ui.module-tab', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.ui.module-tab', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.ui.module-tab', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.ui.module-tab', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab', 'all' )
		);

		$title = array(
			// Background
			array(
				'id' => 'separator_image_background',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__( 'Background', 'themify' ).'</h4>'),
			),
			array(
				'id' => 'background_color_title',
				'type' => 'color',
				'label' => __( 'Background Color', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.ui.module-tab ul.tab-nav li'
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
				'id' => 'font_family_title',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.ui.module-tab ul.tab-nav li a'
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.ui.module-tab ul.tab-nav li a'
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
						'selector' => '.ui.module-tab ul.tab-nav li a'
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
						'selector' => '.ui.module-tab ul.tab-nav li a'
					),
					array(
						'id' => 'line_height_title_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				),
			),
			array(
				'id' => 'title_text_align',
				'label' => __( 'Text Align', 'themify' ),
				'type' => 'radio',
				'meta' => Themify_Builder_Model::get_text_align(),
				'prop' => 'text-align',
				'selector' => array ( '.ui.module-tab ul.tab-nav', '.ui.module-tab ul.tab-nav li' )
			),
			// Active Tab
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_active_tab',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Active Tab', 'themify' ) . '</h4>' ),
			),
			array(
				'id' => 'active_font_color_title',
				'type' => 'color',
				'label' => __( 'Color Active', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.ui.module-tab ul.tab-nav li.current a'
			),
			array(
				'id' => 'active_background_color_title',
				'type' => 'color',
				'label' => __( 'Background Active', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.ui.module-tab ul.tab-nav li.current'
			),
			array(
				'id' => 'active_hover_font_color_title',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.ui.module-tab ul.tab-nav li.current a:hover'
			),
			array(
				'id' => 'active_hover_background_color_title',
				'type' => 'color',
				'label' => __( 'Background Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'background-color',
				'selector' => '.ui.module-tab ul.tab-nav li.current:hover'
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
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab ul.tab-nav li', 'top', 'title' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab ul.tab-nav li', 'right', 'title' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab ul.tab-nav li', 'bottom', 'title' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab ul.tab-nav li', 'left', 'title' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab ul.tab-nav li', 'all', 'title' )
		);

		$icon = array(
			array(
				'id' => 'icon_color',
				'type' => 'color',
				'label' => __( 'Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.ui.module-tab ul.tab-nav li i' )
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
						'selector' => array( '.ui.module-tab ul.tab-nav li i' ),
					),
					array(
						'id' => 'icon_size_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			// Active Tab
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_active_tab_icon',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Active Tab', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'active_tab_icon_color',
				'type' => 'color',
				'label' => __( 'Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.ui.module-tab ul.tab-nav li.current i'
			),
		);

		$content = array(
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
				'selector' => '.ui.module-tab .tab-content'
			),
			// Font
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array('html'=>'<h4>'.__( 'Font', 'themify' ).'</h4>'),
			),
			array(
				'id' => 'font_family_content',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.ui.module-tab .tab-content'
			),
			array(
				'id' => 'font_color_content',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.ui.module-tab .tab-content'
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
						'selector' => '.ui.module-tab .tab-content'
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
						'selector' => '.ui.module-tab .tab-content'
					),
					array(
						'id' => 'line_height_content_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			// Padding
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr />' )
			),
			array(
				'id' => 'separator_padding_content',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Padding', 'themify' ) . '</h4>' )
			),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab .tab-content', 'top', 'content' ),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab .tab-content', 'right', 'content' ),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab .tab-content', 'bottom', 'content' ),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab .tab-content', 'left', 'content' ),
			Themify_Builder_Model::get_field_group( 'padding', '.ui.module-tab .tab-content', 'all', 'content' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab .tab-content', 'top', 'content' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab .tab-content', 'right', 'content' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab .tab-content', 'bottom', 'content' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab .tab-content', 'left', 'content' ),
			Themify_Builder_Model::get_field_group( 'border', '.ui.module-tab .tab-content', 'all', 'content' )
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
						'label' => __( 'Tab Title', 'themify' ),
						'fields' => $title
					),
					'icon' => array(
						'label' => __( 'Tab Icon', 'themify' ),
						'fields' => $icon
					),
					'content' => array(
						'label' => __( 'Tab Content', 'themify' ),
						'fields' => $content
					)
				)
			),
		);

	}

	protected function _visual_template() {
		$module_args = $this->get_module_args(); ?>
		<div class="module module-<?php echo esc_attr( $this->slug ); ?> ui tab-style-{{ data.style_tab }} {{ data.layout_tab }} {{ data.color_tab }} {{ data.css_tab }} <# ! _.isUndefined( data.tab_appearance_tab ) ? print( data.tab_appearance_tab.split('|').join(' ') ) : ''; #>">
			<# if ( data.mod_title_tab ) { #>
			<?php echo $module_args['before_title']; ?>{{{ data.mod_title_tab }}}<?php echo $module_args['after_title']; ?>
			<# }

			if ( data.tab_content_tab ) {
				var counter = 0; #>
				<div class="builder-tabs-wrap">
				<ul class="tab-nav">
					<#
					_.each( data.tab_content_tab, function( item ) { #>
						<li class="<# counter === 0 ? print('current') : ''; #>" <# counter === 0 ? print('aria-expanded="true"') : print('aria-expanded="false"'); #>>
							<a href="#tab-{{ data.cid }}-{{ counter }}">
								<# if ( item.icon_tab ) { #>
								<i class="fa {{ item.icon_tab }}"></i>
								<# } #>
								<span>{{ item.title_tab }}</span>
							</a>
						</li>
					<#
						counter++;
					} ); #>
				</ul>

				<# counter = 0; #>
					<#
					_.each( data.tab_content_tab, function( item ) { #>
						<div id="#tab-{{ data.cid }}-{{ counter }}" class="tab-content" <# counter === 0 ? print('aria-hidden="false"') : print('aria-hidden="true"'); #>>
							{{{ item.text_tab }}}
						</div>
					<#
					counter++;
					} ); #>
				</div>
			<# } #>
		</div>
	<?php
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Tab_Module' );