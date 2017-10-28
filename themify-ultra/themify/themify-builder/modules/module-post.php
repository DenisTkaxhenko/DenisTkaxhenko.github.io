<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Module Name: Post
 * Description: Display Posts
 */
class TB_Post_Module extends Themify_Builder_Module {
	function __construct() {
		parent::__construct(array(
			'name' => __( 'Post', 'themify' ),
			'slug' => 'post'
		));
	}

	public function get_title( $module ) {
		$type = isset( $module['mod_settings']['type_query_post'] ) ? $module['mod_settings']['type_query_post'] : 'category';
		$category = isset( $module['mod_settings']['category_post'] ) ? $module['mod_settings']['category_post'] : '';
		$slug_query = isset( $module['mod_settings']['query_slug_post'] ) ? $module['mod_settings']['query_slug_post'] : '';

		if ( 'category' == $type ) {
			return sprintf( '%s : %s', __( 'Category', 'themify' ), $category );
		} else {
			return sprintf( '%s : %s', __( 'Slugs', 'themify' ), $slug_query );
		}
	}

	public function get_options() {
		global $ThemifyBuilder;

		$taxonomies = $ThemifyBuilder->get_public_taxonomies();
		$term_options = array();

		foreach( $taxonomies as $key => $label ) {
			$term_options[] = array(
				'id' => "{$key}_post",
				'label' => $label,
				'type' => 'query_category',
				'options' => array( 'taxonomy' => $key ),
				'wrap_with_class' => "tf-group-element tf-group-element-{$key}"
			);
		}

		/* allow query posts by slug */
		$taxonomies['post_slug'] = __( 'Slug', 'themify' );

		$options = array(
			array(
				'id' => 'mod_title_post',
				'type' => 'text',
				'label' => __( 'Module Title', 'themify' ),
				'class' => 'large'
			),
			array(
				'id' => 'layout_post',
				'type' => 'layout',
				'label' => __( 'Post Layout', 'themify' ),
				'options' => array(
					array( 'img' => 'list-post.png', 'value' => 'list-post', 'label' => __( 'List Post', 'themify' ) ),
					array( 'img' => 'grid3.png', 'value' => 'grid3', 'label' => __( 'Grid 3', 'themify' ) ),
					array( 'img' => 'grid2.png', 'value' => 'grid2', 'label' => __( 'Grid 2', 'themify' ) ),
					array( 'img' => 'grid4.png', 'value' => 'grid4', 'label' => __( 'Grid 4', 'themify' ) ),
					array( 'img' => 'list-thumb-image.png', 'value' => 'list-thumb-image', 'label' => __( 'List Thumb Image', 'themify' ) ),
					array( 'img' => 'grid2-thumb.png', 'value' => 'grid2-thumb', 'label' => __( 'Grid 2 Thumb', 'themify' ) )
				),
				'render_callback' => array(
					'binding' => 'live',
					'selector' => '> .builder-posts-wrap'
				)
			),
			array(
				'id' => 'post_type_post',
				'type' => 'select',
				'label' => __( 'Post Type', 'themify' ),
				'options' => $ThemifyBuilder->get_public_post_types()
			),
			array(
				'id' => 'type_query_post',
				'type' => 'radio',
				'label' => __( 'Query by', 'themify' ),
				'options' => $taxonomies,
				'default' => 'category',
				'option_js' => true
			),
			array(
				'type' => 'group',
				'fields' => $term_options
			),
			array(
				'id' => 'query_slug_post',
				'type' => 'text',
				'label' => __( 'Post Slugs', 'themify' ),
				'class' => 'large',
				'wrap_with_class' => 'tf-group-element tf-group-element-post_slug',
				'help' => '<br/>' . __( 'Insert post slug. Multiple slug should be separated by comma (,)', 'themify' )
			),
			array(
				'id' => 'post_per_page_post',
				'type' => 'text',
				'label' => __( 'Limit', 'themify' ),
				'class' => 'xsmall',
				'help' => __( 'number of posts to show', 'themify' )
			),
			array(
				'id' => 'offset_post',
				'type' => 'text',
				'label' => __( 'Offset', 'themify' ),
				'class' => 'xsmall',
				'help' => __( 'number of post to displace or pass over', 'themify' )
			),
			array(
				'id' => 'order_post',
				'type' => 'select',
				'label' => __( 'Order', 'themify' ),
				'help' => __( 'Descending = show newer posts first', 'themify' ),
				'options' => array(
					'desc' => __( 'Descending', 'themify' ),
					'asc' => __( 'Ascending', 'themify' )
				)
			),
			array(
				'id' => 'orderby_post',
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
				'id' => 'display_post',
				'type' => 'select',
				'label' => __( 'Display', 'themify' ),
				'options' => array(
					'content' => __( 'Content', 'themify' ),
					'excerpt' => __( 'Excerpt', 'themify' ),
					'none' => __( 'None', 'themify' )
				)
			),
			array(
				'id' => 'hide_feat_img_post',
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
				'id' => 'image_size_post',
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
				'id' => 'img_width_post',
				'type' => 'text',
				'label' => __( 'Image Width', 'themify' ),
				'class' => 'xsmall'
			),
			array(
				'id' => 'img_height_post',
				'type' => 'text',
				'label' => __( 'Image Height', 'themify' ),
				'class' => 'xsmall'
			),
			array(
				'id' => 'unlink_feat_img_post',
				'type' => 'select',
				'label' => __( 'Unlink Featured Image', 'themify' ),
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
				'id' => 'hide_post_title_post',
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
				'id' => 'unlink_post_title_post',
				'type' => 'select',
				'label' => __( 'Unlink Post Title', 'themify' ),
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
				'id' => 'hide_post_date_post',
				'type' => 'select',
				'label' => __( 'Hide Post Date', 'themify' ),
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
				'id' => 'hide_post_meta_post',
				'type' => 'select',
				'label' => __( 'Hide Post Meta', 'themify' ),
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
				'id' => 'hide_page_nav_post',
				'type' => 'select',
				'label' => __( 'Hide Page Navigation', 'themify' ),
				'empty' => array(
					'val' => '',
					'label' => ''
				),
				'options' => array(
					'yes' => __( 'Yes', 'themify' ),
					'no' => __( 'No', 'themify' )
				),
				'default' => 'Yes'
			),
			// Additional CSS
			array(
				'type' => 'separator',
				'meta' => array( 'html' => '<hr/>')
			),
			array(
				'id' => 'css_post',
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
			'layout_post' => 'grid4',
			'post_per_page_post' => 4,
			'display_post' => 'excerpt'
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
				'selector' => array( '.module-post .post' )
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
				'selector' => array( '.module-post .post-title', '.module-post .post-title a' ),
			),
			array(
				'id' => 'font_color',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-post .post', '.module-post h1', '.module-post h2', '.module-post h3:not(.module-title)', '.module-post h4', '.module-post h5', '.module-post h6', '.module-post .post-title', '.module-post .post-title a' ),
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
						'selector' => array( '.module-post .post' ),
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
						'selector' => array( '.module-post .post' ),
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
				'selector' => '.module-post .post',
			),
			array(
				'id' => 'text_transform',
				'label' => __( 'Text Transform', 'themify' ),
				'type' => 'icon_radio',
				'meta' => Themify_Builder_Model::get_text_transform(),
				'prop' => 'text-transform',
				'selector' => '.module-post .post'
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
						'selector' => '.module-post .post'
					),
					array(
						'id' => 'text_decoration_regular',
						'type' => 'icon_radio',
						'meta' => Themify_Builder_Model::get_text_decoration(),
						'prop' => 'text-decoration',
						'class' => 'inline',
						'selector' => '.module-post .post'
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
				'selector' => '.module-post a'
			),
			array(
				'id' => 'link_color_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-post a:hover'
			),
			array(
				'id' => 'text_decoration',
				'type' => 'select',
				'label' => __( 'Text Decoration', 'themify' ),
				'meta'	=> Themify_Builder_Model::get_text_decoration( true ),
				'prop' => 'text-decoration',
				'selector' => '.module-post a'
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
			Themify_Builder_Model::get_field_group( 'padding', '.module-post .post', 'top' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-post .post', 'right' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-post .post', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-post .post', 'left' ),
			Themify_Builder_Model::get_field_group( 'padding', '.module-post .post', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'margin', '.module-post .post', 'top' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-post .post', 'right' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-post .post', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-post .post', 'left' ),
			Themify_Builder_Model::get_field_group( 'margin', '.module-post .post', 'all' ),
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
			Themify_Builder_Model::get_field_group( 'border', '.module-post .post', 'top' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-post .post', 'right' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-post .post', 'bottom' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-post .post', 'left' ),
			Themify_Builder_Model::get_field_group( 'border', '.module-post .post', 'all' )
		);

		$post_title = array(
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
				'selector' => array( '.module-post .post-title', '.module-post .post-title a' )
			),
			array(
				'id' => 'font_color_title',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-post .post-title', '.module-post .post-title a' )
			),
			array(
				'id' => 'font_color_title_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array( '.module-post .post-title:hover', '.module-post .post-title a:hover' )
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
						'selector' => '.module-post .post-title'
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
						'selector' => '.module-post .post-title'
					),
					array(
						'id' => 'line_height_title_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$post_meta = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_meta',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array('.module-post .post-content .post-meta', '.module-post .post-content .post-meta a')
			),
			array(
				'id' => 'font_color_meta',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array('.module-post .post-content .post-meta', '.module-post .post-content .post-meta a')
			),
			array(
				'id' => 'font_color_meta_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array('.module-post .post-content .post-meta:hover', '.module-post .post-content .post-meta a:hover')
			),
			array(
				'id' => 'multi_font_size_meta',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_meta',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-post .post-content .post-meta'
					),
					array(
						'id' => 'font_size_meta_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_meta',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_meta',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-post .post-content .post-meta'
					),
					array(
						'id' => 'line_height_meta_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$post_date = array(
			// Font
			array(
				'id' => 'separator_font',
				'type' => 'separator',
				'meta' => array( 'html' => '<h4>' . __( 'Font', 'themify' ) . '</h4>' )
			),
			array(
				'id' => 'font_family_date',
				'type' => 'font_select',
				'label' => __( 'Font Family', 'themify' ),
				'class' => 'font-family-select',
				'prop' => 'font-family',
				'selector' => array('.module-post .post .post-date', '.module-post .post .post-date a')
			),
			array(
				'id' => 'font_color_date',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array('.module-post .post .post-date', '.module-post .post .post-date a')
			),
			array(
				'id' => 'font_color_date_hover',
				'type' => 'color',
				'label' => __( 'Color Hover', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => array('.module-post .post .post-date:hover', '.module-post .post .post-date a:hover')
			),
			array(
				'id' => 'multi_font_size_date',
				'type' => 'multi',
				'label' => __( 'Font Size', 'themify' ),
				'fields' => array(
					array(
						'id' => 'font_size_date',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'font-size',
						'selector' => '.module-post .post .post-date'
					),
					array(
						'id' => 'font_size_date_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
			array(
				'id' => 'multi_line_height_date',
				'type' => 'multi',
				'label' => __( 'Line Height', 'themify' ),
				'fields' => array(
					array(
						'id' => 'line_height_date',
						'type' => 'text',
						'class' => 'xsmall',
						'prop' => 'line-height',
						'selector' => '.module-post .post .post-date'
					),
					array(
						'id' => 'line_height_date_unit',
						'type' => 'select',
						'meta' => Themify_Builder_Model::get_css_units()
					)
				)
			),
		);

		$post_content = array(
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
				'selector' => '.module-post .post-content .entry-content'
			),
			array(
				'id' => 'font_color_content',
				'type' => 'color',
				'label' => __( 'Font Color', 'themify' ),
				'class' => 'small',
				'prop' => 'color',
				'selector' => '.module-post .post-content .entry-content'
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
						'selector' => '.module-post .post-content .entry-content'
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
						'selector' => '.module-post .post-content .entry-content'
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
						'label' => __( 'Post Title', 'themify' ),
						'fields' => $post_title
					),
					'meta' => array(
						'label' => __( 'Post Meta', 'themify' ),
						'fields' => $post_meta
					),
					'date' => array(
						'label' => __( 'Post Date', 'themify' ),
						'fields' => $post_date
					),
					'content' => array(
						'label' => __( 'Post Content', 'themify' ),
						'fields' => $post_content
					)
				)
			),
		);

	}
}

///////////////////////////////////////
// Module Options
///////////////////////////////////////
Themify_Builder_Model::register_module( 'TB_Post_Module' );

/**
 * Title tag settings for Post module
 *
 * @return array
 */
function themify_builder_post_title_args( $args ) {
	$args['tag'] = 'h2';
	return $args;
}