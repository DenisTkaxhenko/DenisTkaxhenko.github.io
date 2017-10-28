<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly
/**
 * Template Gallery Grid
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */
extract( $settings, EXTR_SKIP );

$i = 0;
$pagination = $settings['gallery_pagination'] && $settings['gallery_per_page'] > 0;
if ( $pagination ) {
	$total = count( $gallery_images );
	if ( $total <= $settings['gallery_per_page'] ) {
		$pagination = false;
	} else {
		$current = isset( $_GET['builder_gallery'] ) ? $_GET['builder_gallery'] : 1;
		$offset = $settings['gallery_per_page'] * ( $current - 1 );
		$gallery_images = array_slice( $gallery_images, $offset, $settings['gallery_per_page'], true );
	}
}
foreach ( $gallery_images as $image ) :
	$alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
	$caption = ! empty( $image->post_excerpt ) ? $image->post_excerpt : $alt;
	$title = $image->post_title;
	?>
	
	<dl class="gallery-item">
		<dt class="gallery-icon">
		<?php
		if ( $link_opt == 'file' ) {
			$link = wp_get_attachment_image_src( $image->ID, $link_image_size );
			$link = $link[0];
		} elseif ( 'none' == $link_opt ) {
			$link = '';
		} else {
			$link = get_attachment_link( $image->ID );
		}
		$link_before = '' != $link ? sprintf( '<a title="%s" href="%s">', esc_attr( $caption ), esc_url( $link ) ) : '';
		$link_before = apply_filters( 'themify_builder_image_link_before', $link_before, $image, $settings );
		$link_after = '' != $link ? '</a>' : '';
		if ( $this->is_img_php_disabled() ) {
			$img = wp_get_attachment_image( $image->ID, $image_size_gallery );
		} else {
			$img = wp_get_attachment_image_src( $image->ID, 'large' );
			$img = themify_get_image( "ignore=true&src={$img[0]}&w={$thumb_w_gallery}&h={$thumb_h_gallery}" );
		}

		echo ! empty( $img ) ? $link_before . $img . $link_after : '';
		?>
		</dt>
		<dd<?php if( ( $gallery_image_title === 'library' && ! empty( $title ) ) || ( $gallery_exclude_caption != 'yes' && ! empty( $caption ) ) ) : ?> class="wp-caption-text gallery-caption"<?php endif; ?>>
			<?php if ( $gallery_image_title === 'library' && ! empty( $title ) ) : ?>
				<strong class="themify_image_title"><?php echo $title ?></strong>
			<?php endif; ?>
			<?php if ( $gallery_exclude_caption != 'yes' && ! empty( $caption ) ) : ?>
				<span class="themify_image_caption"><?php echo $caption ?></span>
			<?php endif; ?>
		</dd>
	</dl>

	<?php if ( $columns > 0 && ++$i % $columns == 0 ) : ?>
		<br style="clear: both" />
	<?php endif; ?>

<?php endforeach; // end loop  ?>
<br style="clear: both" />
<?php if ( $pagination ) : ?>
	<div class="builder_gallery_nav" data->
		<?php

		/**
		 * fix paginate_links url in modules loaded by Ajax request: the url does not match the actual page url.
		 * #5345
		 * @note: paginate_links seems buggy with successive requests, the url parameters get jumbled up;
		 * hence the remove_query_args to remove the parameter from url before it's added in by paginate_links.
		 */
		$key = defined( 'DOING_AJAX' ) && DOING_AJAX ? 'HTTP_REFERER' : 'REQUEST_URI';
		$base = remove_query_arg( 'builder_gallery', $_SERVER[ $key ] );

		echo paginate_links( array(
			'base' => $base . '%_%',
			'current' => $current,
			'total' => ceil( $total / $settings['gallery_per_page'] ),
			'format' => '?builder_gallery=%#%'
		) );
		?>
	</div>
<?php endif; ?>