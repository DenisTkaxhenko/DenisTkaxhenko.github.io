<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Gallery Showcase
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

extract( $settings, EXTR_SKIP );

if ( ! empty( $gallery_images ) ) :
	$first_image = '';
	$disable =  $this->is_img_php_disabled();
	if ( is_array( $gallery_images ) ) {
		if ( is_object( $gallery_images[0] ) ) {
			$alt = get_post_meta( $gallery_images[0]->ID, '_wp_attachment_image_alt', true );
			$caption = $gallery_images[0]->post_excerpt;
			$title = $gallery_images[0]->post_title;
			if( $disable ) {
				$first_image = wp_get_attachment_image_src( $gallery_images[0]->ID, $s_image_size_gallery );
				$first_image = $first_image[0];
			} else {
				$first_image = themify_do_img( $gallery_images[0]->ID, $s_image_w_gallery, $s_image_h_gallery );
				$first_image = $first_image['url'];
			}
		}
	}
	?>

	<div class="gallery-showcase-image">
		<div class="image-wrapper">
		   <img src="<?php echo esc_url( $first_image ); ?>" alt="<?php echo esc_attr($alt)?>" />
			<div class="gallery-showcase-title">
				<h3 id="gallery-showcase-title"><?php echo esc_attr( $title ); ?></h3>
				<h4 id="gallery-showcase-caption"><?php echo esc_attr( $caption ); ?></h4>
			</div>
		</div>

	</div>

	<div class="gallery-images">

		<?php
		$i = 0;
		foreach ( $gallery_images as $image ) :
			$alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
			$title = $image->post_title;
			$caption = $image->post_excerpt;

			if( $disable ) {
				$img = wp_get_attachment_image( $image->ID, $image_size_gallery );
				$link = wp_get_attachment_image_src( $image->ID, $s_image_size_gallery );
				$link = $link[0];
			} else {
				$img = themify_do_img( $image->ID, $thumb_w_gallery, $thumb_h_gallery );
				$img = "<img src='{$img['url']}' width='{$img['width']}' height='{$img['height']}' alt='{$alt}' />";
				$link = themify_do_img( $image->ID, $s_image_w_gallery, $s_image_h_gallery );
				$link = $link['url'];
			}

			if ( ! empty( $link ) ) {
				echo '<a data-image="' . esc_url( $link ) . '" title="' . esc_attr( $title ) . '" data-caption="' . esc_attr( $caption ) . '" href="#">';
			}
			echo wp_kses_post( $img );
			if ( ! empty( $link ) ) {
				echo '</a>';
			}

		endforeach; // end loop ?>
	</div>

<?php endif; ?>