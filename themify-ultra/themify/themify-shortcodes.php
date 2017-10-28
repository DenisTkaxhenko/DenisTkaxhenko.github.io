<?php
/***************************************************************************
 *
 * 	----------------------------------------------------------------------
 * 						DO NOT EDIT THIS FILE
 *	----------------------------------------------------------------------
 * 
 *  				     Copyright (C) Themify
 * 
 *	----------------------------------------------------------------------
 *
 * Shortcodes:
 * 		button
 * 		col
 * 		img
 * 		hr
 * 		quote
 * 		is_logged_in
 * 		is_guest
 * 		map
 * 		video
 * 		flickr
 * 		twitter
 * 		instagram
 * 		post_slider
 * 		slider
 * 		list_posts
 * 		box
 * 		author-box
 *		icon
 * 
 * Functions:
 *		themify_shortcode_list
 * 		themify_shortcodes_js_css
 * 		themify_shortcode
 * 		themify_shortcode_list_posts
 * 		themify_shortcode_flickr
 *		themify_shortcode_twitter
 *		themify_shortcode_instagram
 * 		themify_shortcode_slide
 * 		themify_shortcode_slider
 * 		themify_shortcode_post_slider
 * 		themify_shortcode_author_box
 * 		themify_shortcode_box
 *		themify_fix_shortcode_empty_paragraph
 * 
 ***************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Creates shortcodes
 * @param Object $atts
 * @param String $content
 * @param String $code
 * @return String
 */
function themify_shortcode( $atts, $content = null, $code = '' ) {
	switch ( $code ) {
		case 'is_logged_in':
		case 'themify_is_logged_in':
			if ( is_user_logged_in() ) {
				return do_shortcode( $content );
			}
		break;
		case 'is_guest':
		case 'themify_is_guest':
			if ( ! is_user_logged_in() ) {
				return do_shortcode( $content );
			}
		break;
		case 'button':
		case 'themify_button':
			extract( shortcode_atts( array(
				'bgcolor' => '',
				'size' 	=> '',
				'block' => false,
				'style'	=> '',
				'link' 	=> '#',
				'target'=> '',
				'onclick'=> '',
				'color' => '',
				'text'	=> ''
			), $atts, 'themify_button' ) );
			$style = join( ' ', array( $bgcolor, $size, ( $block ? 'block' : '' ), $style ) );

			if($color){
				$color = "background-color: $color;";
			}
			if( $text ) {
				$text = "color: $text;";	
			}
			$html = '<a href="' . esc_url( $link ) . '" class="shortcode button '. esc_attr( $style ) . '"';
			if( $color || $text ) {
				$html.=' style="'.esc_attr( $color.$text ).'"';
			}
			if( $target ) {
				$html.=' target="'.esc_attr( $target ).'"';
			}
			if( $onclick ) {
				$html.=' onclick="' . esc_attr( $onclick ) . '"';
			}
			$html.='>'.do_shortcode( $content ).'</a>';
			return $html;
		break;
		case 'quote':
		case 'themify_quote':
			return '<blockquote class="shortcode quote">' . do_shortcode( preg_replace( array( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '~\s?<p>(\s|&nbsp;)+</p>\s?~' ), '', force_balance_tags( $content ) ) ) . '</blockquote>';
		break;
		case 'col':
		case 'themify_col':
			extract( shortcode_atts( array( 'grid' => '' ), $atts, 'themify_col' ) );
			return '<div class="shortcode col' . esc_attr( $grid ) . '">' . do_shortcode( preg_replace( array( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '~\s?<p>(\s|&nbsp;)+</p>\s?~' ), '', force_balance_tags( $content ) ) ) . '</div>';
		break;
		case 'sub_col':
		case 'themify_sub_col':
			extract( shortcode_atts( array( 'grid' => '' ), $atts, 'themify_sub_col' ) );
			return '<div class="shortcode col' . esc_attr( $grid ) . '">' . do_shortcode( preg_replace( array( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '~\s?<p>(\s|&nbsp;)+</p>\s?~' ), '', force_balance_tags( $content ) ) ) . "</div>";
		break;
		case 'img':
		case 'themify_img':
			extract( shortcode_atts( array(
				'class' => '',
				'src' 	=> '',
				'id'	=> '',
				'h'		=> '',
				'w'		=> '',
				'crop'	=> true,
				), $atts, 'themify_img' ) );
			return themify_get_image("class=$class&src=$src&id=$id&h=$h&w=$w&crop=$crop&ignore=true");
		break;
		case 'hr':
		case 'themify_hr':
			extract( shortcode_atts( array(
				'color' => '',
				'width' => '',
				'border_width' => ''
			), $atts, 'themify_hr' ));
			if( '' != $width || '' != $border_width  ){
				$hrstyle = 'style="';
				if( '' != $width  ){
					$hrstyle .= 'width:' . $width . ';';
				}
				if( '' != $border_width  ){
					if( preg_match('/MSIE 7/i', $_SERVER['HTTP_USER_AGENT'] ) ){
						$hrstyle .= 'height:' . $border_width . ';';
					}
					$hrstyle .= 'border-width:' . $border_width . ';';
				}
				$hrstyle .= '"';
			} else {
				$hrstyle = '';
			}
			return '<hr class="shortcode hr ' . esc_attr( $color ) . '" ' . $hrstyle . ' />';
		break;
		case 'map':
		case 'themify_map':
			extract( shortcode_atts(
				array(
					'address' => '99 Blue Jays Way, Toronto, Ontario, Canada',
					'width' => '500px',
					'height' => '300px',
					'zoom' => 15,
					'type' => 'ROADMAP',
					'scroll_wheel' => 'yes',
					'draggable_disable_mobile_map' => 'yes',
					'draggable' => 'yes',
				),
				$atts,
				'themify_map'
			));

			/* if no unit is provided for width and height, use "px" */
			if( ! preg_match( '/[px|%]$/', $width ) ) {
				$width = $width . 'px';
			}
			if( ! preg_match( '/[px|%]$/', $height ) ) {
				$height = $height . 'px';
			}

			if ('yes' == $draggable && 'yes' == $draggable_disable_mobile_map && wp_is_mobile()){
				$draggable = 'disable';
			}
			$num = rand(0,10000);
			$data['address'] = $address;
			$data['zoom'] = $zoom;
			$data['type'] = $type;
			$data['scroll'] =  $scroll_wheel =='yes';
			$data['drag'] =  $draggable=='yes';
			return '
			<div class="shortcode map">
				<div data-map="'.esc_attr(base64_encode(json_encode($data))).'" id="themify_map_canvas_' . esc_attr( $num ) . '" style="display: block;width:' . esc_attr( $width ) . ';height:' . esc_attr( $height ) . ';" class="map-container themify_map"></div>
			</div>';
		break;
		case 'video':
		case 'themify_video':
			extract( shortcode_atts(
				array(
					'width' => '500px',
					'height' => '300px',
					'src' => '#'
				),
				$atts,
				'themify_video'
			));
			$num = rand(0,10000);
			if( stripos($_SERVER['HTTP_USER_AGENT'], 'iPod') || stripos($_SERVER['HTTP_USER_AGENT'], 'iPhone') ||
				stripos($_SERVER['HTTP_USER_AGENT'], 'iPad') ||	stripos($_SERVER['HTTP_USER_AGENT'], 'Android') ) {
				return '<div class="shortcode video"><video src="' . esc_url( $src ) . '"></video></div>';
			} else {
				return '<div class="shortcode video themify_video_desktop"><a href="' . esc_url( $src ) . '" style="display:block;width:' . esc_attr( $width ) . ';height:' . esc_attr( $height ) . '" id="themify_player_' . esc_attr( $num ) . '"></a></div>';
			}
		break;
	}
	return '';
}

/**
 * Get a WP_Query parameters from the shortcode attributes
 *
 * @param $atts the array of shortcode parameters supplied by user
 * @param $shortcode the name of shortcode calling, provides "shortcode_atts_$shortcode" filter
 * @param $defaults allows overriding the default shortcode atts, before being replaced by $atts
 *
 * @return array
 */
function themify_parse_shortcode_query_atts( $atts, $shortcode = '', $defaults = array() ) {

	$defaults = wp_parse_args( $defaults, array(
		'category' => '0',
		'limit' => '5',
		'offset' => '0',
		'post_type' => 'post',
		/**
		 * backward compatibility fix
		 * get terms from proper taxonomy for post types registered by Themify themes
		 */
		'taxonomy' => ( isset( $atts['post_type'] ) && 'post' != $atts['post_type'] ) ? $atts['post_type'] . '-category' : 'category',
		'order' => 'DESC',
		'orderby' => 'date',
		'taxonomy_relation' => 'AND',
	) );

	extract( shortcode_atts( $defaults, $atts, $shortcode ) );

	$query_args = array(
		'posts_per_page' => $limit,
		'offset' => $offset,
		'post_type' => $post_type,
		'taxonomy' => $taxonomy,
		'order' => $order,
		'orderby' => $orderby,
		'suppress_filters' => false,
		'post__not_in' => is_singular() ? array( get_the_id() ) : array(),
	);

	if ( '0' !== $category ) {
		$category = array_map( 'trim', explode( ',', $category ) );
		$ids_in = array_filter( $category, create_function( '$a', 'return is_numeric( $a ) && "-" !== $a[0];' ) );
		$ids_not_in = array_filter( $category, create_function( '$a', 'return is_numeric( $a ) && "-" === $a[0];' ) );
		$slugs_in = array_filter( $category, create_function( '$a', 'return ! is_numeric( $a ) && "-" !== $a[0];' ) );
		$slugs_not_in = array_filter( $category, create_function( '$a', 'return ! is_numeric( $a ) && "-" === $a[0];' ) );

		$query_args['tax_query'] = array(
			'relation' => $taxonomy_relation
		);
		if ( ! empty( $ids_in ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field' => 'id',
				'terms' => $ids_in
			);
		}
		if ( ! empty( $ids_not_in ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field' => 'id',
				'terms' => array_map( 'abs', $ids_not_in ),
				'operator' => 'NOT IN'
			);
		}
		if ( ! empty( $slugs_in ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => $slugs_in
			);
		}
		if ( ! empty( $slugs_not_in ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'field' => 'slug',
				'terms' => array_map( create_function( '$a', 'return substr( $a, 1 );' ), $slugs_not_in ), // remove the minus sign (first character)
				'operator' => 'NOT IN'
			);
		}
	}

	return $query_args;
}

/**
 * List posts using get_posts
 * @param Object $atts
 * @param String $content
 * @return String
 */
function themify_shortcode_list_posts( $atts, $content = null ) {
	global $themify;
	
	// Set defaults for featured image in different layouts
	$default_size = array(
		'image_w' => 1160,
		'image_h' => 665
	);
	
	if ( isset( $atts['style'] ) ) {
		switch ( $atts['style'] ) {
			case 'grid4':
				$default_size['image_w'] = 260;
				$default_size['image_h'] = 150;
			break;
			case 'grid3':
				$default_size['image_w'] = 360;
				$default_size['image_h'] = 205;
			break;
			case 'grid2':
				$default_size['image_w'] = 561;
				$default_size['image_h'] = 321;
			break;
			case 'list-thumb-image':
				$default_size['image_w'] = 260;
				$default_size['image_h'] = 150;
			break;
			case 'grid2-thumb':
				$default_size['image_w'] = 160;
				$default_size['image_h'] = 95;
			break;
			case 'list-post':
			default:
				$default_size['image_w'] = 1160;
				$default_size['image_h'] = 665;
			break;
		}
	}

	extract( shortcode_atts( array(
		'title' => 'yes',
		'more_text' => __('More...', 'themify'),
		'excerpt_length' => '',
		'image' => 'yes',
		'image_w' => $default_size['image_w'],
		'image_h' => $default_size['image_h'],
		'display' => 'none',
		'style' => 'list-post',
		'post_date' => 'no',
		'post_meta' => 'no',
		'unlink_title' => 'no',
		'unlink_image' => 'no',
		'image_size' => 'medium',
		'post_type' => 'post',
	), $atts, 'themify_list_posts' ) );

	$query_args = themify_parse_shortcode_query_atts( $atts, 'themify_list_posts' );

	$the_query = new WP_Query();
	$posts = $the_query->query( apply_filters( 'themify_list_posts_shortcode_query_args', $query_args, $atts ) );
	// save a copy
	$themify_save = clone $themify;

	// override $themify object
	$themify->hide_image = 'yes' == $image? 'no' : 'yes';
	$themify->unlink_image = $unlink_image;
	$themify->hide_title = 'yes' == $title? 'no' : 'yes';
	$themify->width = $image_w;
	$themify->height = $image_h;
	$themify->image_setting = 'ignore=true&';
	$themify->unlink_title = $unlink_title;
	$themify->display_content = $display;
	$themify->hide_date = 'yes' == $post_date? 'no' : 'yes';
	$themify->hide_meta = 'yes' == $post_meta? 'no' : 'yes';
	$themify->post_layout = $style;
	$themify->is_shortcode = true;
	$themify->image_size = $image_size;

	$out = '';
	if ($posts){
		$out = '<!-- shortcode list_posts -->';
		$out.='<div class="loops-wrapper shortcode clearfix list-posts layout ' . esc_attr( $style ) . ' ">';
		$out .= themify_get_shortcode_template( $posts, 'includes/loop', $post_type );
		$out .= '</div>';
		$out.='<!-- /shortcode list_posts -->';
	}

	// revert to original $themify state
	$themify = clone $themify_save;

	return $out;
}

/**
 * Insert Flickr Gallery by user, set or group
 * @param Object $atts
 * @param String $content
 * @return String
 */	
function themify_shortcode_flickr( $atts, $content = null ) {
	extract(shortcode_atts(array(
		'user' => '',
		'set' => '',
		'group' => '',
		'limit' => '8',
		'size' => 's',
		'display' => 'latest'
	), $atts, 'themify_flickr' ));
	$flickrstr = '';
        $url = themify_https_esc( '//www.flickr.com/badge_code_v2.gne' ) . '?count=' . $limit . '&amp;display=' . $display . '&amp;size=' . $size . '&amp;layout=x&amp;';
	if ( $user ) {
		$url.='source=user&amp;user=' . $user;
	}
	elseif ( $set) {
		$url.='source=user_set&amp;set=' . $set;
	}
	elseif ( $group ) {
		$url.='source=group&amp;group=' . $group;
	}
	else{
		$url = false;
	}
	if($url){
		$url = esc_url($url);
		$flickrstr='<!-- shortcode Flickr --><div class="shortcode clearfix flickr"><script type="text/javascript" src="'.$url.'"></script></div> <!-- /shortcode Flickr -->';
	}
	return $flickrstr;
}

/**
 * Creates one slide for the slider shortcode
 * @param Object $atts
 * @param String $content
 * @return String
 */
function themify_shortcode_slide( $atts, $content = null ) {
	extract( shortcode_atts( array(), $atts ) );
	$output = '<li><div class="slide-wrap">' . do_shortcode( $content ) . '</div></li>';
	return $output;
}

/**
 * Creates a slider using the slide shortcode
 * @param Object $atts
 * @param String $content
 * @return String
 */
function themify_shortcode_slider( $atts, $content = null ){

	extract( shortcode_atts( array(
		'wrap' => 'yes',
		'visible' => '1',
		'scroll' => '1',
		'auto' => '0',
		'pause_hover' => 'no',
		'speed' => 'normal',
		'slider_nav' => 'yes',
		'pager' => 'yes',
		'effect' => 'scroll',
		'class' => '',
		'height' => 'auto', // [auto / variable]
	), $atts, 'themify_slider' ) );
	$numsldrtemp = rand( 0, 10000 );
	$content = do_shortcode( shortcode_unautop( $content ) );
		
	$class .= ' effect-' . $effect;
	switch ( $speed ) {
		case 'fast':
			$speed = '.5';
		break;
		case 'normal':
			$speed = '1';
		break;
		case 'slow':
			$speed = '4';
		break;
	}
	$js_data['slider_nav'] = $slider_nav=='yes'?1:0;
	$js_data['pager'] = $pager=='yes'?1:0;
	$js_data['wrapvar'] = $wrap == 'yes'?1:0;
	$js_data['auto'] =  intval( $auto );
	$js_data['pause_hover'] = $pause_hover == 'yes'?1:0;
	$js_data['speed'] = $speed;
	$js_data['scroll'] = $scroll;
	$js_data['effect'] = $effect;
	$js_data['visible'] = intval( $visible );
	$js_data['numsldr'] = rand( 0, 1011 ).uniqid();
	$js_data['height'] = $height;

	$strsldr = '<!-- shortcode slider --><div id="slider-' . esc_attr( $js_data['numsldr'] ) . '" class="shortcode clearfix slider ' . esc_attr( $class ) . '">
	<ul data-slider="'.esc_attr(base64_encode( json_encode( $js_data ) ) ). '" class="slides">' . $content . '</ul>';	
	$strsldr .= '</div>
		<!-- /shortcode slider -->';
	return $strsldr;
}

/**
 * Create a slider with posts retrieved through get_posts
 * @param Object $atts
 * @param String $content
 * @return String
 */
function themify_shortcode_post_slider( $atts, $content = null ) {
	global $post, $themify;

	extract( shortcode_atts( array(
		'visible' => '1',
		'scroll' => '1',
		'auto' => '0',
		'pause_hover' => 'no',
		'wrap' => 'yes',
		'excerpt_length' => '20',
		'speed' => 'normal',
		'slider_nav' => 'yes',
		'pager' => 'yes',
		'image' => 'yes',
		'image_w' => '240px',
		'image_h' => '180px',
		'more_text' => __('More...', 'themify'),
		'title' => 'yes',
		'display' => 'none',
		'post_meta' => 'no',
		'post_date' => 'no',
		'width' => '100%',
		'height' => 'auto',
		'class' => '',
		'unlink_title' => 'no',
		'unlink_image' => 'no',
		'image_size' => 'medium',
		'effect' => 'scroll',
		'post_type' => 'post',
	), $atts, 'themify_post_slider' ) );

	$postsliderstr = '';

	$query_args = themify_parse_shortcode_query_atts( $atts, 'themify_post_slider' );
	$posts = get_posts( apply_filters( 'themify_post_slider_shortcode_query_args', $query_args, $atts ) );
	if ($posts) {
		switch ( $speed ) {
			case 'fast':
				$speed = '.5';
			break;
			case 'normal':
				$speed = '1';
			break;
			case 'slow':
				$speed = '4';
			break;
		}
		$class .= ' effect-' . $effect;
		$js_data['slider_nav'] = $slider_nav == 'yes' ? 1 : 0;
		$js_data['pager'] = $pager == 'yes' ? 1 : 0;
		$js_data['wrapvar'] = $wrap == 'yes' ? 1 : 0;
		$js_data['auto'] =  intval($auto);
		$js_data['pause_hover'] = $pause_hover == 'yes' ? 1 : 0;
		$js_data['speed'] = $speed;
		$js_data['scroll'] = $scroll;
		$js_data['effect'] = $effect;
		$js_data['visible'] = intval($visible);
		$js_data['height'] = $height;
		$js_data['numsldr'] = rand(0,1011).uniqid();
		$postsliderstr = '<!-- shortcode post_slider --> <div id="slider-' . esc_attr(  $js_data['numsldr'] ) . '" style="width: ' . esc_attr( $width ) . ';" class="shortcode clearfix post-slider ' . $class . '">
		<ul class="slides" data-slider="'.esc_attr(base64_encode(json_encode($js_data))).'">';
		unset($js_data);

		$themify_save = clone $themify;

		// override $themify object
		$themify->hide_image = 'yes' == $image? 'no' : 'yes';
		$themify->unlink_image = $unlink_image;
		$themify->hide_title = 'yes' == $title? 'no' : 'yes';
		$themify->width = $image_w;
		$themify->height = $image_h;
		$themify->image_setting = 'ignore=true&';
		$themify->unlink_title = $unlink_title;
		$themify->display_content = $display;
		$themify->hide_date = 'yes' == $post_date? 'no' : 'yes';
		$themify->hide_meta = 'yes' == $post_meta? 'no' : 'yes';
		$themify->is_shortcode = true;
		$themify->image_size = $image_size;

		$postsliderstr .= themify_get_shortcode_template( $posts, 'includes/loop', $post_type, array(
			'before_post' => '<li>',
			'after_post' => '</li>'
		) );

		// revert to original $themify state
		$themify = clone $themify_save;

		$postsliderstr .= '</ul>';
		$postsliderstr .= '</div><!-- /shortcode post_slider -->';
	} //$posts

	return $postsliderstr;
}


/**
 * Creates an author box to display your profile
 * @param Object $atts
 * @param String $content
 * @return String
 */
function themify_shortcode_author_box( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'avatar' => 'yes',
		'avatar_size' => '48',
		'color' => '',
		'icon' => '',
		'style' => '',
		'author_link' => 'no'
	), $atts, 'themify_author_box' ) );
	$style = join( ' ', array( $color, $icon, $style ) );

	/** 
	 * Filtered name of author
	 * @var String */
	$nicename = get_the_author_meta( 'nicename' );
	$authorboxstr = '<!-- shortcode author_box --> <div class="shortcode clearfix author-box ' . esc_attr( $style . ' ' . $nicename ) . ' ">';
	if ( 'yes' == $avatar ) {
		$authorboxstr .= '<p class="author-avatar">' . get_avatar( get_the_author_meta( 'user_email' ), $avatar_size, '' ) . '</p>';
	}
	if ( get_the_author_meta( 'user_url' ) ) {
		$authorboxstr .= '<div class="author-bio">
			<h4 class="author-name"><a href="' . esc_url( get_the_author_meta( 'user_url' ) ) . '">' . get_the_author_meta( 'display_name' ) . '</a></h4>
		' . get_the_author_meta( 'description' );
	} else {
		$authorboxstr .= '<div class="author-bio">
		<h4 class="author-name">' . get_the_author_meta( 'display_name' ) . '</h4>
	' . get_the_author_meta( 'description' );
	}
	if ( 'yes' == $author_link ) {
                $url = esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) );
		if ( $url ) {
			$authorboxstr .= '<p class="author-link"><a href="' . $url . '">&rarr; ' . get_the_author_meta( 'display_name' ) . ' </a></p>';
		} else {
			$authorboxstr .= '<p class="author-link">&rarr; ' . get_the_author_meta( 'display_name' ) . ' </p>';
		}
	}
	$authorboxstr .= '</div>
	</div> <!-- /shortcode author_box -->';
	return $authorboxstr;
}

/**
 * Creates a box to enclose content
 * @param Object $atts
 * @param String $content
 * @return String
 */
function themify_shortcode_box( $atts, $content = null ) {
	extract( shortcode_atts( array(
		'color' => '',
		'icon' => '',
		'style' => '',
	), $atts, 'themify_box' ) );
	$style = join( ' ', array( $color, $icon, $style ) );

	$boxstr = '
	<!-- shortcode box --> <div class="shortcode clearfix box ' . esc_attr( $style ) . '">'
		. do_shortcode( preg_replace( array( '#<p>\s*+(<br\s*/*>)?\s*</p>#i', '~\s?<p>(\s|&nbsp;)+</p>\s?~' ), '', force_balance_tags( $content ) ) )
	. '</div> <!-- /shortcode box -->';

	return $boxstr;
}

/**
 * Remove paragraphs wrapping shortcodes
 *
 * @param string $content
 *
 * @since 1.9.4
 *
 * @return string
 */
function themify_fix_shortcode_empty_paragraph( $content ) {
	$block = join( '|', array_keys( themify_shortcode_list() ) ) . '|themify_' . join( '|themify_', array_keys( themify_shortcode_list() ) );
	return preg_replace( array( "/(<p>)?\[($block)(\s[^\]]+)?\](<\/p>|<br \/>)?/", "/(<p>)?\[\/($block)](<\/p>|<br \/>)?/" ), array( '[$2$3]', '[/$2]' ), $content );
}

/**
 * Display tweets by user
 * @param array $atts
 * @param String $content
 * @return String
 */
function themify_shortcode_twitter( $atts, $content = null ) {
	global $themify_twitter_instance;
	$themify_twitter_instance++;

	extract( shortcode_atts( array(
		'username' => '',
		'type' => '',
		'timeline_height' => 400,
		'timeline_width' => 300,
		'show_count' => 5,
		'show_timestamp' => 'true',
		'hide_footer' => false,
		'show_follow' => 'false',
		'embed_code' => '',
		'follow_text' => __('&rarr; Follow me', 'themify'),
		'include_retweets' => 'false',
		'exclude_replies' => 'false',
		'is_widget' => 'false',
		'widget_id' => ''
	), $atts, 'themify_twitter' ) );
	
	$is_shortcode = '';
	$transient_id = $themify_twitter_instance . '_' . get_the_ID();
	if ( 'false' == $is_widget ) {
		$is_shortcode = 'shortcode';
	}

	if ( 'true' == $is_widget ) {
		$transient_id = $widget_id;
	}
	
	if( $type ) {
		if( $type == 'type-timeline') {
			$screen_name = sanitize_user( strip_tags( $username ) );
			$data_chrome = '';
			if( $hide_footer ) {
				$data_chrome = 'data-chrome="nofooter"';
			}
			
			$show_replies = false; 
			if( $exclude_replies == 'false' ) {
				$show_replies = true;
			}
			
			$out = "<a class='twitter-timeline' {$data_chrome} data-show-replies='$show_replies' data-height='$timeline_height' data-width='$timeline_width'
	  					href='https://twitter.com/{$screen_name}'>
							Tweets by @{$screen_name}
					</a>
					<script async src='//platform.twitter.com/widgets.js' charset='utf-8'></script>";
			
			return $out;
		}
		
		return $embed_code;
	}

	$args = array(
		'username' => sanitize_user( strip_tags( $username ) ),
		'limit' => intval( $show_count ),
		'include_retweets' => $include_retweets,
		'exclude_replies' => $exclude_replies
	);

	$tweets = themify_twitter_get_data( $transient_id, $args );
	if( is_array( $tweets ) && isset( $tweets['error_message'] ) ) {
		return $tweets['error_message'];
	}

	$out = '<div class="twitter-list '.$is_shortcode.'">
			<div id="twitter-block-'.$themify_twitter_instance.'">';

	if ( is_array( $tweets ) && count( $tweets ) > 0 ) {
		$out .= '<ul class="twitter-list">';

		foreach( $tweets as $tweet ) {
			$text = $tweet->text;
			foreach ( $tweet->entities as $type => $entity ) {
				if( 'urls' == $type ) {
					foreach($entity as $j => $url) {
						$update_with = '<a href="' . esc_url( $url->url ) . '" target="_blank" title="' . esc_attr( $url->expanded_url ) . '" class="twitter-user">' . $url->display_url . '</a>';
						$text = str_replace($url->url, $update_with, $text);
					}
				} elseif ( 'hashtags' == $type ) {
					foreach($entity as $j => $hashtag) {
						$update_with = '<a href="' . esc_url( '//twitter.com/search?q=%23' . $hashtag->text . '&src=hash' ) . '" target="_blank" title="' . esc_attr( $hashtag->text ) . '" class="twitter-user">#' . $hashtag->text . '</a>';
						$text = str_replace('#'.$hashtag->text, $update_with, $text);
					}
				} elseif ( 'user_mentions' == $type ) {
					foreach($entity as $j => $user) {
						$user->screen_name = str_replace( '@', '', $user->screen_name );
						$update_with = '<a href="' . esc_url( '//twitter.com/' . $user->screen_name ) . '" target="_blank" title="' . esc_attr( $user->name ) . '" class="twitter-user">@' . $user->screen_name . '</a>';
						$text = str_replace('@'.$user->screen_name, $update_with, $text);
					}
				} elseif ( 'media' == $type ) {
					foreach ( $entity as $j => $media ) {
						$update_with = '<a href="' . esc_url( $media->url ) . '" target="_blank" title="' . esc_attr( $media->expanded_url ) . '" class="twitter-media">' . $media->display_url . '</a>';
						$text = str_replace( $media->url, $update_with, $text );
					}
				}
			}
			$out .= '<li class="twitter-item">'.$text;
			if ( 'false' != $show_timestamp ) {
				// hour ago time format
				$time = sprintf( __('%s ago', 'themify'), human_time_diff( strtotime( $tweet->created_at ) ) );
				$out .= '<br /><em class="twitter-timestamp"><small>' . wp_kses_post( $time ) . '</small></em>';
			}
			$out .= '</li>';
		}
		$out .= '</ul>';
	}
	$out .= '</div>';
	if ( 'false' != $show_follow ) {
		$out .= '<div class="follow-user"><a href="' . esc_url( '//twitter.com/' . $username ) . '">' . $follow_text . '</a></div>';
	}

	$out .= '</div>';

	return $out;
}

/**
 * Get twitter data store from cache
 * @param $transient_id
 * @param $args
 * @return array|mixed
 */
function themify_twitter_get_data ( $transient_id, $args ) {
	$data = array();
	$transient_key = $transient_id . '_themify_tweets';

	$transient = get_transient( $transient_key );
	
	if ( false === $transient ) {
		$response = themify_request_tweets( $args );

		if ( ! is_wp_error( $response ) && is_array( $response ) && ( isset( $response[0]->user->id ) || isset( $response['error_message'] ) ) ) {
			$data = $response;
			set_transient( $transient_key, $data, 10 * 60 ); // 10 min cache
		}
	} else {
		$data = $transient;
	}

	return $data;
}

/**
 * Get request tweets from service api
 * @param $args
 * @return bool|object
 */
function themify_request_tweets($args) {
	$screen_name = urlencode(strip_tags( sanitize_user( str_replace( '@', '', $args['username'] ) ) ));
	$count = 0;

	if ( $args['limit'] != '' ) {
		$count = intval( $args['limit'] );
	}
	if ( $args['include_retweets'] == 'true' ) {
		$include_rts = '1';
	} else {
		$include_rts = '0';
	}
	$exclude_replies = $args['exclude_replies'];

	if ( ! class_exists( 'Wp_Twitter_Api' ) ) {
		// Require twitter oauth class
		require 'twitteroauth/class-wp-twitter-api.php';
	}
	$credentials = apply_filters( 'themify_twitter_credentials', array(
		'consumer_key' => '',
		'consumer_secret' => ''
	) );

	$query = 'screen_name='.$screen_name.'&count='.$count.'&include_rts='.$include_rts.'&exclude_replies='.$exclude_replies.'&include_entities=true';
	
	$twitterConnection = new Wp_Twitter_Api( $credentials );
	$tweets = $twitterConnection->query($query);
	
	return $tweets;
}

/**
 * Flush transient when post is saved.
 * @param $post_id
 */
function themify_twitter_flush_transient( $post_id ) {
	//verify post is not a revision
	if ( ! wp_is_post_revision( $post_id ) ) {
		// Count unprefixed and/or prefixed shortcode instances
		$post_content = '';
		if ( isset( $_POST['content'] ) ) {
			$post_content = $_POST['content'];
		}
		$unprefixed_shortcode = substr_count($post_content, '[twitter');
		$prefixed_shortcode = substr_count($post_content, '[themify-twitter');
		$shortcode_count = $unprefixed_shortcode + $prefixed_shortcode;
		if ( $shortcode_count > 0 ) {
			// delete transients
			for ($i=1; $i <= $shortcode_count; $i++) { 
				delete_transient( $i.'_'.$post_id.'_themify_tweets' );
			}
		}
	}
}

/**
 * Renders a font icon.
 *
 * @since 1.9.1
 *
 * @param array $atts
 * @param null $content
 * @return string
 */
function themify_shortcode_icon( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'icon'       => '',
		'label'      => '',
		'link'       => '',
		'style'      => '',
		'icon_bg'    => '',
		'icon_color' => '',
		'target'     => '',
	), $atts );

	$atts['icon'] = themify_get_icon( $atts['icon'] );

	// Set front and background colors.
	$colors = '';
	$style_attr = '';
	if ( ! empty( $atts['icon_bg'] ) ) {
		$colors .= "background-color: {$atts['icon_bg']};";
	}
	if ( ! empty( $atts['icon_color'] ) ) {
		$colors .= "color: {$atts['icon_color']};";
	}
	if ( ! empty( $colors ) ) {
		$style_attr = 'style="' . esc_attr( $colors ) . '"';
	}

	// Begin building markup for icon.
	$out = '';

	// Build icon
	if ( ! empty( $atts['icon'] ) ) {
		$out .= '<i class="themify-icon-icon ' . esc_attr( $atts['icon'] ) . '" ' . $style_attr . '></i>';
	}

	// Build label
	if ( ! empty( $atts['label'] ) ) {
		$out .= '<span class="themify-icon-label">' . $atts['label'] . '</span>';
	}

	// Sanitize link
	$link = $atts['link'];
	if ( '' != $link && '' != $out ) {
		$target = !empty( $atts['target'] )? ' target="' . esc_attr( $atts['target'] ) . '"' : '';
		$out = '<a href="' . esc_url( $link ) . '" class="themify-icon-link"' . $target . '>' . $out . '</a>';
	}

	return '<span class="shortcode themify-icon ' . esc_attr( $atts['style'] ) . '">' . $out . '</span>';
}

/**
 * Themify List shortcode
 *
 * @since 2.8.9
 */
function themify_shortcode_icon_list( $atts, $content = null ) {
	// the list shortcode has the same parameters as [themify_icon]
	$style = isset( $atts['style'] ) ? $atts['style'] : '';
	unset( $atts['style'] );
	$out = str_ireplace( "</li>", ( themify_shortcode_icon( $atts ) . "</li>" ), $content );
	return '<div class="shortcode themify-list '. $style . '">' . do_shortcode( $out ) . '</div>';
}