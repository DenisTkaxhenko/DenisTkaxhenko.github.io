<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Video
 * Description: Display Video content
 */
class TB_Video_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Video', 'themify' ),
			'slug' => 'video'
		));
	}

	public function get_title( $module ) {
		return isset( $module['mod_settings']['title_video'] ) ? esc_html( $module['mod_settings']['title_video'] ) : '';
	}

	public function get_options() {
		$options = array(
			array(
				'id' => 'mod_title_video',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large'
			),
			array(
				'id' => 'style_video',
				'type' => 'layout',
				'label' => __( 'Video Style', 'themify' ),
				'options' => array(
					array( 'img' => 'video-top.png', 'value' => 'video-top', 'label' => __( 'Video Top', 'themify' ) ),
					array( 'img' => 'video-left.png', 'value' => 'video-left', 'label' => __( 'Video Left', 'themify' ) ),
					array( 'img' => 'video-right.png', 'value' => 'video-right', 'label' => __( 'Video Right', 'themify' ) ),
					array( 'img' => 'video-overlay.png', 'value' => 'video-overlay', 'label' => __( 'Video Overlay', 'themify' ) )
				),
				'render_callback' => array(
					'binding' => 'live',
					'selector' => '' // empty means apply to module container
				)
			),
			array(
				'id' => 'url_video',
				'type' => 'text',
				'label' => __( 'Video URL', 'themify' ),
				'class' => 'fullwidth',
				'help' => __( 'YouTube, Vimeo, etc. video <a href="https://themify.me/docs/video-embeds" target="_blank">embed link</a>', 'themify' )
			),
			array(
				'id' => 'autoplay_video',
				'type' => 'radio',
				'label' => __( 'Autoplay', 'themify' ),
				'options' => array(
					'no'=> __( 'No', 'themify' ),
					'yes'=>__( 'Yes', 'themify' ),
				),
				'default' => 'no',
			),
			array(
				'id' => 'width_video',
				'type' => 'text',
				'label' => __( 'Video Width', 'themify' ),
				'class' => 'xsmall',
				'help' => __( 'Enter fixed witdth (eg. 200px) or relative (eg. 100%). Video height is auto adjusted.', 'themify' ),
				'break' => true,
				'unit' => array(
					'id' => 'unit_video',
					'options' => array(
						array( 'id' => 'pixel_unit', 'value' => 'px' ),
						array( 'id' => 'percent_unit', 'value' => '%' )
					),
					'render_callback' => array(
						'control_type' => 'select'
					)
				)
			),
			array(
				'id' => 'title_video',
				'type' => 'text',
				'label' => __( 'Video Title', 'themify' ),
				'class' => 'xlarge'
			),
			array(
				'id' => 'title_link_video',
				'type' => 'text',
				'label' => __( 'Video Title Link', 'themify' ),
				'class' => 'xlarge'
			),
			array(
				'id' => 'caption_video',
				'type' => 'textarea',
				'label' => __( 'Video Caption', 'themify' ),
				'class' => 'fullwidth'
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>' )
			),
			array(
				'id' => 'css_video',
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
			'url_video' => 'https://www.youtube.com/watch?v=waM20ewLj34'
		);
		return $settings;
	}

	public function get_animation() {
		$animation = array(
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . esc_html__( 'Appearance Animation', 'themify' ) . '</h4>' )
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
				'selector' => '.module-video',
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
				'selector' => array( '.module-video .video-content', '.module-video .video-title', '.module-video .video-title a' )
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'border-left-style',
				'selector' => array( '.module-video .video-content', '.module-video .video-title', '.module-video .video-title a', '.module-video h1', '.module-video h2', '.module-video h3:not(.module-title)', '.module-video h4', '.module-video h5', '.module-video h6' ),
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
						'selector' => '.module-video .video-content'
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
						'selector' => '.module-video .video-content'
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
				'selector' => '.module-video .video-content'
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-video .video-content'
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
						'selector' => '.module-video .video-content'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-video .video-content'
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
				'selector' => '.module-video a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-video a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-video a'
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-video', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-video', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-video', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-video', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-video', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module-video', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-video', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-video', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-video', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-video', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-video', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-video', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-video', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-video', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-video', 'all' )
		);

		$video_title = array(
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
				'selector' => array( '.module-video .video-title', '.module-video .video-title a' )
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-video .video-title', '.module-video .video-title a' )
			),
			array(
				'id' => 'font_color_title_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-video .video-title:hover', '.module-video .video-title a:hover' )
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
						'selector' => '.module-video .video-title'
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
						'selector' => '.module-video .video-title'
					),
					array(
						'id' => 'line_height_title_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$video_caption = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_caption',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => '.module-video .video-content .video-caption'
			),
			array(
				'id' => 'font_color_caption',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-video .video-content .video-caption'
			),
			array(
				'id' => 'multi_font_size_caption',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_caption',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-video .video-content .video-caption'
					),
					array(
						'id' => 'font_size_caption_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_caption',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_caption',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-video .video-content .video-caption'
					),
					array(
						'id' => 'line_height_caption_unit',
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
						'label' => __( 'Video Title', 'themify' ),
						'fields' => $video_title
					),
					'caption' => array(
						'label' => __( 'Video Caption', 'themify' ),
						'fields' => $video_caption
					)
				)
			)
		);

	}

	public static function autoplay_callback( $match ){
		return str_replace( $match[1], add_query_arg( 'autoplay', 1, $match[1] ), $match[0] );
	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Video_Module' );