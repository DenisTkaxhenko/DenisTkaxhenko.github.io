<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Testimonial Slider
 * Description: Display testimonial custom post type
 */
class TB_Testimonial_Slider_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Testimonial Slider', 'themify' ),
			'slug' => 'testimonial-slider'
		));
		
	}

	public function get_title( $module ) {
		$type = isset( $module['mod_settings']['type_query_testimonial'] ) ? $module['mod_settings']['type_query_testimonial'] : 'category';
		$category = isset( $module['mod_settings']['category_testimonial'] ) ? $module['mod_settings']['category_testimonial'] : '';
		$slug_query = isset( $module['mod_settings']['query_slug_testimonial'] ) ? $module['mod_settings']['query_slug_testimonial'] : '';

		if ( 'category' == $type ) {
			return sprintf( '%s : %s', __( 'Category', 'themify' ), $category );
		} else {
			return sprintf( '%s : %s', __( 'Slugs', 'themify' ), $slug_query );
		}
	}

	public function get_options() {
		$visible_opt = array(1 => 1, 2, 3, 4, 5, 6, 7);
		$auto_scroll_opt = array(
			'off' => __( 'Off', 'themify' ),
			1 => __( '1 sec', 'themify' ),
			2 => __( '2 sec', 'themify' ),
			3 => __( '3 sec', 'themify' ),
			4 => __( '4 sec', 'themify' ),
			5 => __( '5 sec', 'themify' ),
			6 => __( '6 sec', 'themify' ),
			7 => __( '7 sec', 'themify' ),
			8 => __( '8 sec', 'themify' ),
			9 => __( '9 sec', 'themify' ),
			10 => __( '10 sec', 'themify' ),
			15 => __( '15 sec', 'themify' ),
			20 => __( '20 sec', 'themify' ),
		);
		$options = array(
			array(
				'id' => 'mod_title_testimonial',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large'
			),
			array(
				'id' => 'layout_testimonial',
				'type' => 'layout',
				'label' => __( 'Testimonial Slider Layout', 'themify' ),
				'options' => array(
					array( 'img' => 'testimonials-image-top.png', 'value' => 'image-top', 'label' => __( 'Image Top', 'themify' ) ),
					array( 'img' => 'testimonials-image-bottom.png', 'value' => 'image-bottom', 'label' => __( 'Image Bottom', 'themify' ) ),
				)
			),
			array(
				'id' => 'tab_content_testimonial',
				'type' => 'builder',
				'options' => array(
					array(
						'id' => 'title_testimonial',
						'type' => 'text',
						'label' => __( 'Testimonial Title', 'themify' ),
						'class' => 'fullwidth',
						'render_callback' => array(
							'repeater' => 'tab_content_testimonial'
						)
					),
					array(
						'id' => 'content_testimonial',
						'type' => 'wp_editor',
						'label' => false,
						'class' => 'fullwidth',
						'rows' => 6,
						'render_callback' => array(
							'repeater' => 'tab_content_testimonial'
						)
					),
					array(
						'id' => 'person_picture_testimonial',
						'type' => 'image',
						'label' => __( 'Person Picture', 'themify' ),
						'class' => 'xlarge',
						'render_callback' => array(
							'repeater' => 'tab_content_testimonial'
						)
					),
					array(
						'id' => 'person_name_testimonial',
						'type' => 'text',
						'label' => __( 'Person Name', 'themify' ),
						'class' => 'fullwidth',
						'render_callback' => array(
							'repeater' => 'tab_content_testimonial'
						)
					),
					array(
						'id' => 'person_position_testimonial',
						'type' => 'text',
						'label' => __( 'Person Position', 'themify' ),
						'class' => 'fullwidth',
						'render_callback' => array(
							'repeater' => 'tab_content_testimonial'
						)
					),
					array(
						'id' => 'company_testimonial',
						'type' => 'text',
						'label' => __( 'Company', 'themify' ),
						'class' => 'fullwidth',
						'render_callback' => array(
							'repeater' => 'tab_content_testimonial'
						)
					),
					array(
						'id' => 'company_website_testimonial',
						'type' => 'text',
						'label' => __( 'Company Website', 'themify' ),
						'class' => 'fullwidth',
						'render_callback' => array(
							'repeater' => 'tab_content_testimonial'
						)
					)
				)
			),
			array(
				'id' => 'img_w_slider',
				'type' => 'text',
				'label' => __( 'Image Width', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
			),
			array(
				'id' => 'img_h_slider',
				'type' => 'text',
				'label' => __( 'Image Height', 'themify' ),
				'class' => 'xsmall',
				'help' => 'px',
			),
			array(
				'id' => 'slider_option_testimonial',
				'type' => 'slider',
				'label' => __( 'Slider Options', 'themify' ),
				'options' => array(
					array(
						'id' => 'visible_opt_slider',
						'type' => 'select',
						'default' => 1,
						'options' => $visible_opt,
						'help' => __( 'Visible', 'themify' )
					),
					array(
						'id' => 'auto_scroll_opt_slider',
						'type' => 'select',
						'default' => 4,
						'options' => $auto_scroll_opt,
						'help' => __( 'Auto Scroll', 'themify' )
					),
					array(
						'id' => 'scroll_opt_slider',
						'type' => 'select',
						'options' => $visible_opt,
						'help' => __( 'Scroll', 'themify' )
					),
					array(
						'id' => 'speed_opt_slider',
						'type' => 'select',
						'options' => array(
							'normal' => __( 'Normal', 'themify' ),
							'fast' => __( 'Fast', 'themify' ),
							'slow' => __( 'Slow', 'themify' )
						),
						'help' => __( 'Speed', 'themify' )
					),
					array(
						'id' => 'effect_slider',
						'type' => 'select',
						'options' => array(
							'scroll' => __( 'Slide', 'themify' ),
							'fade' => __( 'Fade', 'themify' ),
							'crossfade' => __( 'Cross Fade', 'themify' ),
							'cover' => __( 'Cover', 'themify' ),
							'cover-fade' => __( 'Cover Fade', 'themify' ),
							'uncover' => __( 'Uncover', 'themify' ),
							'uncover-fade' => __( 'Uncover Fade', 'themify' ),
							'continuously' => __( 'Continuously', 'themify' )
						),
						'help' => __( 'Effect', 'themify' )
					),
					array(
						'id' => 'pause_on_hover_slider',
						'type' => 'select',
						'options' => array(
							'resume' => __( 'Yes', 'themify' ),
							'false' => __( 'No', 'themify' )
						),
						'help' => __( 'Pause On Hover', 'themify' )
					),
					array(
						'id' => 'wrap_slider',
						'type' => 'select',
						'help' => __( 'Wrap', 'themify' ),
						'options' => array(
							'yes' => __( 'Yes', 'themify' ),
							'no' => __( 'No', 'themify' )
						)
					),
					array(
						'id' => 'show_nav_slider',
						'type' => 'select',
						'help' => __( 'Show slider pagination', 'themify' ),
						'options' => array(
							'yes' => __( 'Yes', 'themify' ),
							'no' => __( 'No', 'themify' )
						)
					),
					array(
						'id' => 'show_arrow_slider',
						'type' => 'select',
						'help' => __( 'Show slider arrow buttons', 'themify' ),
						'options' => array(
							'yes' => __( 'Yes', 'themify' ),
							'no' => __( 'No', 'themify' )
						)
					),
					array(
						'id' => 'show_arrow_buttons_vertical',
						'type' => 'checkbox',
						'label' => false,
						'help' => false,
						'wrap_with_class' => '',
						'options' => array(
							array( 'name' => 'vertical', 'value' =>__( 'Display arrow buttons vertical middle on the left/right side', 'themify' ) )
						)
					),
					array(
						'id' => 'left_margin_slider',
						'type' => 'text',
						'class' => 'xsmall',
						'unit' => 'px',
						'help' => __( 'Left margin space between slides', 'themify' )
					),
					array(
						'id' => 'right_margin_slider',
						'type' => 'text',
						'class' => 'xsmall',
						'unit' => 'px',
						'help' => __( 'Right margin space between slides', 'themify' )
					),
					array(
						'id' => 'height_slider',
						'type' => 'select',
						'options' => array(
							'variable' => __( 'Variable', 'themify' ),
							'auto' => __( 'Auto', 'themify' )
						),
						'help' => __( 'Height <small class="description">"Auto" measures the highest slide and all other slides will be set to that size. "Variable" makes every slide has it\'s own height.</small>', 'themify' )
					),
				)
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_testimonial',
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
			'layout_testimonial' => 'image-top',
			'tab_content_testimonial' => array(
				array( 
					'title_testimonial' => esc_html__( 'Optional Title', 'themify' ), 
					'content_testimonial' => esc_html__( 'Testimonial content', 'themify' ),
					'person_name_testimonial' => 'John Smith',
					'person_position_testimonial' => 'CEO',
					'company_testimonial' => 'X-corporation'
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
				'selector' => '.module-testimonial-slider'
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
				'selector' => '.module-testimonial-slider .testimonial-content','.module-testimonial-slider .testimonial-content .testimonial-title', '.module-testimonial-slider .testimonial-content .testimonial-title a',
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-testimonial-slider .testimonial-content', '.module-testimonial-slider .testimonial-content h1', '.module-testimonial-slider .testimonial-content h2', '.module-testimonial-slider .testimonial-content h3', '.module-testimonial-slider .testimonial-content h4', '.module-testimonial-slider .testimonial-content h5', '.module-testimonial-slider .testimonial-content h6', '.module-testimonial-slider .testimonial-content .testimonial-title', '.module-testimonial-slider .testimonial-content .testimonial-title a',
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
						'selector' => '.module-testimonial-slider .testimonial-content'
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
						'selector' => '.module-testimonial-slider .testimonial-content'
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
				'selector' => '.module-testimonial-slider .testimonial-content',
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-testimonial-slider .testimonial-content'
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
						'selector' => '.module-testimonial-slider .testimonial-content'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-testimonial-slider .testimonial-content'
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
				'selector' =>'.module-testimonial-slider a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-testimonial-slider a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-testimonial-slider a'
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial-slider', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial-slider', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial-slider', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial-slider', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial-slider', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial-slider', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial-slider', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial-slider', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial-slider', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial-slider', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial-slider', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial-slider', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial-slider', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial-slider', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial-slider', 'all' )
		);

		$testimonial_title = array(
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
				'selector' => '.module-testimonial-slider .testimonial-content .testimonial-title', '.module-testimonial-slider .testimonial-content .testimonial-title a'
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' =>'.module-testimonial-slider .testimonial-content .testimonial-title', '.module-testimonial-slider .testimonial-content .testimonial-title a'
			),
			array(
				'id' => 'font_color_title_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-testimonial-slider .testimonial-content .testimonial-title:hover', '.module-testimonial-slider .testimonial-content .testimonial-title a:hover'
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
						'selector' => '.module-testimonial-slider .testimonial-content .testimonial-title'
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
						'selector' => '.module-testimonial-slider .testimonial-content .testimonial-title'
					),
					array(
						'id' => 'line_height_title_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$testimonial_content = array(
			// Font
			array(
				'id' => 'font_family_content',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' =>'.module-testimonial-slider .testimonial-content'
			),
			array(
				'id' => 'font_color_content',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-testimonial-slider .testimonial-content'
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
						'selector' =>'.module-testimonial-slider .testimonial-content'
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
						'selector' => '.module-testimonial-slider .testimonial-content'
					),
					array(
						'id' => 'line_height_content_unit',
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
						'label' => __( 'Testimonial Title', 'themify' ),
						'fields' => $testimonial_title
					),
					'content' => array(
						'label' => __( 'Testimonial Content', 'themify' ),
						'fields' => $testimonial_content
					)
				)
			)
		);
	}
}

if( ! function_exists( 'themify_builder_testimonial_author_name' ) ) :
	function themify_builder_testimonial_author_name( $post, $show_author ) {
		$out = '';
		if( 'yes' == $show_author){
			if( $author = get_post_meta( $post->ID, '_testimonial_name', true ) )
				$out .= '<span class="dash"></span><cite class="testimonial-name">' . $author . '</cite> <br/>';

			if( $position = get_post_meta( $post->ID, '_testimonial_position', true ) )
				$out .= '<em class="testimonial-title">' . $position;

				if( $link = get_post_meta( $post->ID, '_testimonial_link', true ) ){
					if( $position ){
						$out .= ', ';
					}
					else {
						$out .= '<em class="testimonial-title">';
					}
					$out .= '<a href="'.esc_url($link).'">';
				}

					if( $company = get_post_meta( $post->ID, '_testimonial_company', true ) )
						$out .= $company;
					else
						$out .= $link;

				if( $link ) $out .= '</a>';

			$out .= '</em>';

			return $out;
		}
		return '';
	}
endif;

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Testimonial_Slider_Module' );