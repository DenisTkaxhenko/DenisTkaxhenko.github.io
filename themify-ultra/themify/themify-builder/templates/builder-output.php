<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div id="themify_builder_content-<?php echo esc_attr( $builder_id ); ?>" data-postid="<?php echo esc_attr( $builder_id ); ?>" class="themify_builder_content themify_builder_content-<?php echo esc_attr( $builder_id ); ?> themify_builder themify_builder_front">

	<?php
		foreach ( $builder_output as $key => $row ) {
			if ( 0 == count( $row ) ) continue;
			if( isset( $row['row_order'] ) ) $row['row_order'] = $key; // Fix issue with import content has same row_order number
			$this->get_template_row( $key, $row, $builder_id, true );
		} // end row loop
	?>
</div>
<!-- /themify_builder_content -->