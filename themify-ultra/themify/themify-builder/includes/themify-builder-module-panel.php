<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Builder Frontend Panel HTML
 */

$helper_class = array();
if ( isset( $post ) && $post->post_status == 'auto-draft' ) $helper_class[] = 'tb_auto_draft';
$is_premium = Themify_Builder_Model::is_premium();
?>

<div id="tb_toolbar" class="tb_toolbar <?php echo implode(' ', $helper_class ); ?>">
	<a href="#" class="tb_toolbar_add_modules"><?php esc_html_e( 'Add modules', 'themify' );?></a>
	<div id="tb_module_panel">
		<div class="tb_module_panel_top_wrap">
			<div id="tb_module_panel_search">
				<input type="text" class="tb_module_panel_search_text">
			</div>
			<a href="#" class="tb_module_panel_lock"><i class="ti-lock"></i></a>
		</div>
		<!-- /tb_module_panel_top_wrap -->
		<ul class="tb_module_types">
			<li class="active"><a href="#" data-target="tb_module_panel_modules_wrap"><?php _e('Modules','themify')?></a></li>
			<li><a href="#" data-target="tb_module_panel_rows_wrap"><?php _e('Rows','themify')?></a></li>
		</ul>
		<div class="tb_module_panel_modules_wrap"></div>
		<!-- /tb_module_panel_modules_wrap -->
		
		<div class="tb_module_panel_rows_wrap">
		
			<div class="tb_row_cat_filter_wrap">
				<span class="tb_row_cat_filter_active"><?php _e('All','themify')?></span>
				<!-- /tb_row_cat_filter_active -->
				<ul class="tb_row_cat_filter">
					<li><a href="#"><?php _e('All','themify')?></a></li>
					<!-- Fetch Testimonials from themify server with JS -->
				</ul>
				<!-- /tb_row_cat_filter -->
			</div>
			<!-- /tb_row_cat_filter_wrap -->
			
			<div class="tb_predesigned_rows_list">
			
				<!-- Fetch Pre-Design Rows from themify server with JS -->
				
			</div>
			<!-- /tb_predesigned_rows_list -->
			
		</div>
		<!-- /tb_module_panel_rows_wrap -->
	</div>
	<!-- /tb_module_panel -->
	
	<ul class="tb_toolbar_menu">
		<li><a href="#" class="tb_tooltip js--themify_builder_breakpoint_switcher breakpoint-desktop tb_selected"><i class="ti-desktop"></i><span><?php esc_html_e( 'Desktop', 'themify' );?></span></a></li>
		<li <?php if(!$is_premium):?>class="themify_builder_lite"<?php endif;?>><a href="#" class="tb_tooltip js--themify_builder_breakpoint_switcher breakpoint-tablet-landscape"><i class="ti-tablet ti-tablet-landscape"></i><span><?php esc_html_e( 'Tablet Landscape', 'themify' );?></span></a></li>
		<li <?php if(!$is_premium):?>class="themify_builder_lite"<?php endif;?>><a href="#" class="tb_tooltip js--themify_builder_breakpoint_switcher breakpoint-tablet"><i class="ti-tablet"></i><span><?php esc_html_e( 'Tablet', 'themify' );?></span></a></li>
		<li <?php if(!$is_premium):?>class="themify_builder_lite"<?php endif;?>><a href="#" class="tb_tooltip js--themify_builder_breakpoint_switcher breakpoint-mobile"><i class="ti-mobile"></i><span><?php esc_html_e( 'Mobile', 'themify' );?></span></a></li>
		<li class="tb_toolbar_divider"></li>
		<li><a href="#" class="tb_tooltip js-themify-builder-undo-btn"><i class="ti-back-left"></i><span><?php esc_html_e( 'Undo (CTRL+Z)', 'themify' );?></span></a></li>
		<li><a href="#" class="tb_tooltip js-themify-builder-redo-btn"><i class="ti-back-right"></i><span><?php esc_html_e( 'Redo (CTRL+SHIFT+Z)', 'themify' );?></span></a></li>
		<li class="tb_toolbar_divider"></li>
		<li><a href="#"><i class="ti-import"></i></a>
			<ul>
				<li><a href="#" class="themify_builder_import_file"><?php esc_html_e( 'Import From File', 'themify' );?></a></li>
				<li><a href="#" class="themify_builder_import_page"><?php esc_html_e( 'Import From Page', 'themify' );?></a></li>
				<li><a href="#" class="themify_builder_import_post"><?php esc_html_e( 'Import From Post', 'themify' );?></a></li>
			</ul>
		</li>
		<li><a href="<?php echo wp_nonce_url('?themify_builder_export_file=true&postid=data.post_ID', 'themify_builder_export_nonce') ?>" class="tb_tooltip tb_export_link"><i class="ti-export"></i><span><?php esc_html_e( 'Export', 'themify' );?></span></a></li>
		<li class="tb_toolbar_divider"></li>
		<li><a href="#"><i class="ti-layout"></i></a>
			<ul>
				<li <?php if(!$is_premium):?>class="themify_builder_lite"<?php endif;?>><a href="#" class="themify_builder_load_layout"><?php esc_html_e( 'Load Layout', 'themify' );?></a></li>
				<li <?php if(!$is_premium):?>class="themify_builder_lite"<?php endif;?>><a href="#" class="themify_builder_save_layout"><?php esc_html_e( 'Save as Layout', 'themify' );?></a></li>
			</ul>
		</li>
		<li class="tb_toolbar_divider"></li>
		<li><a href="#" class="tb_tooltip themify_builder_dup_link"><i class="ti-layers"></i><span><?php esc_html_e( 'Duplicate this page', 'themify' );?></span></a></li>
		<li class="tb_toolbar_divider"></li>
		<li><a href="//themify.me/docs/builder" class="tb_tooltip" target="_blank"><i class="ti-help"></i><span><?php esc_html_e( 'Help', 'themify' );?></span></a></li>
	</ul>

	<div class="tb_toolbar_save_wrap">
		<?php if( ! empty( $_POST['post_id'] ) ): ?>
			<div class="tb_toolbar_backend_edit"><a href="<?php echo get_edit_post_link( $_POST['post_id'] ); ?>" id="themify_builder_switch_backend"><i class="ti-arrow-left"></i><?php esc_html_e( 'Edit in backend', 'themify' ); ?></a></div>
		<?php endif; ?>

		<div class="tb_toolbar_close">
			<a href="#"  class="tb_tooltip tb_toolbar_close_btn"><i class="ti-close"></i><span><?php esc_html_e( 'Close', 'themify' );?></span></a>
		</div>
		<!-- /tb_toolbar_close -->
		<div class="tb_toolbar_save_btn">
			<a href="#" class="tb_toolbar_save"><?php esc_html_e( 'Save', 'themify' );?></a>
			<div class="tb_toolbar_revision_btn">
				<span class="ti-angle-down"></span>
				<ul <?php if(!$is_premium):?>class="themify_builder_lite"<?php endif;?>>
					<li><a href="#" class="themify_builder_save_revision"><?php esc_html_e( 'Save as Revision', 'themify' );?></a></li>
					<li><a href="#" class="themify_builder_load_revision"><?php esc_html_e( 'Load Revision', 'themify' );?></a></li>
				</ul>
			</div>
		</div>
		<!-- /tb_toolbar_save_btn -->
	</div>
	<!-- /tb_toolbar_save_wrap -->

	<a href="#" id="themify_builder_switch_frontend" class="themify_builder_switch_frontend"><?php esc_html_e( 'Go to frontend', 'themify' ); ?></a>
	
</div>
<!-- /tb_toolbar -->