<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Testimonial
 * Description: Display testimonial custom post type
 */
class TB_Testimonial_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Testimonial', 'themify' ),
			'slug' => 'testimonial'
		));

		///////////////////////////////////////
		// Load Post Type
		///////////////////////////////////////
		$this->meta_box = $this->set_metabox();
		$this->initialize_cpt( array(
			'plural' => __( 'Testimonials', 'themify' ),
			'singular' => __( 'Testimonial', 'themify' ),
			'menu_icon' => 'dashicons-testimonial'
		));

		if ( ! shortcode_exists( 'themify_' . $this->slug .'_posts' ) ) {
			add_shortcode( 'themify_' . $this->slug . '_posts', array( $this, 'do_shortcode' ) );
		}
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
				'label' => __( 'Testimonial Layout', 'themify' ),
				'options' => array(
					array( 'img' => 'grid4.png', 'value' => 'grid4', 'label' => __( 'Grid 4', 'themify' ) ),
					array( 'img' => 'grid3.png', 'value' => 'grid3', 'label' => __( 'Grid 3', 'themify' ) ),
					array( 'img' => 'grid2.png', 'value' => 'grid2', 'label' => __( 'Grid 2', 'themify' ) ),
					array( 'img' => 'fullwidth.png', 'value' => 'fullwidth', 'label' => __( 'fullwidth', 'themify' ) )
				)
			),
			array(
				'id' => 'type_query_testimonial',
				'type' => 'radio',
				'label' => __( 'Query by', 'themify' ),
				'options' => array(
					'category' => __( 'Category', 'themify' ),
					'post_slug' => __( 'Slug', 'themify' )
				),
				'default' => 'category',
				'option_js' => true,
			),
			array(
				'id' => 'category_testimonial',
				'type' => 'query_category',
				'label' => __( 'Category', 'themify' ),
				'options' => array(
					'taxonomy' => 'testimonial-category'
				),
				'help' => sprintf(__( 'Add more <a href="%s" target="_blank">testimonials</a>', 'themify' ), admin_url('post-new.php?post_type=testimonial')),
				'wrap_with_class' => 'tf-group-element tf-group-element-category'
			),
			array(
				'id' => 'query_slug_testimonial',
				'type' => 'text',
				'label' => __( 'Testimonial Slugs', 'themify' ),
				'class' => 'large',
				'wrap_with_class' => 'tf-group-element tf-group-element-post_slug',
				'help' => '<br/>' . __( 'Insert Testimonial slug. Multiple slug should be separated by comma (,)', 'themify' )
			),
			array(
				'id' => 'post_per_page_testimonial',
				'type' => 'text',
				'label' => __( 'Limit', 'themify' ),
				'class' => 'xsmall',
				'help' => __( 'number of posts to show', 'themify' )
			),
			array(
				'id' => 'offset_testimonial',
				'type' => 'text',
				'label' => __( 'Offset', 'themify' ),
				'class' => 'xsmall',
				'help' => __( 'number of post to displace or pass over', 'themify' )
			),
			array(
				'id' => 'order_testimonial',
				'type' => 'select',
				'label' => __( 'Order', 'themify' ),
				'help' => __( 'Descending = show newer posts first', 'themify' ),
				'options' => array(
					'desc' => __( 'Descending', 'themify' ),
					'asc' => __( 'Ascending', 'themify' )
				)
			),
			array(
				'id' => 'orderby_testimonial',
				'type' => 'select',
				'label' => __( 'Order By', 'themify' ),
				'options' => array(
					'date' => __( 'Date', 'themify' ),
					'id' => __( 'Id', 'themify' ),
					'author' => __( 'Author', 'themify' ),
					'title' => __( 'Title', 'themify' ),
					'name' => __( 'Name', 'themify' ),
					'modified' => __( 'Modified', 'themify' ),
					'rand' => __( 'Random', 'themify' ),
					'comment_count' => __( 'Comment Count', 'themify' )
				)
			),
			array(
				'id' => 'display_testimonial',
				'type' => 'select',
				'label' => __( 'Display', 'themify' ),
				'options' => array(
					'content' => __( 'Content', 'themify' ),
					'excerpt' => __( 'Excerpt', 'themify' ),
					'none' => __( 'None', 'themify' )
				)
			),
			array(
				'id' => 'hide_feat_img_testimonial',
				'type' => 'select',
				'label' => __( 'Hide Featured Image', 'themify' ),
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'options' => array(
					'yes' => __( 'Yes', 'themify' ),
					'no' => __( 'No', 'themify' )
				)
			),
			array(
				'id' => 'image_size_testimonial',
				'type' => 'select',
				'label' => Themify_Builder_Model::is_img_php_disabled() ? __( 'Image Size', 'themify' ) : false,
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'hide' => ! Themify_Builder_Model::is_img_php_disabled(),
				'options' => themify_get_image_sizes_list( false )
			),
			array(
				'id' => 'img_width_testimonial',
				'type' => 'text',
				'label' => __( 'Image Width', 'themify' ),
				'class' => 'xsmall'
			),
			array(
				'id' => 'img_height_testimonial',
				'type' => 'text',
				'label' => __( 'Image Height', 'themify' ),
				'class' => 'xsmall'
			),
			array(
				'id' => 'hide_post_title_testimonial',
				'type' => 'select',
				'label' => __( 'Hide Post Title', 'themify' ),
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'options' => array(
					'yes' => __( 'Yes', 'themify' ),
					'no' => __( 'No', 'themify' )
				)
			),
			array(
				'id' => 'hide_page_nav_testimonial',
				'type' => 'select',
				'label' => __( 'Hide Page Navigation', 'themify' ),
				'options' => array(
					'yes' => __( 'Yes', 'themify' ),
					'no' => __( 'No', 'themify' )
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
				'selector' => array( '.module-testimonial .post' )
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
				'selector' => array( '.module-testimonial .post-title', '.module-testimonial .post-title a' ),
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-testimonial .post', '.module-testimonial h1', '.module-testimonial h2', '.module-testimonial h3:not(.module-title)', '.module-testimonial h4', '.module-testimonial h5', '.module-testimonial h6', '.module-testimonial .post-title', '.module-testimonial .post-title a' ),
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
						'selector' => '.module-testimonial .post'
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
						'selector' => '.module-testimonial .post'
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
				'selector' => '.module-testimonial .post',
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-testimonial .post'
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
						'selector' => '.module-testiomonial .post'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-testiomonial .post'
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
				'selector' => '.module-testimonial a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-testimonial a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-testimonial a'
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial .post', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial .post', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial .post', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial .post', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-testimonial .post', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial .post', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial .post', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial .post', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial .post', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-testimonial .post', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial .post', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial .post', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial .post', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial .post', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-testimonial .post', 'all' )
		);

		$testimonial_title = array(
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
				'selector' => array( '.module-testimonial .post-title', '.module-testimonial .post-title a' )
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-testimonial .post-title', '.module-testimonial .post-title a' )
			),
			array(
				'id' => 'font_color_title_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-testimonial .post-title:hover', '.module-testimonial .post-title a:hover' )
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
						'selector' => '.module-testimonial .post-title'
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
						'selector' => '.module-testimonial .post-title'
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
				'selector' => '.module-testimonial .testimonial-post .post-content'
			),
			array(
				'id' => 'font_color_content',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-testimonial .testimonial-post .post-content'
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
						'selector' => '.module-testimonial .testimonial-post .post-content'
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
						'selector' => '.module-testimonial .testimonial-post .post-content'
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
			),
		);

	}

	function set_metabox() {
		// Testimonial Meta Box Options
		$meta_box = array(
			// Feature Image
			Themify_Builder_Model::$post_image,
			// Featured Image Size
			Themify_Builder_Model::$featured_image_size,
			// Image Width
			Themify_Builder_Model::$image_width,
			// Image Height
			Themify_Builder_Model::$image_height,
			// Testimonial Author Name
			array(
				'name' 		=> '_testimonial_name',
				'title' 	=> __( 'Testimonial Author Name', 'themify' ),
				'description' => '',
				'type' 		=> 'textbox',
				'meta'		=> array()
			),
			// Testimonial Author Link
			array(
				'name' 		=> '_testimonial_link',
				'title' 	=> __( 'Testimonial Author Link', 'themify' ),
				'description' => '',
				'type' 		=> 'textbox',
				'meta'		=> array()
			),
			// Testimonial Author Company
			array(
				'name' 		=> '_testimonial_company',
				'title' 	=> __( 'Testimonial Author Company', 'themify' ),
				'description' => '',
				'type' 		=> 'textbox',
				'meta'		=> array()
			),
			// Testimonial Author Position
			array(
				'name' 		=> '_testimonial_position',
				'title' 	=> __( 'Testimonial Author Position', 'themify' ),
				'description' => '',
				'type' 		=> 'textbox',
				'meta'		=> array()
			)
		);
		return $meta_box;
	}

	function do_shortcode( $atts ) {
		global $ThemifyBuilder;

		extract( shortcode_atts( array(
			'id' => '',
			'title' => 'no', // no
			'image' => 'yes', // no
			'image_w' => 80,
			'image_h' => 80,
			'display' => 'content', // excerpt, none
			'more_link' => false, // true goes to post type archive, and admits custom link
			'more_text' => __( 'More &rarr;', 'themify' ),
			'limit' => 4,
			'category' => 0, // integer category ID
			'order' => 'DESC', // ASC
			'orderby' => 'date', // title, rand
			'style' => 'grid2', // grid3, grid4, list-post
			'show_author' => 'yes', // no
			'section_link' => false // true goes to post type archive, and admits custom link
		), $atts ) );

		$sync = array(
			'mod_title_testimonial' => '',
			'layout_testimonial' => $style,
			'category_testimonial' => $category,
			'post_per_page_testimonial' => $limit,
			'offset_testimonial' => '',
			'order_testimonial' => $order,
			'orderby_testimonial' => $orderby,
			'display_testimonial' => $display,
			'hide_feat_img_testimonial' => '',
			'image_size_testimonial' => '',
			'img_width_testimonial' => $image_w,
			'img_height_testimonial' => $image_h,
			'unlink_feat_img_testimonial' => 'no',
			'hide_post_title_testimonial' => $title == 'yes' ? 'no' : 'yes',
			'unlink_post_title_testimonial' => 'no',
			'hide_post_date_testimonial' => 'no',
			'hide_post_meta_testimonial' => 'no',
			'hide_page_nav_testimonial' => 'yes',
			'animation_effect' => '',
			'css_testimonial' => ''
		);
		$module = array(
			'module_ID' => $this->slug . '-' . rand(0,10000),
			'mod_name' => $this->slug,
			'mod_settings' => $sync
		);

		return $ThemifyBuilder->retrieve_template( 'template-' . $this->slug . '.php', $module, '', '', false );
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
if( $this->is_cpt_active( 'testimonial' ) ) {
	Themify_Builder_Model::register_module( 'TB_Testimonial_Module' );
}