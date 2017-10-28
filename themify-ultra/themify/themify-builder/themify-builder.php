<?php
/**
 * Framework Name: Themify Builder
 * Framework URI: http://themify.me/
 * Description: Page Builder with interactive drag and drop features
 * Version: 1.0
 * Author: Themify
 * Author URI: http://themify.me
 *
 *
 * @package ThemifyBuilder
 * @category Core
 * @author Themify
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Define builder constant
 */
define( 'THEMIFY_BUILDER_DIR', dirname(__FILE__) );
define( 'THEMIFY_BUILDER_MODULES_DIR', THEMIFY_BUILDER_DIR . '/modules' );
define( 'THEMIFY_BUILDER_TEMPLATES_DIR', THEMIFY_BUILDER_DIR . '/templates' );
define( 'THEMIFY_BUILDER_CLASSES_DIR', THEMIFY_BUILDER_DIR . '/classes' );
define( 'THEMIFY_BUILDER_INCLUDES_DIR', THEMIFY_BUILDER_DIR . '/includes' );
define( 'THEMIFY_BUILDER_LIBRARIES_DIR', THEMIFY_BUILDER_INCLUDES_DIR . '/libraries' );

require_once( THEMIFY_BUILDER_DIR . '/themify-builder-functions.php' );

// URI Constant
define( 'THEMIFY_BUILDER_URI', THEMIFY_URI . '/themify-builder' );

/**
 * Include builder class
 */
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-model.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/premium/class-themify-builder-include.php');
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/premium/class-themify-builder-layouts.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-module.php' );
require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder.php' );

/**
 * Init themify builder class
 */
add_action( 'after_setup_theme', 'themify_builder_init', 15 );
function themify_builder_init() {
	global $ThemifyBuilder, $Themify_Builder_Layouts;
	if ( class_exists( 'Themify_Builder') ) {

		do_action( 'themify_builder_before_init' );

		if ( Themify_Builder_Model::builder_check() ) {
			$Themify_Builder_Layouts = new Themify_Builder_Layouts();

			$ThemifyBuilder = new Themify_Builder();
			$ThemifyBuilder->init();
		}
	} // class_exists check

	if( is_admin() ) {
		if( current_user_can( 'update_plugins' ) ) {
			include THEMIFY_BUILDER_DIR . '/themify-builder-updater.php';
		}
	}
}

if ( ! function_exists('themify_builder_edit_module_panel') ) {
	/**
	 * Hook edit module frontend panel
	 * @param $mod_name
	 * @param $mod_settings
	 */
	function themify_builder_edit_module_panel( $mod_name, $mod_settings ) {
		do_action( 'themify_builder_edit_module_panel', $mod_name, $mod_settings );
	}
}

if(!function_exists('themify_manage_builder')) {
	/**
	 * Builder Settings
	 * @param array $data
	 * @return string
	 * @since 1.2.7
	 */
	function themify_manage_builder($data=array()) {
		global $ThemifyBuilder;
		$data = themify_get_data();
		$pre = 'setting-page_builder_';
		$output = '';
		$modules = $ThemifyBuilder->get_modules( 'all' );

		foreach ($modules as $m) {
			$exclude = $pre.'exc_'.$m['id'];
			$checked = isset($data[$exclude]) && $data[$exclude] ? 'checked="checked"' : '';
			$output .= '<p>
						<span><input id="'.esc_attr( 'builder_module_'.$m['id'] ).'" type="checkbox" name="'.esc_attr( $exclude ).'" value="1" '.$checked.'/> <label for="'.esc_attr( 'builder_module_'.$m['id'] ).'">' . wp_kses_post( sprintf(__('Exclude %s module', 'themify'), $m['name'] ) ) . '</label></span>
					</p>';	
		}
		
		return $output;
	}
}

if(!function_exists('themify_manage_builder_active')) {
	/**
	 * Builder Settings
	 * @param array $data
	 * @return string
	 * @since 1.2.7
	 */
	function themify_manage_builder_active($data=array()) {
		$pre = 'setting-page_builder_';
		$output = '';
		$options = array(
			array('name' => __('Enable', 'themify'), 'value' => 'enable'),
			array('name' => __('Disable', 'themify'), 'value' =>'disable')
		);

		$output .= sprintf('<p><span class="label">%s</span><select id="%s" name="%s">%s</select>%s</p>',
			esc_html__( 'Themify Builder:', 'themify' ),
			esc_attr( $pre . 'is_active' ),
			esc_attr( $pre . 'is_active' ),
			themify_options_module( $options, $pre . 'is_active' ),
			sprintf( '<small class="pushlabel" data-show-if-element="[name=setting-page_builder_is_active]" data-show-if-value="disable">%s</small>'
			, esc_html__( 'WARNING: When Builder is disabled, all Builder content/layout will not appear. They will re-appear once Builder is enabled.', 'themify' ) )
		);

		if ( 'disable' != themify_get( $pre . 'is_active' ) ) {
			
			$output .= sprintf( '<p><label for="%s"><input type="checkbox" id="%s" name="%s"%s> %s</label></p>',
				esc_attr( $pre . 'disable_shortcuts' ),
				esc_attr( $pre . 'disable_shortcuts' ),
				esc_attr( $pre . 'disable_shortcuts' ),
				checked( 'on', themify_get( $pre . 'disable_shortcuts' ), false ),
				wp_kses_post( __( 'Disable keyboard shortcuts like Cmd+S = Save', 'themify') )
			);
                       
		}

		return $output;
	}
}

if(!function_exists('themify_manage_builder_animation')) {
	/**
	 * Builder Setting Animations
	 * @param array $data
	 * @return string
	 * @since 2.0.0
	 */
	function themify_manage_builder_animation($data=array()) {
		$opt_data = themify_get_data();
		$pre = 'setting-page_builder_animation_';
		$options = array(
			array( 'name' => '', 'value' => '' ),
			array( 'name' => esc_html__( 'Disable on mobile & tablet', 'themify' ), 'value' =>'mobile' ),
			array( 'name' => esc_html__( 'Disable on all devices', 'themify' ), 'value' =>'all' )
		);

		$output = '';
		$output .= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>',
			esc_attr( $pre . 'appearance' ),
			esc_html__( 'Appearance Animation', 'themify' ),
			esc_attr( $pre . 'appearance' ),
			esc_attr( $pre . 'appearance' ),
			themify_options_module( $options, $pre . 'appearance' )
		);
		$output .= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>',
			esc_attr( $pre . 'parallax_bg' ),
			esc_html__( 'Parallax Background', 'themify' ),
			esc_attr( $pre . 'parallax_bg' ),
			esc_attr( $pre . 'parallax_bg' ),
			themify_options_module( $options, $pre . 'parallax_bg' )
		);
		$output .= sprintf('<p><label for="%s" class="label">%s</label><select id="%s" name="%s">%s</select></p>',
			esc_attr( $pre . 'parallax_scroll' ),
			esc_html__( 'Parallax Scrolling', 'themify' ),
			esc_attr( $pre . 'parallax_scroll' ),
			esc_attr( $pre . 'parallax_scroll' ),
			themify_options_module( $options, $pre . 'parallax_scroll', true, 'mobile' )
		);
		$output .= sprintf( '<span class="pushlabel"><small>%s</small></span>', 
			esc_html__( 'If animation is disabled, the element will appear static', 'themify' )
		);

		return $output;
	}
}
/**
 * Add Builder to all themes using the themify_theme_config_setup filter.
 * @param $themify_theme_config
 * @return mixed
 * @since 1.4.2
 */
function themify_framework_theme_config_add_builder($themify_theme_config) {
	$themify_theme_config['panel']['settings']['tab']['page_builder'] = array(
		'title' => __('Themify Builder', 'themify'),
		'id' => 'themify-builder',
		'custom-module' => array(
			array(
				'title' => __('Themify Builder Options', 'themify'),
				'function' => 'themify_manage_builder_active'
			),
		)
	);
	if ( 'disable' != apply_filters( 'themify_enable_builder', themify_get('setting-page_builder_is_active') ) ) {
		$themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
			'title' => __('Animation Effects', 'themify'),
			'function' => 'themify_manage_builder_animation'
		);

		$themify_theme_config['panel']['settings']['tab']['page_builder']['custom-module'][] = array(
			'title' => __('Exclude Builder Modules', 'themify'),
			'function' => 'themify_manage_builder'
		);
	}
	return $themify_theme_config;
};
add_filter('themify_theme_config_setup', 'themify_framework_theme_config_add_builder');

if ( ! function_exists( 'themify_builder_grid_lists' ) ) {
	/**
	 * Get Grid menu list
	 */
	function themify_builder_grid_lists( $handle = 'row', $set_gutter = null, $column_alignment_value = '', $row_anchor = '' ) {
		$grid_lists = Themify_Builder_Model::get_grid_settings();
		$gutters = Themify_Builder_Model::get_grid_settings( 'gutter' );
		$column_alignment = Themify_Builder_Model::get_grid_settings( 'column_alignment' );
                $column_direction = Themify_Builder_Model::get_grid_settings( 'column_dir' );
		$selected_gutter = is_null( $set_gutter ) ? '' : $set_gutter; 
                $handle = esc_attr($handle);
                ?>
		<div class="grid_menu" data-handle="<?php echo $handle; ?>">
			<div class="grid_icon ti-layout-column3"><span class="row-anchor-name"><?php echo esc_attr( $row_anchor ); ?></span></div>
			<div class="themify_builder_grid_list_wrapper">
                                <ul class="grid_tabs<?php echo !Themify_Builder_Model::is_premium()?' themify_builder_lite':''?>">
                                    <li><a data-handle="<?php echo $handle; ?>" href="#desktop"><?php _e('DESKTOP','themify')?></a></li>
                                    <li><a data-handle="<?php echo $handle; ?>" href="#tablet"><?php _e('TABLET','themify')?></a></li>
                                    <li><a data-handle="<?php echo $handle; ?>" href="#mobile"><?php _e('MOBILE','themify')?></a></li>
                                </ul>
                            <div class="themify_builder_grid_tab themify_builder_grid_desktop">
				<ul class="themify_builder_grid_list clearfix">
					<?php foreach( $grid_lists as $row ): ?>
					<li>
						<ul>
                                                    <?php foreach( $row as $li ): ?>
                                                            <li><a href="#" class="themify_builder_column_select <?php echo esc_attr( 'grid-layout-' . implode( '-', $li['data'] ) ); ?>" data-type="desktop" data-handle="<?php echo $handle; ?>"   data-col="<?php echo $li['col']; ?>"  data-grid="<?php echo esc_attr( json_encode( $li['data'] ) ); ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
                                                    <?php endforeach; ?>
						</ul>
					</li>
					<?php endforeach; ?>
				</ul>

				<ul class="themify_builder_column_alignment clearfix">
					<?php foreach( $column_alignment as $li ): ?>
						<li <?php if ( $column_alignment_value === $li['alignment'] || ( $column_alignment_value == '' && $li['alignment'] === 'col_align_top' ) ) echo ' class="selected"' ?>><a href="#" class="themify_builder_column_select column-alignment-<?php echo esc_attr( $li['alignment'] ); ?>" data-handle="<?php echo $handle; ?>" data-alignment="<?php echo esc_attr( $li['alignment'] ); ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
					<?php endforeach; ?>

					<li><?php _e( 'Column Alignment', 'themify' ) ?></li>
				</ul>
                                <ul class="themify_builder_column_direction clearfix">
					<?php foreach( $column_direction as $li ): ?>
						<li<?php if ( $li['dir'] === 'ltr' )  echo ' class="selected"' ?>><a href="#" class="themify_builder_dir_select column-dir-<?php echo $li['dir']; ?>" data-type="desktop" data-handle="<?php echo $handle; ?>" data-dir="<?php echo $li['dir']; ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
					<?php endforeach; ?>
					<li><?php _e( 'Column Direction', 'themify' ) ?></li>
				</ul>
                                <div class="themify_builder_column_gutter clearfix">
                                    <select class="gutter_select" data-handle="<?php echo esc_attr( $handle ); ?>">
                                            <?php foreach( $gutters as $gutter ): ?>
                                            <option value="<?php echo esc_attr( $gutter['value'] ); ?>"<?php selected( $selected_gutter, $gutter['value'] ); ?>><?php echo esc_html( $gutter['name'] ); ?></option>
                                            <?php endforeach; ?>
                                    </select>
                                    <span><?php _e('Gutter Spacing', 'themify') ?></span>
                                </div>
                            </div>
                            <div class="themify_builder_grid_tab themify_builder_grid_tablet">
                                <ul class="themify_builder_grid_list clearfix">
                                        <?php foreach( $grid_lists as $k=>$row ): ?>
                                            <li>
                                                    <ul>
                                                        <?php if($k===0):?>
                                                            <li><a href="#" class="themify_builder_column_select tablet-auto" data-type="tablet" data-handle="<?php echo $handle; ?>"   data-col="1"  data-grid='["-auto"]'><img src="<?php echo THEMIFY_BUILDER_URI?>/img/builder/auto.png"></a></li>
                                                        <?php endif;?>
                                                        <?php foreach( $row as $li ): ?>
                                                            <?php if(empty($li['hide'])):?>
                                                                <?php 
                                                                    $data = array_unique($li['data']);
                                                                    if(count($data)===1){
                                                                        $li['data'] = $data;
                                                                    }
                                                                ?>
                                                                <li><a href="#" class="themify_builder_column_select <?php echo esc_attr( 'tablet' . implode( '-', $li['data'] ) ); ?>" data-type="tablet" data-handle="<?php echo $handle; ?>"   data-col="<?php echo $li['col']; ?>"  data-grid="<?php echo esc_attr( json_encode( $li['data'] ) ); ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
                                                            <?php endif;?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                            </li>
					<?php endforeach; ?>
				</ul>
                                <ul class="themify_builder_column_direction clearfix">
                                    <?php foreach( $column_direction as $li ): ?>
                                            <li<?php if ( $li['dir'] === 'ltr' )  echo ' class="selected"' ?>><a href="#" class="themify_builder_dir_select column-dir-<?php echo $li['dir']; ?>" data-type="tablet" data-handle="<?php echo $handle; ?>" data-dir="<?php echo $li['dir']; ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
                                    <?php endforeach; ?>
                                    <li><?php _e( 'Column Direction', 'themify' ) ?></li>
				</ul>
                            </div>
                            <div class="themify_builder_grid_tab themify_builder_grid_mobile">
                                <ul class="themify_builder_grid_list clearfix">
                                        <?php foreach( $grid_lists as $k=>$row ): ?>
                                            <li>
                                                    <ul>
                                                        <?php if($k===0):?>
                                                            <li><a href="#" class="themify_builder_column_select mobile-auto" data-type="mobile" data-handle="<?php echo $handle; ?>"   data-col="1"  data-grid='["-auto"]'><img src="<?php echo THEMIFY_BUILDER_URI?>/img/builder/auto.png"></a></li>
                                                        <?php endif;?>
                                                        <?php foreach( $row as $li ): ?>
                                                            <?php if(empty($li['hide'])):?>
                                                                <?php 
                                                                  
                                                                    $data = array_unique($li['data']);
                                                                    if(count($data)===1){
                                                                        $li['data'] = $data;
                                                                    }
                                                                ?>
                                                                <li><a href="#" class="themify_builder_column_select <?php echo esc_attr( 'mobile' . implode( '-', $li['data'] ) ); ?>" data-type="mobile" data-handle="<?php echo $handle; ?>"   data-col="<?php echo $li['col']; ?>"  data-grid="<?php echo esc_attr( json_encode( $li['data'] ) ); ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
                                                            <?php endif;?>
                                                        <?php endforeach; ?>
                                                    </ul>
                                            </li>
					<?php endforeach; ?>
				</ul>
                                <ul class="themify_builder_column_direction clearfix">
                                    <?php foreach( $column_direction as $li ): ?>
                                            <li<?php if ( $li['dir'] === 'ltr' )  echo ' class="selected"' ?>><a href="#" class="themify_builder_dir_select column-dir-<?php echo $li['dir']; ?>" data-type="mobile" data-handle="<?php echo $handle; ?>" data-dir="<?php echo $li['dir']; ?>"><img src="<?php echo esc_url( $li['img'] ); ?>"></a></li>
                                    <?php endforeach; ?>
                                    <li><?php _e( 'Column Direction', 'themify' ) ?></li>
				</ul>
                            </div>
			</div>
			<!-- /themify_builder_grid_list_wrapper -->
		</div>
		<!-- /grid_menu -->
		<?php
	}
}
