<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Template Gallery Lightboxed
 * 
 * Access original fields: $mod_settings
 * @author Themify
 */

extract( $settings, EXTR_SKIP );

$alt = isset( $gallery_images[0]->post_excerpt ) ? $gallery_images[0]->post_excerpt : '';

/* if no thumbnail is set for the gallery, use the first image */
if( ! isset( $thumbnail_gallery ) ) {
	$thumbnail_gallery = wp_get_attachment_url( $gallery_images[0]->ID );
}
$thumbnail = themify_get_image( "ignore=true&src={$thumbnail_gallery}&w={$thumb_w_gallery}&h={$thumb_h_gallery}&alt={$alt}" );

foreach ( $gallery_images as $key => $image ): ?>
        <dl class="gallery-item" style="<?php echo 0 == $key ? '' : 'display: none;'; ?>">
                <?php
                $link = wp_get_attachment_url( $image->ID );

                $img = wp_get_attachment_image_src( $image->ID, 'full' );

                $alt = get_post_meta( $image->ID, '_wp_attachment_image_alt', true );
                $title = $image->post_title;
                $caption = $image->post_excerpt;

                if ( ! empty( $link ) ) {
                        echo '<dt class="gallery-icon"><a href="' . esc_url( $link ) . '" title="' . esc_attr(  $title ) . '">';
                }
                echo wp_kses_post( ( 0 == $key ) ? $thumbnail : $img[1] );
                if ( ! empty( $link ) ) {
                        echo '</a></dt>';
                } ?>

                <dd<?php if( $gallery_image_title==='library' && !empty( $title ) ):?> class="wp-caption-text gallery-caption"<?php endif;?>>
                    <?php if($gallery_image_title==='library' && !empty( $title ) ):?>
                        <strong class="themify_image_title"><?php echo $title; ?></strong>
                    <?php endif;?>
					<?php if( $gallery_exclude_caption!='yes' && !empty( $caption ) ): ?>
						<span class="themify_image_caption"><?php echo esc_attr( $caption ); ?></span>
					<?php endif ?>
                </dd>
        </dl>

<?php endforeach; // end loop ?>
