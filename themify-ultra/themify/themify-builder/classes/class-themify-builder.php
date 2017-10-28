<?php
if (!defined('ABSPATH'))
	exit; // Exit if accessed directly

if ( ! class_exists( 'Themify_Builder' ) ) :
/**
 * Main Themify Builder class
 * 
 * @package default
 */
class Themify_Builder {

	/**
	 * @var string
	 */
	public $meta_key;

	/**
	 * @var string
	 */
	public $meta_key_transient;

	/**
	 * @var array
	 */
	public $builder_settings = array();

	/**
	 * @var array
	 */
	public $module_settings = array();

	/**
	 * @var array
	 */
	public $registered_post_types = array();

	/**
	 * Define builder grid active or not
	 * @var bool
	 */
	private static $frontedit_active = false;

	/**
	 * Define load form
	 * @var string
	 */
	public $load_form = 'module';

	/**
	 * Directory Registry
	 */
	public $directory_registry = array();

	/**
	 * Array of classnames to add to post objects
	 */
	public $_post_classes = array();

	/**
	 * Get status of builder content whether inside builder content or not
	 */
	public $in_the_loop = false;

	/**
	 * The original post id
	 */
	public static $post_id = false;

	/**
	 * The layout_part_id
	 */
	public static $layout_part_id = false;

	/**
	 * Active custom post types registered by Builder.
	 *
	 * @var array
	 */
	public $builder_cpt = array();

	/**
	 * List of all public post types.
	 *
	 * @since 1.1.9
	 *
	 * @var array
	 */
	public $public_post_types = array();

	/**
	 * A list of posts which have been rendered by Builder
	 */
	private $post_ids = array();
	
	/**
	 * Selectors for preview styling.
	 */
	private $modules_styles = array();

	/**
	 *  Components Manager.
	 */
	public $components_manager;

	public $stylesheet;

	public $preview;

	/**
	 * Themify Builder Constructor
	 */
	public function __construct() {}

	/**
	 * Class Init
	 */
	public function init() {
		// Include required files
		$this->includes();
		$this->setup_default_directories();

		/* git #1862 */
		$this->builder_cpt_check();
				
                new Themify_Builder_Include($this);
				
		do_action('themify_builder_setup_modules', $this);

		// Init
		Themify_Builder_Model::load_general_metabox(); // setup metabox fields
		$this->load_modules(); // load builder modules
		// Builder write panel
		add_filter('themify_do_metaboxes', array($this, 'builder_write_panels'), 11);

		// Filtered post types
		add_filter('themify_post_types', array($this, 'extend_post_types'));
		add_filter('themify_builder_module_content', array('Themify_Builder_Model','format_text'));
                add_filter('themify_main_script_vars',array($this,'add_minify_vars'));
		add_filter('themify_builder_module_content', 'do_shortcode', 12 );
		/**
		 * WordPress 4.4 Responsive Images support */
		global $wp_version;
		if (version_compare($wp_version, '4.4', '>=')) {
			add_filter('themify_builder_module_content', 'wp_make_content_images_responsive');
			add_filter('themify_image_make_responsive_image', 'wp_make_content_images_responsive');
		}

		// Actions
		add_action('init', array($this, 'setup'), 10);
		add_action('themify_builder_metabox', array($this, 'add_builder_metabox'), 10);
		//add_action( 'media_buttons_context', array( $this, 'add_custom_switch_btn' ), 10 );
		add_action('admin_enqueue_scripts', array($this, 'load_admin_interface'), 10);

		// Asynchronous Loader
		add_action('wp_enqueue_scripts', array($this, 'register_frontend_js_css'), 9);
		add_action( 'template_redirect', array( $this, 'setup_frontend_editor' ) );
		if (Themify_Builder_Model::is_frontend_editor_page()) {
			add_action('wp_ajax_themify_builder_loader', array($this, 'async_load_builder'));
			add_action('themify_builder_frontend_load_builder_tmpl',array($this, 'async_load_builder_responsive'));
			add_action('wp_ajax_themify_builder_loader_tpl', array($this, 'async_load_builder_tpl'));
			
			// load module panel frontend
			// Frontend builder javascript tmpl load
			add_action('themify_builder_frontend_load_builder_tmpl', array($this, 'load_javascript_template_front'), 10);
		}

		// Ajax Actions
		add_action('wp_ajax_tfb_load_shortcode_preview', array($this, 'shortcode_preview'), 10);
		add_action('wp_ajax_builder_import', array($this, 'builder_import_ajaxify'), 10);
		add_action('wp_ajax_builder_import_submit', array($this, 'builder_import_submit_ajaxify'), 10);
		add_action('wp_ajax_builder_render_duplicate_row', array($this, 'render_duplicate_row_ajaxify'), 10);
		add_action('wp_ajax_tfb_imp_component_data_lightbox_options', array($this, 'imp_component_data_lightbox_options_ajaxify'), 10);
		add_action('wp_ajax_tfb_exp_component_data_lightbox_options', array($this, 'exp_component_data_lightbox_options_ajaxify'), 10);
		add_action('wp_ajax_themify_get_tax', array($this, 'themify_get_tax'),10);
		add_action('wp_ajax_themify_builder_get_tax_data', array($this, 'themify_builder_get_tax_data'),10);

		// Live styling
		add_action('wp_ajax_tfb_slider_live_styling', array($this, 'slider_live_styling'), 10);

		// WP_AJAX Live styling hooks (from addons/plugins).
		do_action('themify_builder_live_styling_ajax', $this);

		// Builder Save Data
		add_action('wp_ajax_tfb_save_data', array($this, 'save_data_builder'), 10);

		// Duplicate page / post action
		add_action('wp_ajax_tfb_duplicate_page', array($this, 'duplicate_page_ajaxify'), 10);

		// Hook to frontend
		add_action('wp_head', array($this, 'load_inline_js_script'), 10);
		add_filter('the_content', array($this, 'builder_show_on_front'), 11);
		add_action('wp_ajax_tfb_toggle_frontend', array($this, 'load_toggle_frontend_ajaxify'), 10);
		add_action('wp_ajax_tfb_load_module_partial', array($this, 'load_module_partial_ajaxify'), 10);
		add_action('wp_ajax_tfb_load_row_partial', array($this, 'load_row_partial_ajaxify'), 10);
		add_filter('body_class', array($this, 'body_class'), 10);

		// Shortcode
		add_shortcode('themify_builder_render_content', array($this, 'do_shortcode_builder_render_content'));

		// Plupload Action
		add_action('admin_enqueue_scripts', array($this, 'plupload_admin_head'), 10);
		// elioader
		//add_action( 'wp_head', array( $this, 'plupload_front_head' ), 10 );

		add_action('wp_ajax_themify_builder_plupload_action', array($this, 'builder_plupload'), 10);

		add_action('admin_bar_menu', array($this, 'builder_admin_bar_menu'), 100);

		// Frontend editor
		add_action('themify_builder_edit_module_panel', array($this, 'module_edit_panel_front'), 10, 2);

		// Switch to frontend
		add_action('admin_init', array($this, 'switch_frontend'));

		// WordPress Search
		add_filter('posts_where', array($this, 'do_search'), 10, 2);

		add_filter('post_class', array($this, 'filter_post_class'));

		// Add body class .ios7 for old version of IOS
		add_filter( 'body_class', array($this, 'check_for_old_ios') );

		// Add extra protocols like skype: to WordPress allowed protocols.
		if (!has_filter('kses_allowed_protocols', 'themify_allow_extra_protocols') && function_exists('themify_allow_extra_protocols')) {
			add_filter('kses_allowed_protocols', 'themify_allow_extra_protocols');
		}

		// Clear All builder caches in Themify Settings > Builder with ajax
		if (defined('DOING_AJAX')) {
			add_action('wp_ajax_tfb_render_column', array($this, 'render_column_ajaxify'), 10);
			add_action('wp_ajax_tfb_render_subrow', array($this, 'render_sub_row_ajaxify'), 10);
			add_action('wp_ajax_tfb_render_element', array($this, 'render_element_ajaxify'), 10);
			add_action('wp_ajax_tfb_render_element_shortcode', array($this, 'render_element_shortcode_ajaxify'), 10);
		}

		add_filter('themify_builder_is_frontend_editor', array($this, 'post_type_editor_support_check'));


		// Plugin compatibility
		new Themify_Builder_Plugin_Compat();

		// Import Export
		new Themify_Builder_Import_Export();

		$this->components_manager = new Themify_Builder_Components_Manager();

		$this->stylesheet = new Themify_Builder_Stylesheet( $this );

		$this->preview = new Themify_Builder_Preview( $this );

		add_filter( 'themify_builder_module_args', array( $this, 'add_module_args' ) );

	}

	/**
	 * Load assets required to load the frontend editor
	 *
	 * Ensures user has proper access rights before loading
	 */
	function setup_frontend_editor() {
		if( current_user_can( 'edit_post', get_the_id() ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'async_load_builder_js' ), 9 );
			add_action( 'wp_footer', array( $this, 'async_load_assets_loaded' ), 99 );
			add_action( 'wp_footer', array( $this, 'builder_module_panel_frontedit' ), 10 );
		}
	}

	/**
	 * Return Builder data for a post
	 *
	 * @since 1.4.2
	 * @return array
	 */
	public function get_builder_data($post_id) {
		$builder_data = get_post_meta($post_id, $this->meta_key, true);
		$builder_data = stripslashes_deep(maybe_unserialize($builder_data));
		if (!is_array($builder_data)) {
			$builder_data = array();
		}

		return apply_filters('themify_builder_data', $builder_data, $post_id);
	}

	/**
	 * Return all modules for a post as a two-dimensional array
	 *
	 * @since 1.4.2
	 * @return array
	 */
	public function get_flat_modules_list($post_id = null, $builder_data = null) {
		if ($builder_data == null) {
			$builder_data = $this->get_builder_data($post_id);
		}

		$_modules = array();
		// loop through modules in Builder
		if (is_array($builder_data)) {
			foreach ($builder_data as $row) {
				if (!empty($row['cols'])) {
					foreach ($row['cols'] as $col) {
						if (!empty($col['modules'])) {
							foreach ($col['modules'] as $mod) {
								if (isset($mod['mod_name'])) {
									$_modules[] = $mod;
								}
								// Check for Sub-rows
								if (!empty($mod['cols'])) {
									foreach ($mod['cols'] as $sub_col) {
										if (!empty($sub_col['modules'])) {
											foreach ($sub_col['modules'] as $sub_module) {
												$_modules[] = $sub_module;
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return $_modules;
	}

	/**
	 * Return first not empty text module
	 *
	 * @since 1.4.2
	 * @return string
	 */
	public function get_first_text($post_id = null, $builder_data = null) {
		if ($builder_data == null) {
			$builder_data = $this->get_builder_data($post_id);
		}
		// loop through modules in Builder
		if (is_array($builder_data)) {
			foreach ($builder_data as $row) {
				if (!empty($row['cols'])) {
					foreach ($row['cols'] as $col) {
						if (!empty($col['modules'])) {
							foreach ($col['modules'] as $mod) {
								if (isset($mod['mod_name']) && $mod['mod_name'] === 'text' &&  !empty($mod['mod_settings']['content_text'])) {
									return $mod['mod_settings']['content_text'];
								}
								// Check for Sub-rows
								if ( !empty($mod['cols'])) {
									foreach ($mod['cols'] as $sub_col) {
										if (!empty($sub_col['modules'])) {
											foreach ($sub_col['modules'] as $sub_module) {
												if (isset($sub_module['mod_name']) && $sub_module['mod_name'] === 'text' && !empty($sub_module['mod_settings']['content_text'])) {
													return $sub_module['mod_settings']['content_text'];
												}
											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		return '';
	}

	/**
	 * Return Layout part ids used on the page/post
	 *
	 * @since 3.2.2
	 * @return array
	 */
	public function get_layout_part_ids( $post_id = null ) {
		global $Themify_Builder_Layouts;

		$modules = $this->get_flat_modules_list( $post_id );
		$layout_part_ids = array();

		if( !empty( $modules ) ) {
			foreach( $modules as $module ) {
				if( $module[ 'mod_name' ] === 'layout-part' && ! empty( $module[ 'mod_settings' ][ 'selected_layout_part' ] ) ) {
					$args = array(
						'post_type' => $Themify_Builder_Layouts->layout_part->post_type_name,
						'name' => $module[ 'mod_settings' ][ 'selected_layout_part' ],
						'post_status' => 'publish',
						'numberposts' => 1
					);
					
					$layout_part = get_posts( $args );

					if( ! empty( $layout_part[0]->ID ) ) {
						$layout_part_ids[] = $layout_part[0]->ID;
					}

				}
			}
		}

		return $layout_part_ids;
	}

	/**
	 * Load JS and CSs for async loader.
	 *
	 * @since 2.1.9
	 */
	public function async_load_builder_js() {

		wp_enqueue_style('themify-builder-loader', themify_enque(THEMIFY_BUILDER_URI . '/css/themify.builder.loader.css'));
		wp_enqueue_script('themify-builder-loader', themify_enque(THEMIFY_BUILDER_URI . '/js/themify.builder.loader.js'), array('jquery'));
		wp_localize_script('themify-builder-loader', 'tbLoaderVars', array(
			'ajaxurl' => admin_url('admin-ajax.php', 'relative'),
			'assets' => array(
				'scripts' => array(),
				'styles' => array(),
			),
			'post_ID' => get_the_ID(),
			'isRevisionEnabled' => wp_revisions_enabled( get_post( get_the_ID() ) ) ? 'true' : 'false',
			'progress' => '<div id="builder_progress"><div></div></div>',
			'turnOnBuilder' => __('Turn On Builder', 'themify'),
		));

		if (function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}

	/**
	 * Called by AJAX action themify_builder_loader.
	 * 1. Hooks the load_front_js_css function to wp_footer
	 * 2. Saves scripts and styles already loaded in page
	 * 3. Executes wp_head and wp_footer to load new scripts from load_front_js_css. Dismisses output
	 * 4. Compiles list of new styles and scripts to load and js vars to pass
	 * 5. Echoes list
	 *
	 * @since 2.1.9
	 * @since 2.4.2 Clear all output buffers.
	 */
	public function async_load_builder() {
		add_action('wp_footer', array($this, 'load_frontend_interface'));

		global $wp_scripts, $wp_styles;

		$done_styles = isset($_POST['styles']) ? ( $_POST['styles'] ) : array();
		$done_scripts = isset($_POST['scripts']) ? ( $_POST['scripts'] ) : array();

		ob_start();
		wp_head();
		wp_footer();
		while (ob_get_length()) {
			ob_end_clean();
		}

		$results = array();

		$new_styles = array_diff($wp_styles->done, $done_styles);
		$new_scripts = array_diff($wp_scripts->done, $done_scripts);

		if (!empty($new_styles)) {
			$results['styles'] = array();

			foreach ($new_styles as $handle) {
				// Abort if somehow the handle doesn't correspond to a registered stylesheet
				if (!isset($wp_styles->registered[$handle]))
					continue;

				// Provide basic style data
				$style_data = array(
					'handle' => $handle,
					'media' => 'all'
				);

				// Base source
				$src = $wp_styles->registered[$handle]->src;

				// Take base_url into account
				if (strpos($src, 'http') !== 0)
					$src = $wp_styles->base_url . $src;

				// Version and additional arguments
				if (null === $wp_styles->registered[$handle]->ver)
					$ver = '';
				else
					$ver = $wp_styles->registered[$handle]->ver ? $wp_styles->registered[$handle]->ver : $wp_styles->default_version;

				if (isset($wp_styles->args[$handle]))
					$ver = $ver ? $ver . '&amp;' . $wp_styles->args[$handle] : $wp_styles->args[$handle];

				// Full stylesheet source with version info
				$style_data['src'] = add_query_arg('ver', $ver, $src);

				// Parse stylesheet's conditional comments if present, converting to logic executable in JS
				if (isset($wp_styles->registered[$handle]->extra['conditional']) && $wp_styles->registered[$handle]->extra['conditional']) {
					// First, convert conditional comment operators to standard logical operators. %ver is replaced in JS with the IE version
					$style_data['conditional'] = str_replace(array(
						'lte',
						'lt',
						'gte',
						'gt'
							), array(
						'%ver <=',
						'%ver <',
						'%ver >=',
						'%ver >',
							), $wp_styles->registered[$handle]->extra['conditional']);

					// Next, replace any !IE checks. These shouldn't be present since WP's conditional stylesheet implementation doesn't support them, but someone could be _doing_it_wrong().
					$style_data['conditional'] = preg_replace('#!\s*IE(\s*\d+){0}#i', '1==2', $style_data['conditional']);

					// Lastly, remove the IE strings
					$style_data['conditional'] = str_replace('IE', '', $style_data['conditional']);
				}

				// Parse requested media context for stylesheet
				if (isset($wp_styles->registered[$handle]->args))
					$style_data['media'] = esc_attr($wp_styles->registered[$handle]->args);

				// Add stylesheet to data that will be returned to IS JS
								$results['styles'][] = $style_data;
			}
		}

		if (!empty($new_scripts)) {
			$results['scripts'] = array();

			foreach ($new_scripts as $handle) {
				// Abort if somehow the handle doesn't correspond to a registered script
				if (!isset($wp_scripts->registered[$handle])) {
					continue;
				}

				// Provide basic script data
				$script_data = array(
					'handle' => $handle,
					'footer' => ( is_array($wp_scripts->in_footer) && in_array($handle, $wp_scripts->in_footer) ),
					'jsVars' => $wp_scripts->print_extra_script($handle, false)
				);

				// Base source
				$src = $wp_scripts->registered[$handle]->src;

				// Take base_url into account
				if (strpos($src, 'http') !== 0) {
					$src = $wp_scripts->base_url . $src;
				}

				// Version and additional arguments
				if (null === $wp_scripts->registered[$handle]->ver) {
					$ver = '';
				} else {
					$ver = $wp_scripts->registered[$handle]->ver ? $wp_scripts->registered[$handle]->ver : $wp_scripts->default_version;
				}

				if (isset($wp_scripts->args[$handle])) {
					$ver = $ver ? $ver . '&amp;' . $wp_scripts->args[$handle] : $wp_scripts->args[$handle];
				}

				// Full script source with version info
				$script_data['src'] = add_query_arg('ver', $ver, $src);

				// Add script to data that will be returned to IS JS
								$results['scripts'][] = $script_data;
			}
		}
		
		echo json_encode($results);

		die();
	}
	
	/**
	 * Called by AJAX action themify_builder_loader_tpl
	 * Load the script tpl of modules
	 */
	public function async_load_builder_tpl(){
		
		ob_start();
		/**
		 * Fires frontend load javascript template hooks.
		 * Hook all builder frontend js template here.
		 */
		do_action('themify_builder_frontend_load_builder_tmpl');

		$results = ob_get_contents();
		ob_end_clean();

		echo $results;

		die();
	}
	
	/**
	 * Called by AJAX action themify_builder_loader_responsive.
	 * Load the responsive html
	 */
	public function async_load_builder_responsive(){
		echo '<div class="themify_builder_workspace_container"><div class="themify_builder_workspace"><div class="themify_builder_site_canvas">
		<ifr'.'ame id="themify_builder_site_canvas_iframe" name="themify_builder_site_canvas_iframe" class="themify_builder_site_canvas_iframe"></ifr'.'ame>
		</div></div><div class="themify_builder_workspace_overlay"></div></div>';
	}

	/**
	 * Print scripts that are already loaded.
	 *
	 * @since 2.1.9
	 *
	 * @global $wp_scripts, $wp_styles
	 * @action wp_footer
	 * @return string
	 */
	function async_load_assets_loaded() {
		global $wp_scripts, $wp_styles;

		wp_editor('', '');

		$scripts = is_a($wp_scripts, 'WP_Scripts') ? $wp_scripts->done : array();
		$styles = is_a($wp_styles, 'WP_Styles') ? $wp_styles->done : array(); ?>
		<script type="text/javascript">
			jQuery.extend(tbLoaderVars.assets.scripts, <?php echo json_encode($scripts); ?>);
			jQuery.extend(tbLoaderVars.assets.styles, <?php echo json_encode($styles); ?>);
		</script><?php
	}

	public function builder_cpt_check() {
		$post_types = get_option('builder_cpt', null);
		if (!is_array($post_types)) {
			global $wpdb;
			foreach (array('slider', 'highlight', 'testimonial', 'portfolio') as $post_type) {
				$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = '%s'", $post_type));
				if ($count > 0) {
					$this->builder_cpt[] = $post_type;
				}
			}
			update_option('builder_cpt', $this->builder_cpt);
		} else {
			$this->builder_cpt = $post_types;
		}
	}

	public function is_cpt_active($post_type) {
		$active = in_array($post_type, $this->builder_cpt);
		return apply_filters("builder_is_{$post_type}_active", $active);
	}

	/**
	 * Register default directories used to load modules and their templates
	 */
	function setup_default_directories() {
		$this->register_directory('templates', THEMIFY_BUILDER_TEMPLATES_DIR, 1);
		$this->register_directory('templates', get_template_directory() . '/themify-builder/', 5);
		if (is_child_theme()) {
			$this->register_directory('templates', get_stylesheet_directory() . '/themify-builder/', 9);
		}
		$this->register_directory('modules', THEMIFY_BUILDER_MODULES_DIR, 1);
		$this->register_directory('modules', get_template_directory() . '/themify-builder-modules/', 5);
	}

	/**
	 * Init function
	 */
	function setup() {
		// Define builder path
		$this->builder_settings = array(
			'template_url' => 'themify-builder/',
			'builder_path' => THEMIFY_BUILDER_TEMPLATES_DIR . '/'
		);

		// Define meta key name
		$this->meta_key = apply_filters('themify_builder_meta_key', '_themify_builder_settings');
		$this->meta_key_transient = apply_filters('themify_builder_meta_key_transient', 'themify_builder_settings_transient');

		// Check whether grid edit active
		self::is_front_builder_activate();
	}

	function get_meta_key() {
		return $this->meta_key;
	}

	/**
	 * Include required files
	 */
	function includes() {

		require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-form.php' );
		require_once( THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-options.php' );
		require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-import-export.php' );
		require_once( THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-plugin-compat.php' );

		// Class duplicate page
		include_once THEMIFY_BUILDER_CLASSES_DIR . '/class-builder-duplicate-page.php';
		include_once THEMIFY_BUILDER_CLASSES_DIR . '/class-builder-data-manager.php';

		include_once THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-components-manager.php';
		include_once THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-stylesheet.php';
		include_once THEMIFY_BUILDER_CLASSES_DIR . '/class-themify-builder-preview.php';
	}

	/**
	 * List of post types that support the editor
	 *
	 * @since 2.4.8
	 */
	function builder_post_types_support() {
		$public_post_types = get_post_types(array(
			'public' => true,
			'_builtin' => false,
			'show_ui' => true,
		));
		$post_types = array_merge($public_post_types, array('post', 'page'));
		foreach ($post_types as $key => $type) {
			if (!post_type_supports($type, 'editor')) {
				unset($post_types[$key]);
			}
		}

		return apply_filters('themify_builder_post_types_support', $post_types);
	}

	/**
	 * Disable Builder front end editor for post types that do not support "editor".
	 *
	 * @since 2.4.8
	 */
	function post_type_editor_support_check($active) {
		$post_type = get_post_type();
		if (!defined('DOING_AJAX') /* check for Ajax requests, this prevents Builder frontend editor not loading via Ajax */ && !in_array($post_type, $this->builder_post_types_support())
		) {
			$active = false;
		}

		return $active;
	}

	/**
	 * Builder write panels
	 *
	 * @param $meta_boxes
	 *
	 * @return array
	 */
	function builder_write_panels($meta_boxes) {

		// Page builder Options
		$page_builder_options = apply_filters('themify_builder_write_panels_options', array(
			// Notice
			array(
				'name' => '_builder_notice',
				'title' => '',
				'description' => '',
				'type' => 'separator',
				'meta' => array(
					'html' => '<div class="themify-info-link">' . wp_kses_post(sprintf(__('<a href="%s">Themify Builder</a> is a drag &amp; drop tool that helps you to create any type of layouts. To use it: drop the module on the grid where it says "drop module here". Once the post is saved or published, you can click on the "Switch to frontend" button to switch to frontend edit mode.', 'themify'), 'http://themify.me/docs/builder')) . '</div>'
				),
			),
			array(
				'name' => 'page_builder',
				'title' => __('Themify Builder', 'themify'),
				'description' => '',
				'type' => 'page_builder',
				'meta' => array()
			),
		));

		$types = $this->builder_post_types_support();
		$all_meta_boxes = array();
		foreach ($types as $type) {
			$all_meta_boxes[] = apply_filters('themify_builder_write_panels_meta_boxes', array(
				'name' => __('Themify Builder', 'themify'),
				'id' => 'page-builder',
				'options' => $page_builder_options,
				'pages' => $type
			));
		}

		return array_merge($meta_boxes, $all_meta_boxes);
	}

	function register_directory($context, $path, $priority = 10) {
		$this->directory_registry[$context][$priority][] = trailingslashit($path);
	}

	function get_directory_path($context) {
		return call_user_func_array('array_merge', $this->directory_registry[$context]);
	}

	/**
	 * Load builder modules
	 */
	function load_modules() {
		// load modules
		$active_modules = $this->get_modules('active');

		foreach ($active_modules as $m) {
			$path = $m['dirname'] . '/' . $m['basename'];
			require_once( $path );
		}
	}

	/**
	 * Get module php files data
	 * @param string $select
	 * @return array
	 */
	function get_modules($select = 'all') {
		$_modules = array();
		foreach ($this->get_directory_path('modules') as $dir) {
			if (file_exists($dir)) {
				$d = dir($dir);
				while (( false !== ( $entry = $d->read() ))) {
					if ($entry !== '.' && $entry !== '..' && $entry !== '.svn') {
						$path = $d->path . $entry;
						$module_name = basename($path);
						$_modules[$module_name] = $path;
					}
				}
			}
		}
		ksort($_modules);

		foreach ($_modules as $value) {
			if (is_dir($value)){
				continue; /* clean-up, make sure no directories is included in the list */
						}
			$path_info = pathinfo($value);
			if (!preg_match('/^module-/', $path_info['filename']))
				continue; /* convention: module's file name must begin with module-* */
			$id = str_replace('module-', '', $path_info['filename']);
			$module_data = get_file_data($value, array('Module Name'));
			$modules[$id] = array(
				'name' => $module_data[0],
				'id' => $id,
				'dirname' => $path_info['dirname'],
				'extension' => $path_info['extension'],
				'basename' => $path_info['basename'],
			);
		}
                if (!empty($modules)) {
                    if ('active' === $select) {
                            $pre = 'setting-page_builder_';
                            $data = themify_get_data();
                            foreach ($modules as $key => $m) {
                                    $exclude = $pre . 'exc_' . $m['id'];
                                    if ( !empty($data[$exclude]) ){
                                            unset($modules[$m['id']]);
                                    }
                            }

                    } elseif ('registered' === $select) {
                            foreach ($modules as $key => $m) {
                                    /* check if module is registered */
                                    if (!Themify_Builder_Model::check_module_active($key)) {
                                            unset($modules[$key]);
                                    }
                            }
                    }
                }
		return $modules;
	}

	/**
	 * Check if builder frontend edit being invoked
	 */
	public static function is_front_builder_activate() {
		return self::$frontedit_active = Themify_Builder_Model::is_front_builder_activate();
	}

	/**
	 * Add builder metabox
	 */
	function add_builder_metabox() {
		global $post;

		$builder_data = $this->get_builder_data($post->ID);

		if (empty($builder_data)) {
			$builder_data = array();
		}

		include THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-meta.php';
	}

	/**
	 * Load admin js and css
	 * @param $hook
	 */
	function load_admin_interface($hook) {

		if (in_array($hook, array('post-new.php', 'post.php')) && in_array(get_post_type(), themify_post_types()) && Themify_Builder_Model::hasAccess()) {

			add_action('admin_footer', array(&$this, 'load_javascript_template_admin'), 10);

			wp_enqueue_style('themify-builder-loader', themify_enque(THEMIFY_BUILDER_URI . '/css/themify.builder.loader.css'));
			wp_enqueue_style('themify-builder-admin-ui', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui.css'), array(), THEMIFY_VERSION);
			wp_enqueue_style('themify-builder-style', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-style.css'));
			if (is_rtl()) {
				wp_enqueue_style('themify-builder-admin-ui-rtl', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui-rtl.css'), array('themify-builder-admin-ui'), THEMIFY_VERSION);
			}

			// Enqueue builder admin scripts
			$enqueue_scripts = array(
				'masonry',
				'imagesloaded',
				'jquery-ui-core',
				'jquery-ui-accordion',
				'jquery-ui-droppable',
				'jquery-ui-sortable',
				'jquery-ui-resizable',
				'jquery-effects-core',
				'themify-builder-undo-manager-js',
				'themify-builder-google-webfont',
				'themify-combobox',
				'themify-builder-common-js',
				'themify-builder-app-js',
				'themify-builder-backend-js'
			);

			foreach ($enqueue_scripts as $script) {
				switch ($script) {
					case 'themify-combobox':
						wp_enqueue_style($script . '-css', themify_enque(THEMIFY_BUILDER_URI . '/css/themify.combobox.css'), array(), THEMIFY_VERSION);
						wp_enqueue_script($script, THEMIFY_BUILDER_URI . '/js/themify.combobox.min.js', array('jquery'));
						break;
					case 'themify-builder-google-webfont':
						wp_enqueue_script($script, themify_https_esc('http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js'));
						break;
					case 'themify-builder-undo-manager-js':
						wp_enqueue_script($script, THEMIFY_BUILDER_URI . '/js/undomanager.min.js', array('jquery'));
						break;
					case 'themify-builder-common-js':
						wp_register_script('themify-builder-simple-bar-js', THEMIFY_BUILDER_URI . "/js/simplebar.min.js", array('jquery'), '2.0.3', true);
						wp_register_script('themify-builder-common-js', themify_enque(THEMIFY_BUILDER_URI . "/js/themify.builder.common.js"), array('jquery','themify-builder-simple-bar-js'), THEMIFY_VERSION, true);
						wp_enqueue_script('themify-builder-common-js');

						wp_localize_script('themify-builder-common-js', 'themifyBuilderCommon', apply_filters('themify_builder_common_vars', array(
							'text_no_localStorage' =>
							__("Your browser does not support this feature. Please use a modern browser such as Google Chrome or Safari.", 'themify'),
							'text_confirm_data_paste' => __('This will overwrite the data. Ok to proceed?', 'themify'),
							'text_alert_wrong_paste' => __('Error: Paste valid data only (paste row data to row, sub-row data to sub-row, module data to module).', 'themify'),
							'text_import_layout_button' => __( 'Import Layout', 'themify' )
						)));
						break;

					case 'themify-builder-app-js':
						wp_enqueue_script('themify-builder-app-js', themify_enque(THEMIFY_BUILDER_URI . "/js/themify-builder-app.js"), array('jquery'), THEMIFY_VERSION, true);
						break;

					case 'themify-builder-admin-js':
						wp_register_script('themify-builder-admin-js', themify_enque(THEMIFY_BUILDER_URI . "/js/themify.builder.admin.js"), array('jquery', 'themify-builder-common-js'), THEMIFY_VERSION, true);
						wp_enqueue_script('themify-builder-admin-js');

						wp_localize_script('themify-builder-admin-js', 'TBuilderAdmin_Settings', apply_filters('themify_builder_ajax_admin_vars', array(
							'home_url' => get_home_url(),
							'permalink' => get_permalink(),
							'tfb_load_nonce' => wp_create_nonce('tfb_load_nonce')
						)));
						break;

					case 'themify-builder-backend-js':
						wp_enqueue_script( 'jquery-knob', THEMIFY_BUILDER_URI . '/js/jquery.knob.min.js', array( 'jquery' ), null, true );
                                                if(Themify_Builder_Model::is_premium()){
                                                    wp_enqueue_script( 'themifyGradient', themify_enque(THEMIFY_BUILDER_URI . '/js/premium/themifyGradient.js'), array( 'jquery', 'themify-colorpicker' ), null, true );
						}						
                                                wp_register_script('themify-builder-backend-js', themify_enque(THEMIFY_BUILDER_URI . "/js/themify-builder-backend.js"), array('jquery'), THEMIFY_VERSION, true);
						wp_enqueue_script('themify-builder-backend-js');
						$gutterClass = Themify_Builder_Model::get_grid_settings('gutter_class');
						$excluded_key = 'setting-page_builder_exc_';
						$builder_settings = themify_get_data();
						$excluded_modules = array();
						foreach ( $builder_settings as $key => $value ) {
							if( strlen( $key ) > strlen( $excluded_key ) && substr( $key, 0, strlen( $excluded_key ) ) == $excluded_key && intval( $value ) === 1 ) {
								array_push( $excluded_modules, str_replace( $excluded_key, '', $key ) );
							}
						}
						wp_localize_script('themify-builder-backend-js', 'themifyBuilder', apply_filters('themify_builder_ajax_admin_vars', array(
							'ajaxurl'            => admin_url('admin-ajax.php', 'relative'),
							'tfb_load_nonce'     => wp_create_nonce('tfb_load_nonce'),
							'tfb_url'            => THEMIFY_BUILDER_URI,
							'post_ID'            => get_the_ID(),
							'dropPlaceHolder'    => __('drop module here', 'themify'),
							'draggerTitleMiddle' => __('Drag left/right to change columns', 'themify'),
							'draggerTitleLast'   => __('Drag left to add columns', 'themify'),
							'textRowStyling'     => __('Row Styling', 'themify'),
							'textColumnStyling'  => __('Column Styling', 'themify'),
							'permalink'          => get_permalink(),
							'isTouch'            => themify_is_touch() ? 'true' : 'false',
							'isThemifyTheme'     => $this->is_themify_theme() ? 'true' : 'false',
                                                        'is_premium'=>Themify_Builder_Model::is_premium(),
							'disableShortcuts'   => themify_check('setting-page_builder_disable_shortcuts'),
							'isFrontend'         => 'false',
							'gutterClass'        => $gutterClass,
							// Breakpoints
							'breakpoints'        => themify_get_breakpoints(),
							// Output builder data to use by Backbone Models
							'builder_data'       => $this->get_builder_data( get_the_ID() ),
							'modules'            => Themify_Builder_Model::get_modules_localize_settings(),
							'excludedModules'    => implode(',', $excluded_modules ),
							'i18n'               => array(
								'confirmRestoreRev'         => esc_html__('Save the current state as a revision before replacing?', 'themify'),
								'dialog_import_page_post'   => esc_html__( 'Would you like to replace or append the builder?', 'themify' ),
								'confirm_on_duplicate_page' => esc_html__('Save the Builder before duplicating this page?', 'themify'),
								'moduleDeleteConfirm'       => esc_html__('Press OK to remove this module', 'themify'),
								'rowDeleteConfirm'          => esc_html__('Press OK to remove this row', 'themify'),
								'subRowDeleteConfirm'       => esc_html__('Press OK to remove this sub row', 'themify'),
								'importFileConfirm'         => esc_html__('This import will override all current Builder data. Press OK to continue', 'themify'),
								'confirm_template_selected' => esc_html__('Would you like to replace or append the layout?', 'themify'),
								'confirm_delete_layout'     => esc_html__('Are you sure want to delete this layout ?', 'themify'),
								'enterRevComment'           => esc_html__('Add optional revision comment:', 'themify'),
								'confirmDeleteRev'          => esc_html__('Are you sure want to delete this revision', 'themify'),
								'switchToFrontendLabel'     => esc_html__( 'Themify Builder', 'themify' ),
								'editExcludedModule'		=> esc_html__( 'This module is excluded from the Themify Builder settings. If it is an addon module, it could be deactivated. Re-enable the module in Themify Settings or re-activate the addon plugin to resolve this problem.', 'themify' )
							)
						)));
						break;

					default:
						wp_enqueue_script($script);
						break;
				}
			}

			do_action('themify_builder_admin_enqueue', $this);
		}
	}

	/**
	 * Load inline js script
	 * Frontend editor
	 */
	function load_inline_js_script() {
		if (Themify_Builder_Model::is_frontend_editor_page()) {
			?>
			<script type="text/javascript">
				var ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative'); ?>',
						isRtl = <?php echo (int) is_rtl(); ?>;
			</script>
			<?php
		}
	}

	public static function getMapKey(){
		static $key = null;
		if(is_null($key)){
                    $key = themify_get( 'setting-google_map_key' );
		}
		return $key;
	}

	function is_fullwidth_layout_supported() {
		return apply_filters('themify_builder_fullwidth_layout_support', false);
	}

		/**
	 * Register styles and scripts necessary for Builder template output.
	 * These are enqueued when user initializes Builder or from a template output.
	 *
	 * Registered style handlers:
	 *
	 * Registered script handlers:
	 * themify-builder-module-plugins-js
	 * themify-builder-script-js
	 *
	 * @since 2.1.9
	 */
	function register_frontend_js_css() {

		wp_enqueue_style( 'builder-styles', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-style.css'),array(), THEMIFY_VERSION );
		add_filter( 'style_loader_tag', array( $this, 'builder_stylesheet_style_tag' ), 10, 4 );
		////Enqueue main js that will load others needed js

		wp_localize_script('themify-main-script', 'tbLocalScript', apply_filters('themify_builder_script_vars', array(
			'isAnimationActive' => Themify_Builder_Model::is_animation_active(),
			'isParallaxActive' => Themify_Builder_Model::is_parallax_active(),
			'isParallaxScrollActive' => Themify_Builder_Model::is_parallax_scroll_active(),
			'animationInviewSelectors' => array( '.module.wow', '.themify_builder_content .themify_builder_row.wow', '.module_row.wow', '.builder-posts-wrap > .post.wow' ),
			'backgroundSlider' => array(
				'autoplay' => 5000,
				'speed' => 2000,
			),
			'animationOffset' => 100,
			'videoPoster' => THEMIFY_BUILDER_URI . '/img/blank.png',
			'backgroundVideoLoop' => 'yes',
			'builder_url' => THEMIFY_BUILDER_URI,
			'framework_url' => THEMIFY_URI,
			'version' => THEMIFY_VERSION,
			'fullwidth_support' => $this->is_fullwidth_layout_supported(),
			'fullwidth_container' => 'body',
			'loadScrollHighlight' => true,
			'addons'=>Themify_Builder_Model::get_addons_assets(),
                        'breakpoints' => themify_get_breakpoints()
		)));

		//Inject variable values in gallery script
		wp_localize_script('themify-main-script', 'themifyScript', array(
			'lightbox' => themify_lightbox_vars_init(),
			'lightboxContext' => apply_filters('themify_lightbox_context', 'body')
					)
		);
		//Inject variable values in Scroll-Highlight script
		wp_localize_script('themify-main-script', 'tbScrollHighlight', apply_filters('themify_builder_scroll_highlight_vars', array(
			'fixedHeaderSelector' => '',
			'speed' => 900,
			'navigation' => '#main-nav',
			'scrollOffset' => 0
		)));
	}

	/**
	 * Prevent builder-style.css stylesheet from loading in the page, the stylesheet is loaded in themify.builder.script.js
	 *
	 * @return html
	 */
	function builder_stylesheet_style_tag( $tag, $handle, $href, $media ) {
		if( 'builder-styles' === $handle ) {
			$tag = '<meta name="builder-styles-css" content="builder-styles-css" id="builder-styles-css">' . "\n";
		}

		return $tag;
	}

	/**
	 * Load interface js and css
	 *
	 * @since 2.1.9
	 */
	function load_frontend_interface() {
		// load only when editing and login
		if (Themify_Builder_Model::is_frontend_editor_page()) {
			wp_enqueue_style('themify-builder-admin-ui', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui.css'), array(), THEMIFY_VERSION);
			if (is_rtl()) {
				wp_enqueue_style('themify-builder-admin-ui-rtl', themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-admin-ui-rtl.css'), array('themify-builder-admin-ui'), THEMIFY_VERSION);
			}
			wp_enqueue_style('google-fonts-builder', themify_https_esc('http://fonts.googleapis.com/css') . '?family=Open+Sans:400,300,600|Montserrat');
			wp_enqueue_style('themify-colorpicker', themify_enque(THEMIFY_METABOX_URI . 'css/jquery.minicolors.css')); // from themify framework
			// Icon picker
			Themify_Icon_Picker::get_instance()->enqueue();

			do_action('themify_builder_admin_enqueue', $this);
		}

		if (Themify_Builder_Model::is_frontend_editor_page()) {

			if (class_exists('Jetpack_VideoPress')) {
				// Load this so submit_button() is available in VideoPress' print_media_templates().
				require_once ABSPATH . 'wp-admin/includes/template.php';
			}
			
			$enqueue_scripts = array(
				'masonry',
				'imagesloaded',
				'underscore',
				'jquery-ui-core',
				'jquery-ui-accordion',
				'jquery-ui-droppable',
				'jquery-ui-sortable',
				'jquery-ui-resizable',
				'jquery-ui-tooltip',
				'jquery-effects-core',
				'media-upload',
				'jquery-ui-dialog',
				'wpdialogs',
				'wpdialogs-popup',
				'wplink',
				'word-count',
				'editor',
				'quicktags',
				'wp-fullscreen',
				'admin-widgets',
				'themify-colorpicker',
				'themify-builder-google-webfont',
				'themify-builder-undo-manager-js',
				'themify-combobox',
				'themify-builder-common-js',
				'themify-builder-app-js',
				'themify-builder-front-ui-js'
			);

			// For editor
			wp_enqueue_style('buttons');

			// is mobile version
			if ($this->isMobile()) {
				wp_enqueue_script('themify-builder-mobile-ui-js', THEMIFY_BUILDER_URI . "/js/jquery.ui.touch-punch.min.js", array('jquery-ui-mouse'), THEMIFY_VERSION, true);
			}

			foreach ($enqueue_scripts as $script) {
				switch ($script) {
					case 'themify-combobox':
						wp_enqueue_style($script . '-css', themify_enque(THEMIFY_BUILDER_URI . '/css/themify.combobox.css'), array(), THEMIFY_VERSION);
						wp_enqueue_script($script, THEMIFY_BUILDER_URI . '/js/themify.combobox.min.js', array('jquery'));
						break;
					case 'admin-widgets':
						wp_enqueue_script($script, admin_url('/js/widgets.min.js'), array('jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable'));
						break;

					case 'themify-colorpicker':
						wp_enqueue_script($script, THEMIFY_METABOX_URI . 'js/jquery.minicolors.min.js', array('jquery')); // grab from themify framework
						break;

					case 'themify-builder-google-webfont':
						wp_enqueue_script($script, themify_https_esc('http://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js'));
						break;

					case 'themify-builder-undo-manager-js':
						wp_enqueue_script($script, THEMIFY_BUILDER_URI . '/js/undomanager.min.js', array('jquery'));
						break;

					case 'themify-builder-common-js':
						// front ui js
						wp_register_script('themify-builder-simple-bar-js', THEMIFY_BUILDER_URI . "/js/simplebar.min.js", array('jquery'), '2.0.3', true);
						wp_register_script($script, themify_enque(THEMIFY_BUILDER_URI . "/js/themify.builder.common.js"), array('jquery', 'themify-builder-simple-bar-js'), THEMIFY_VERSION, true);
						wp_enqueue_script($script);

						wp_localize_script('themify-builder-common-js', 'themifyBuilderCommon', apply_filters('themify_builder_common_vars', array(
							'text_no_localStorage' =>
							__("Your browser does not support this feature. Please use a modern browser such as Google Chrome or Safari.", 'themify'),
							'text_confirm_data_paste' => __('This will overwrite the data. Ok to proceed?', 'themify'),
							'text_alert_wrong_paste' => __('Error: Paste valid data only (paste row data to row, sub-row data to sub-row, module data to module).', 'themify'),
							'text_import_layout_button' => __( 'Import Layout', 'themify' )
						)));
						break;

					case 'themify-builder-app-js':
						wp_enqueue_script('themify-builder-app-js', themify_enque(THEMIFY_BUILDER_URI . "/js/themify-builder-app.js"), array('jquery'), THEMIFY_VERSION, true);
						break;

					case 'themify-builder-front-ui-js':
						// front ui js
						wp_enqueue_script( 'jquery-knob', THEMIFY_BUILDER_URI . '/js/jquery.knob.min.js', array( 'jquery' ), null, true );
						if(Themify_Builder_Model::is_premium()){
							wp_enqueue_script( 'themifyGradient', themify_enque(THEMIFY_BUILDER_URI . '/js/premium/themifyGradient.js'), array( 'jquery', 'themify-colorpicker' ), null, true );
						}
                                                wp_register_script($script, themify_enque(THEMIFY_BUILDER_URI . "/js/themify-builder-visual.js"), array('jquery', 'jquery-ui-tabs', 'themify-builder-common-js'), THEMIFY_VERSION, true);
						wp_enqueue_script($script);

						$gutterClass = Themify_Builder_Model::get_grid_settings('gutter_class');
						$columnAlignmentClass = Themify_Builder_Model::get_grid_settings('column_alignment_class');
						$excluded_key = 'setting-page_builder_exc_';
						$builder_settings = themify_get_data();
						$excluded_modules = array();
						foreach ( $builder_settings as $key => $value ) {
							if( strlen( $key ) > strlen( $excluded_key ) && substr( $key, 0, strlen( $excluded_key ) ) == $excluded_key && intval( $value ) === 1 ) {
								array_push( $excluded_modules, str_replace( $excluded_key, '', $key ) );
							}
						}
						global $shortcode_tags;
						wp_localize_script($script, 'themifyBuilder', apply_filters('themify_builder_ajax_front_vars', array(
							'ajaxurl'                 => admin_url('admin-ajax.php', 'relative'),
							'isTouch'                 => themify_is_touch() ? 'true' : 'false',
							'tfb_load_nonce'          => wp_create_nonce('tfb_load_nonce'),
							'tfb_url'                 => THEMIFY_BUILDER_URI,
							'post_ID'                 => get_the_ID(),
							'dropPlaceHolder'         => __('drop module here', 'themify'),
							'draggerTitleMiddle'      => __('Drag left/right to change columns', 'themify'),
							'draggerTitleLast'        => __('Drag left to add columns', 'themify'),
							'isFrontend'              => 'true',
							'confirm_delete_layout'   => __('Are you sure want to delete this layout ?', 'themify'),
							'isThemifyTheme'          => $this->is_themify_theme() ? 'true' : 'false',
                                                        'is_premium'=>Themify_Builder_Model::is_premium(),
							'gutterClass'             => $gutterClass,
							'columnAlignmentClass'    => $columnAlignmentClass,
							'disableShortcuts'        => themify_check('setting-page_builder_disable_shortcuts'),
							// for live styling
							'webSafeFonts'            => themify_get_web_safe_font_list(true),
							// Breakpoints
							'breakpoints'             => themify_get_breakpoints(),
							'element_style_rules'     => Themify_Builder_Model::get_elements_style_rules(),
							'modules'                 => Themify_Builder_Model::get_modules_localize_settings(),
							'excludedModules'         => implode( ',', $excluded_modules ),
							'available_shortcodes'  => implode( '|', array_keys( $shortcode_tags ) ),
							'i18n'                    => array(
								'confirmRestoreRev'         => esc_html__('Save the current state as a revision before replacing?', 'themify'),
								'dialog_import_page_post'   => esc_html__( 'Would you like to replace or append the builder?', 'themify' ),
								'moduleDeleteConfirm'       => esc_html__('Press OK to remove this module', 'themify'),
								'confirm_on_turn_off'       => esc_html__('Do you want to save the changes made to this page?', 'themify'),
								'confirm_on_duplicate_page' => esc_html__('Save the Builder before duplicating this page?', 'themify'),
								'confirm_on_unload'         => esc_html__('You have unsaved data.', 'themify'),
								'rowDeleteConfirm'          => esc_html__('Press OK to remove this row', 'themify'),
								'importFileConfirm'         => esc_html__('This import will override all current Builder data. Press OK to continue', 'themify'),
								'confirm_template_selected' => esc_html__('Would you like to replace or append the layout?', 'themify'),
								'subRowDeleteConfirm'       => esc_html__('Press OK to remove this sub row', 'themify'),
								'errorSaveBuilder'          => esc_html__('Error saving. Please save again.', 'themify'),
								// Revisions
								'enterRevComment'           => esc_html__('Add optional revision comment:', 'themify'),
								'confirmDeleteRev'          => esc_html__('Are you sure want to delete this revision', 'themify'),
								'editExcludedModule'		=> esc_html__( 'This module is excluded from the Themify Builder settings and cant be edited', 'themify' ),
								'excludedModule'            => esc_html__( 'Excluded module', 'themify' )
							)
						)));
						wp_localize_script($script, 'themify_builder_plupload_init', $this->get_builder_plupload_init());
						break;

					default:
						wp_enqueue_script($script);
						break;
				}
			}

			do_action('themify_builder_front_editor_enqueue', $this);
		}
	}

	public function slider_live_styling() {
		check_ajax_referer('tfb_load_nonce', 'nonce');

		$bg_slider_data = $_POST['tfb_background_slider_data'];

		$row_or_col = array(
			'styling' => array(
				'background_slider' => urldecode($bg_slider_data['shortcode']),
				'background_type' => 'slider',
				'background_slider_mode' => $bg_slider_data['mode'],
				'background_slider_size' => $bg_slider_data['size'],
			)
		);
		do_action('themify_builder_background_styling',$row_or_col,$bg_slider_data['order'],$bg_slider_data['type']);
		wp_die();
	}

	/**
	 * Duplicate page
	 */
	function duplicate_page_ajaxify() {
		global $themifyBuilderDuplicate;
		check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');
		$post_id = (int) $_POST['tfb_post_id'];
		$post = get_post($post_id);
		$themifyBuilderDuplicate->edit_link = $_POST['tfb_is_admin'];
		$themifyBuilderDuplicate->duplicate($post);
		$response['status'] = 'success';
		$response['new_url'] = $themifyBuilderDuplicate->new_url;
		echo json_encode($response);
		die();
	}

	/**
	 * Render component import form in lightbox
	 */
	function imp_component_data_lightbox_options_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'nonce');

		$component = $_POST['component'];

		$id = '';
		$label = '';
		$description = '';

		switch ($component) {
			case 'row':
				$id = 'tfb_imp_row_data_field';
				$label = __('Row data', 'themify');
				$description = __('Paste row data here', 'themify');
				break;

			case 'sub-row':
				$id = 'tfb_imp_sub_row_data_field';
				$label = __('Sub-Row data', 'themify');
				$description = __('Paste sub-row data here', 'themify');
				break;

			case 'module':
				$id = 'tfb_imp_module_data_field';
				$label = __('Module data', 'themify');
				$description = __('Paste module data here', 'themify');
				break;

			case 'column':
				$id = 'tfb_imp_column_data_field';
				$label = __('Column data', 'themify');
				$description = __('Paste column data here', 'themify');
				break;

			case 'sub-column':
				$id = 'tfb_imp_sub_column_data_field';
				$label = __('Sub-Column data', 'themify');
				$description = __('Paste sub-column data here', 'themify');
				break;
		}

		$fields = array(
			array(
				'id' => $id,
				'type' => 'textarea',
				'label' => $label,
				'class' => 'xlarge',
				'description' => $description,
				'rows' => 13
			)
		);

		if ( in_array( $component, array( 'column', 'sub-column' ) ) ) {

			$data_index = $_POST['indexData'];
			$uniqid = uniqid();
			$row_index = isset( $data_index['row'] ) ? $data_index['row'] : $uniqid;
			$col_index = isset( $data_index['col'] ) ? $data_index['col'] : $uniqid;

			$fields[] = array(
				'id' => 'imp_row_index',
				'type' => 'hidden',
				'label' => '',
				'value' => $row_index
			);

			$fields[] = array(
				'id' => 'imp_col_index',
				'type' => 'hidden',
				'label' => '',
				'value' => $col_index
			);
		}

		include_once THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-imp-component-form.php';
		die();
	}

	/**
	 * Render component export form in lightbox.
	 */
	function exp_component_data_lightbox_options_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'nonce');

		$component = $_POST['component'];

		$id = '';
		$label = '';
		$description = '';

		switch ($component) {
			case 'row':
				$id = 'tfb_exp_row_data_field';
				$label = __('Row data', 'themify');
				$description = __('You can copy & paste this data to another Builder site', 'themify');
				break;

			case 'sub-row':
				$id = 'tfb_exp_sub_row_data_field';
				$label = __('Sub-Row data', 'themify');
				$description = __('You can copy & paste this data to another Builder site', 'themify');
				break;

			case 'module':
				$id = 'tfb_exp_module_data_field';
				$label = __('Module data', 'themify');
				$description = __('You can copy & paste this data to another Builder site', 'themify');
				break;

			case 'column':
				$id = 'tfb_exp_column_data_field';
				$label = __('Column data', 'themify');
				$description = __('You can copy & paste this data to another Builder site', 'themify');
				break;

			case 'sub-column':
				$id = 'tfb_exp_sub_column_data_field';
				$label = __('Sub-Column data', 'themify');
				$description = __('You can copy & paste this data to another Builder site', 'themify');
				break;
		}

		$fields = array(
			array(
				'id' => $id,
				'type' => 'textarea',
				'label' => $label,
				'class' => 'xlarge',
				'description' => $description,
				'rows' => 13 //300px height
			)
		);

		include_once THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-exp-component-form.php';
		die();
	}

	function shortcode_preview() {
		check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');
		if (!empty($_POST['shortcode'])) {
			$shortcode = sanitize_text_field($_POST['shortcode']);
			$images = $this->get_images_from_gallery_shortcode($shortcode);
			if (!empty($images)) {
				$html = '<div class="themify_builder_shortcode_preview">';
				foreach ($images as $image) {
					$img_data = wp_get_attachment_image_src($image->ID, 'thumbnail');
					$html.='<img src="' . $img_data[0] . '" width="50" height="50" />';
				}
				$html.='</div>';
				echo $html;
			}
		}
		wp_die();
	}
		
	function themify_get_tax(){
		if(!empty($_GET['tax']) && !empty($_GET['term'])){
			$terms_by_tax = get_terms(sanitize_key($_GET['tax']),array('hide_empty'=>true,'name__like'=>sanitize_text_field($_GET['term'])));
			$items = array();
			if(!empty($terms_by_tax)){
				foreach ($terms_by_tax as $t){
					$items[] = array('value'=>$t->slug,'label'=>$t->name);
				}
			}
			echo wp_json_encode($items);
		}
		wp_die();
	}
	
	function themify_builder_get_tax_data(){
		if(!empty($_POST['data'])){
			$respose = array();
			foreach($_POST['data'] as $k=>$v){
				$tax = key($v);
				$slug = $v[$tax];
				$terms_by_slug = get_term_by('slug',$slug,$tax);
				$respose[] = array('tax'=>$tax,'val'=>$terms_by_slug->name);
			}
			echo wp_json_encode($respose);
		}
		wp_die();
	}

	/**
	 * Load Editable builder grid
	 */
	function load_toggle_frontend_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');

		$response = array();
		$post_ids = isset($_POST['tfb_post_ids']) ? $_POST['tfb_post_ids'] : array();
		$is_edit = !empty($_POST['state']);
		global $post;

		foreach ($post_ids as $k => $id) {
			$sanitize_id = (int) $id;

			$builder_data = $this->get_builder_data( $sanitize_id );
			$response[$k]['builder_id'] = $sanitize_id;

			if ( $is_edit ) {
				$response[$k]['builder_data'] = $builder_data;
			} else {
				$post = get_post($sanitize_id);
				setup_postdata($post);
				$response[$k]['markup'] = $this->retrieve_template('builder-output.php', array('builder_output' => $builder_data, 'builder_id' => $post->ID ), '', '', false);
			}
		}
		
		if ( ! $is_edit ) 
			wp_reset_postdata();

		echo json_encode($response);

		die();
	}

	/**
	 * Load module partial when update live content
	 */
	function load_module_partial_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');
		global $post;

		$temp_post = $post;
		$post_id = (int) $_POST['tfb_post_id'];
		$cid = $_POST['tfb_cid'];
		$post = get_post($post_id);
		$module_slug = $_POST['tfb_module_slug'];
		$module_settings = json_decode(stripslashes($_POST['tfb_module_data']), true);
		$identifier = array( $cid );
		$response = array();

		$new_modules = array(
			'mod_name' => $module_slug,
			'mod_settings' => $module_settings
		);

		$response['html'] = $this->get_template_module($new_modules, $cid, false, false, null, $identifier);
		$response['gfonts'] = $this->get_custom_google_fonts();
		$post = $temp_post;
		echo json_encode($response);

		die();
	}

	public function render_element_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');
		
		$response = array();
		$batch = json_decode(stripslashes($_POST['batch']), true);

		if ( is_array( $batch ) && !empty( $batch )) {
			foreach( $batch as $b ) {
				$type = $b['data']['elType'];
				switch ( $type ) {
					case 'module':
						
						$identifier = array($b['jobID']);
						$markup = $this->get_template_module($b['data'], $b['jobID'], false, false, null, $identifier);
                                                $styling = $b['data']['mod_settings'];
                                                $type = $b['data']['mod_name'];
						break;

					case 'subrow':

						$b['data']['row_order'] = $b['jobID'];
						if ( isset( $b['data']['cols'] ) ){
                                                    unset( $b['data']['cols'] );
                                                }
						$markup = $this->get_template_sub_row($b['jobID'], $b['jobID'], $b['jobID'], $b['data'], $b['jobID']);
						$styling = isset( $b['data']['styling'] ) ? $b['data']['styling'] : array();
						break;

					case 'column':

						$row = array( 'row_order' => $b['jobID'] );
						if ( isset( $b['data']['modules'] ) ){
                                                    unset( $b['data']['modules'] );
                                                }
						$b['data']['column_order'] = $b['jobID'];
						$markup = $this->get_template_column( $b['jobID'], $row, $b['jobID'], $b['data'], $b['jobID'] );
						$styling = isset( $b['data']['styling'] ) ? $b['data']['styling'] : array();
						break;

					case 'row':

						$b['data']['row_order'] = $b['jobID'];
						if ( isset( $b['data']['cols'] ) ){
                                                    unset( $b['data']['cols'] );
                                                }
						$markup = $this->get_template_row($b['jobID'], $b['data'], $b['jobID']);
						$styling = isset( $b['data']['styling'] ) ? $b['data']['styling'] : array();
						break;
				}
                                $styles = $this->stylesheet->get_style_rules( $type, $styling );
				$response[ $b['jobID'] ] = array( 'markup' => $markup, 'styles' => $styles );
			}
		}
		echo json_encode($response);

		die();
	}

	public function render_element_shortcode_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');
		
		$response = array();
		$shortcode_data = json_decode(stripslashes_deep($_POST['shortcode_data']), true);
		
		if ( is_array( $shortcode_data ) ) {
			foreach( $shortcode_data as $shortcode ) {
				$response[] = array( 'key' => $shortcode['key'], 'rendered_html' => do_shortcode( $shortcode['sc_render'] ) );
			}
		}

		wp_send_json_success( $response );
	}

	/**
	 * Load row partial when update live content
	 */
	function load_row_partial_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'nonce');

		$post_id = (int) $_POST['post_id'];
		$row = json_decode( stripslashes_deep( $_POST['row'] ), true );
		$uniqid = uniqid();
		$response = array();

		$response['html'] = $this->get_template_row($uniqid, $row, $post_id);
		$response['gfonts'] = $this->get_custom_google_fonts();

		echo json_encode($response);

		die();
	}

	/**
	 * Render column in ajax.
	 * 
	 * @return json
	 */
	public function render_column_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'nonce');

		$cid = $_POST['cid'];
		$col = json_decode(stripslashes_deep($_POST['column_data']), true);
		$response = array();

		if ( isset( $col['component_name'] ) && 'column' == $col['component_name'] ) {
			$row = array( 'row_order' => $cid );
			$col['column_order'] = $cid;
			$response['html'] = $this->get_template_column( $row['row_order'], $row, $col['column_order'], $col, $cid );
		} else if ( isset( $col['component_name'] ) && 'sub-column' == $col['component_name'] ) {
			$rows = $col['row_order'];
			$cols = $col['col_order'];
			$modules = $col['sub_row_order'];
			$col_key = $col['column_order'];
                        $post_id = (int) $_POST['post_id'];
			$response['html'] = $this->get_template_sub_column( $rows, $cols, $modules, $col_key, $col, $post_id );
		}

		echo json_encode($response);

		die();
	}

	/**
	 * Render sub-row in ajax.
	 * 
	 * @return json
	 */
	public function render_sub_row_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'nonce');

		$post_id = (int) $_POST['post_id'];
		$mod = json_decode(stripslashes_deep($_POST['sub_row_data']), true);
		$response = array();
		$rows = $mod['row_order'];
		$cols = $mod['col_order'];
		$modules = $mod['sub_row_order'];
		$response['html'] = $this->get_template_sub_row( $rows, $cols, $modules, $mod, $post_id );	

		echo json_encode($response);

		die();
	}

	/**
	 * Render duplicate row
	 */
	function render_duplicate_row_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'nonce');

		$row = stripslashes_deep($_POST['row']);
		$post_id = $_POST['id'];
		$response = array();
		$uniqid = uniqid();

		if (isset($row['row_order'])){
                    unset($row['row_order']);
                }
		$response['html'] = $this->get_template_row($uniqid, $row, $post_id);

		echo json_encode($response);

		die();
	}

	public static function remove_cache($post_id, $tag = false, array $args = array()) {
		if(Themify_Builder_Model::is_premium()){
			TFCache::remove_cache($tag, $post_id, $args);
		}
	}

	/**
	 * Save builder main data
	 */
	function save_data_builder() {
		check_ajax_referer('tfb_load_nonce', 'tfb_load_nonce');

		// Information about writing process.
		$results = array();

		$saveto = $_POST['tfb_saveto'];
		$ids = json_decode(stripslashes_deep($_POST['ids']), true);

		if (is_array($ids) && !empty($ids)) {
			global $wpdb;
			foreach ($ids as $v) {
				$post_id = isset($v['id']) ? $v['id'] : '';

				/* skip saving the data if user doesn't have access rights to modify it */
				if( ! current_user_can( 'edit_post', $post_id ) ) {
					continue;
				}

				$post_data =  !empty($v['data']) && is_array($v['data'])  > 0  ? $v['data'] : array();

				if ('main' === $saveto) {
					$GLOBALS['ThemifyBuilder_Data_Manager']->save_data($post_data, $post_id);

					// update the post modified date time, to indicate the post has been modified
					//we don't need to call save/updates hooks, it can break the builder save(e.g there are too many revisions)
					$wpdb->update( 
						$wpdb->posts, 
						array( 
							'post_modified' => current_time( 'mysql' ),
							'post_modified_gmt' => current_time( 'mysql', 1 )	
						), 
						array( 'ID' => $post_id ), 
						array( 
							'%s',	
							'%s'
						), 
						array( '%d' ) 
					);
					if (!empty($post_data)) {
						// Write Stylesheet
						$results = $this->stylesheet->write_stylesheet(array('id' => $post_id, 'data' => $post_data));
					}

					self::remove_cache($post_id);
				} else {
					$transient = $this->meta_key_transient . '_' . $post_id;
					set_transient($transient, $post_data, 60 * 60);
				}
			}
		}

		wp_send_json_success($results);
	}

	/**
	 * Hook to content filter to show builder output
	 * @param $content
	 * @return string
	 */
	function builder_show_on_front($content) {
		global $post;
		// Exclude builder output in admin post list mode excerpt, Dont show builder on product single description
		if (!is_object($post) 
					|| ( is_admin() && ! defined( 'DOING_AJAX' ) )
					|| post_password_required()
					|| (themify_is_woocommerce_active() &&  is_singular('product') && 'product' == get_post_type() )
		) {
					return $content;
		}
			
		$display = apply_filters('themify_builder_display', true, $post->ID);
		if (false === $display) {
			return $content;
		}

		//the_excerpt
		global $wp_current_filter;
		if (in_array('get_the_excerpt', $wp_current_filter)) {
			if ($content) {
				return $content;
			}
			return $this->get_first_text($post->ID);
		}

		// Infinite-loop prevention
		if (empty($this->post_ids)) {
			$this->post_ids[] = $post->ID;
		} elseif (in_array($post->ID, $this->post_ids)) {
			// we have already rendered this, go back.
			return $content;
		}

		// Builder display position
		$display_position = apply_filters('themify_builder_display_position', 'below', $post->ID);

		$this->post_ids[] = $post->ID;

		$builder_data = $this->get_builder_data($post->ID);

		if (!is_array($builder_data) || strpos($content, '#more-')) {
			$builder_data = array();
		}
		self::$post_id = get_the_ID();
		if ($this->in_the_loop) {
			$builder_output = $this->retrieve_template('builder-output-in-the-loop.php', array('builder_output' => $builder_data, 'builder_id' => $post->ID), '', '', false);
		} else {
			$builder_output = $this->retrieve_template('builder-output.php', array('builder_output' => $builder_data, 'builder_id' => $post->ID), '', '', false);
		}

		if ('above' == $display_position) {
			$content = $builder_output . $content;
		} else {
			$content .= $builder_output;
		}

		$this->post_ids = array_unique($this->post_ids);
		if (array_shift($this->post_ids) == $post->ID) {
			// the loop is finished, reset the ID list
			$this->post_ids = array();
		}

		// load Builder stylesheet if necessary
		$content = $this->get_builder_stylesheet( $builder_output ) . $content;

		return $content;
	}

	/**
	 * Returns <link> tag for Builder stylesheet or enqueue it properly, if necessary.
	 *
	 * @return string
	 */
	public function get_builder_stylesheet( $builder_output ) {

		/* in RSS feeds and REST API endpoints, do not output the scripts */
		if( is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}

		static $builder_loaded = false;
		$output = '';
		// check if builder has any content
		if( ! $builder_loaded && ! Themify_Builder_Model::is_front_builder_activate() && strpos( $builder_output,'themify_builder_row' ) !== false ) {
			$builder_loaded = true;
			wp_dequeue_style('builder-styles');
			$link_tag = "<link id='builder-styles' rel='stylesheet' href='". themify_enque(THEMIFY_BUILDER_URI . '/css/themify-builder-style.css').'?ver='.THEMIFY_VERSION. "' type='text/css' />";
			$output .= $this->get_responsive_breakpoint_script();
			$output .= '<script type="text/javascript">
				if( document.getElementById( "builder-styles-css" ) ) document.getElementById( "builder-styles-css" ).insertAdjacentHTML( "beforebegin", "' . $link_tag . '" );
				</script>';
		}
		return $output;
	}

	/**
	 * Output the script tag that adds responsive body classes
	 *
	 * @todo: move to builder.script.js
	 */
	function get_responsive_breakpoint_script() {
		$breakpoints = themify_get_breakpoints();
		ob_start();
		?>
<script>
(function ( $el, el ){
	function responsive_classes() {
		if ( el.clientWidth > <?php echo $breakpoints['tablet_landscape'][1]; ?> ) {
			$el.removeClass( 'tb_tablet tb_mobile' ).addClass( 'tb_desktop' );
		} else if ( el.clientWidth < <?php echo $breakpoints['mobile']?> ) {
			$el.removeClass( 'tb_desktop tb_tablet' ).addClass( 'tb_mobile' );
		} else if ( el.clientWidth < <?php echo $breakpoints['tablet_landscape'][1]; ?> ) {
			$el.removeClass( 'tb_desktop tb_mobile' ).addClass( 'tb_tablet' );
		}
	}
	responsive_classes();
	jQuery( window ).resize( responsive_classes );
})( jQuery( 'body' ), document.body )
</script>
		<?php
		return ob_get_clean();
	}

	/**
	 * Display module panel on frontend edit
	 */
	function builder_module_panel_frontedit() {
		echo '<div style="display:none;">';
		wp_editor(' ', 'tfb_lb_hidden_editor');
		echo '</div>';
	}

	/**
	 * Loads JS templates for front-end editor.
	 */
	public function load_javascript_template_front() {
		include_once( sprintf("%s/themify-builder-js-tmpl-common.php", THEMIFY_BUILDER_INCLUDES_DIR) );
		include_once( sprintf("%s/themify-builder-js-tmpl-front.php", THEMIFY_BUILDER_INCLUDES_DIR) );
		include_once( sprintf("%s/themify-builder-module-panel.php", THEMIFY_BUILDER_INCLUDES_DIR) );
		$this->components_manager->render_components_form_content();

		do_action( 'themify_builder_load_javascript_templates' );
	}

	/**
	 * Loads JS templates for WordPress admin dashboard editor.
	 */
	public function load_javascript_template_admin() {
		include_once( sprintf("%s/themify-builder-js-tmpl-common.php", THEMIFY_BUILDER_INCLUDES_DIR) );
		include_once( sprintf("%s/themify-builder-js-tmpl-admin.php", THEMIFY_BUILDER_INCLUDES_DIR) );
		$this->components_manager->render_components_form_content();

		do_action( 'themify_builder_load_javascript_templates' );
	}

	/**
	 * Get initialization parameters for plupload. Filtered through themify_builder_plupload_init_vars.
	 * @return mixed|void
	 * @since 1.4.2
	 */
	function get_builder_plupload_init() {
		return apply_filters('themify_builder_plupload_init_vars', array(
			'runtimes' => 'html5,flash,silverlight,html4',
			'browse_button' => 'themify-builder-plupload-browse-button', // adjusted by uploader
			'container' => 'themify-builder-plupload-upload-ui', // adjusted by uploader
			'drop_element' => 'drag-drop-area', // adjusted by uploader
			'file_data_name' => 'async-upload', // adjusted by uploader
			'multiple_queues' => true,
			'max_file_size' => wp_max_upload_size() . 'b',
			'url' => admin_url('admin-ajax.php'),
			'flash_swf_url' => includes_url('js/plupload/plupload.flash.swf'),
			'silverlight_xap_url' => includes_url('js/plupload/plupload.silverlight.xap'),
			'filters' => array(array(
					'title' => __('Allowed Files', 'themify'),
					'extensions' => 'jpg,jpeg,gif,png,zip,txt'
				)),
			'multipart' => true,
			'urlstream_upload' => true,
			'multi_selection' => false, // added by uploader
			// additional post data to send to our ajax hook
			'multipart_params' => array(
				'_ajax_nonce' => '', // added by uploader
				'action' => 'themify_builder_plupload_action', // the ajax action name
				'imgid' => 0 // added by uploader
			),
			'fonts'=>array('safe'=>themify_get_web_safe_font_list(),'google'=>themify_get_google_web_fonts_list())
		));
	}

	/**
	 * Inject plupload initialization variables in Javascript
	 * @since 1.4.2
	 */
	function plupload_front_head() {
		wp_localize_script('themify-builder-front-ui-js', 'themify_builder_plupload_init', $this->get_builder_plupload_init());
	}

	/**
	 * Plupload initialization parameters
	 * @since 1.4.2
	 */
	function plupload_admin_head() {
		wp_localize_script('themify-builder-backend-js', 'themify_builder_plupload_init', $this->get_builder_plupload_init());
	}

	/**
	 * Plupload ajax action
	 */
	function builder_plupload() {
		// check ajax nonce
		check_ajax_referer('tfb_load_nonce');
		if( ! current_user_can( 'upload_files' ) ) {
			die;
		}

		$imgid = $_POST['imgid'];
		/** If post ID is set, uploaded image will be attached to it. @var String */
		$postid = $_POST['topost'];

		/** Handle file upload storing file|url|type. @var Array */
		$file = wp_handle_upload($_FILES[$imgid . 'async-upload'], array('test_form' => true, 'action' => 'themify_builder_plupload_action'));

		//let's see if it's an image, a zip file or something else
		$ext = explode('/', $file['type']);

		// Import routines
		if ('zip' === $ext[1] || 'rar' === $ext[1] || 'plain' === $ext[1]) {

			$url = wp_nonce_url('admin.php?page=themify');

			if (false === ( $creds = request_filesystem_credentials($url) )) {
				return true;
			}
			if (!WP_Filesystem($creds)) {
				request_filesystem_credentials($url, '', true);
				return true;
			}

			global $wp_filesystem;
						$is_txt = $path = false;
			if ('zip' === $ext[1] || 'rar' === $ext[1]) {
							$destination = wp_upload_dir();
							$destination_path = $destination['path'];
							unzip_file($file['file'], $destination_path);
							if ($wp_filesystem->exists($destination_path . '/builder_data_export.txt')) {
								$path = $destination_path . '/builder_data_export.txt';
								$is_txt = true;
							}
			} elseif ($wp_filesystem->exists($file['file'])) {
							$path = $file['file']; 
			}
						
						if($path){
							$data = $wp_filesystem->get_contents($path);
							$data = is_serialized($data)?maybe_unserialize($data):json_decode($data);
							// set data here

							// safety check: ensure user has access rights to edit the post
							if( current_user_can( 'edit_post', $postid ) ) {
								$GLOBALS['ThemifyBuilder_Data_Manager']->save_data( $data, $postid, true );
							}

							if($is_txt){
								$wp_filesystem->delete($destination_path . '/builder_data_export.txt');
							}
							$wp_filesystem->delete($file['file']);
						}
						else{
							_e('Data could not be loaded', 'themify');
						}
		} else {
			// Insert into Media Library
			// Set up options array to add this file as an attachment
			$attachment = array(
				'post_mime_type' => sanitize_mime_type($file['type']),
				'post_title' => str_replace('-', ' ', sanitize_file_name(pathinfo($file['file'], PATHINFO_FILENAME))),
				'post_status' => 'inherit'
			);

			if ($postid)
				$attach_id = wp_insert_attachment($attachment, $file['file'], $postid);

			// Common attachment procedures
			require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
			$attach_data = wp_generate_attachment_metadata($attach_id, $file['file']);
			wp_update_attachment_metadata($attach_id, $attach_data);

			if ($postid) {
				$large = wp_get_attachment_image_src($attach_id, 'large');
				$thumb = wp_get_attachment_image_src($attach_id, 'thumbnail');

				//Return URL for the image field in meta box
				$file['large_url'] = $large[0];
				$file['thumb'] = $thumb[0];
				$file['id'] = $attach_id;
			}
		}

		$file['type'] = $ext[1];
		// send the uploaded file url in response
		echo json_encode($file);
		exit;
	}

	/**
	 * Display Toggle themify builder
	 * wp admin bar
	 */
	function builder_admin_bar_menu($wp_admin_bar) {
		if (is_admin() || !Themify_Builder_Model::is_frontend_editor_page() || ( is_post_type_archive() && !is_post_type_archive('product') ) || !is_admin_bar_showing() || isset($wp_query->query_vars['product_cat']) || is_tax('product_tag')) {
			return;
		}
		$p = get_queried_object(); //get_the_ID can back wrong post id
		$post_id = isset($p->ID) ? $p->ID : false;
		unset($p);
		if (!$post_id || !current_user_can('edit_page', $post_id)){
                    return;
                }

		$args = array(
			array(
				'id' => 'themify_builder',
				'title' => sprintf('<span class="themify_builder_front_icon"></span> %s', esc_html__('Turn On Builder', 'themify')),
				'href' => '#',
				'meta' => array('class' => 'toggle_tf_builder')
			)
		);

		if (is_singular() || is_page()) {
			$args = apply_filters('themify_builder_admin_bar_menu_single_page', $args);
		}

		foreach ($args as $arg) {
			$wp_admin_bar->add_node($arg);
		}
	}

	/**
	 * Switch to frontend
	 * @param int $post_id
	 */
	function switch_frontend() {
		if( isset( $_GET['builder_switch_frontend'] ) ) {
			//verify post is not a revision
			if ( isset( $_GET['post'] ) && ( $post_id = $_GET['post'] ) && ! wp_is_post_revision( $post_id ) ) {
				self::remove_cache( $post_id );
				// redirect to frontend
				$post_url = get_permalink( $post_id );
				wp_redirect( themify_https_esc( $post_url ) . '#builder_active' );
				exit;
			}
		}
	}

	/**
	 * Editing module panel in frontend
	 * @param $mod_name
	 * @param $mod_settings
	 */
	function module_edit_panel_front($mod_name, $mod_settings) {
		?>
		<div class="module_menu_front">
			<ul class="themify_builder_dropdown_front">
				<li class="themify_module_menu"><span class="ti-menu"></span>
					<ul>
						<li><a href="#" data-title="<?php _e('Edit', 'themify') ?>" rel="themify-tooltip-bottom"
							   class="themify_module_options" data-module-name="<?php echo esc_attr($mod_name); ?>">
		<?php _e('Edit', 'themify') ?>
							</a></li>
						<li><a href="#" data-title="<?php _e('Styling', 'themify') ?>" rel="themify-tooltip-bottom"
							   class="themify_builder_module_styling js--themify_builder_module_styling ti-brush" data-module-name="<?php echo esc_attr($mod_name); ?>">
		<?php _e('Styling', 'themify') ?>
							</a></li>
						<li><a href="#" data-title="<?php _e('Duplicate', 'themify') ?>" rel="themify-tooltip-bottom"
							   class="themify_module_duplicate">
		<?php _e('Duplicate', 'themify') ?>
							</a></li>
						<li class="separator">
							<div></div>
						</li>
						<li><a href="#" data-title="<?php _e('Export', 'themify') ?>" rel="themify-tooltip-bottom"
							   class="themify_builder_export_component ti-export" data-component="module">
		<?php _e('Export', 'themify') ?>
							</a></li>
						<li><a href="#" data-title="<?php _e('Import', 'themify') ?>" rel="themify-tooltip-bottom"
							   class="themify_builder_import_component ti-import" data-component="module">
		<?php _e('Import', 'themify') ?>
							</a></li>
						<li class="separator">
							<div></div>
						</li>
						<li><a href="#" data-title="<?php _e('Copy', 'themify') ?>" rel="themify-tooltip-bottom"
							   class="themify_builder_copy_component ti-files" data-component="module">
		<?php _e('Copy', 'themify') ?>
							</a></li>
						<li><a href="#" data-title="<?php _e('Paste', 'themify') ?>" rel="themify-tooltip-bottom"
							   class="themify_builder_paste_component ti-clipboard" data-component="module">
		<?php _e('Paste', 'themify') ?>
							</a></li>
						<li class="separator"><div></div></li>
						<li><a href="#" data-title="<?php _e('Delete', 'themify') ?>" rel="themify-tooltip-bottom"
							   class="themify_module_delete">
		<?php _e('Delete', 'themify') ?>
							</a></li>
					</ul>
				</li>
			</ul>
			<div class="front_mod_settings mod_settings_<?php echo esc_attr($mod_name); ?>" data-mod-name="<?php echo esc_attr($mod_name); ?>">
				<script type="text/json"><?php echo json_encode($this->clean_json_bad_escaped_char($mod_settings)); ?></script>
			</div>
		</div>
		<div class="themify_builder_data_mod_name"><?php echo Themify_Builder_Model::get_module_name($mod_name); ?></div>
		<?php
	}

	/**
	 * Add Builder body class
	 * @param $classes
	 * @return mixed|void
	 */
	function body_class($classes) {
		if (Themify_Builder_Model::is_frontend_editor_page()) {
			$classes[] = 'frontend';
		}

		if (themify_is_touch()) {
			$classes[] = 'istouch';
		}
		// return the $classes array
		return apply_filters('themify_builder_body_class', $classes);
	}

	/**
	 * Just print the shortcode text instead of output html
	 * @param array $array
	 * @return array
	 */
	function return_text_shortcode($array) {
		if (!empty($array)) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
                                    $this->return_text_shortcode($value);
				} else {
                                    $array[$key] = str_replace(array('[',']'), array('&#91;','&#93;'), $value);
				}
			}
		} 
                else {
			$array = array();
		}
		return $array;
	}

	/**
	 * Clean bad escape char for json
	 * @param array $array 
	 * @return array
	 */
	function clean_json_bad_escaped_char($array) {
		if (!empty($array)) {
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					$this->clean_json_bad_escaped_char($value);
				} else {
					$array[$key] = str_replace("<wbr />", "<wbr>", $value);
				}
			}
		} 
                else {
			$array = array();
		}
		return $array;
	}

	/**
	 * Retrieve builder templates
	 * @param $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 * @param bool $echo
	 * @return string
	 */
	function retrieve_template($template_name, $args = array(), $template_path = '', $default_path = '', $echo = true) {
		ob_start();
		$this->get_template($template_name, $args, $template_path = '', $default_path = '');
		if ($echo){
					echo ob_get_clean();
				}	
		else{
					return ob_get_clean();
				}
			
	}

	/**
	 * Get template builder
	 * @param $template_name
	 * @param array $args
	 * @param string $template_path
	 * @param string $default_path
	 */
	function get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
		if ($args && is_array($args))
			extract($args);

		$located = $this->locate_template($template_name, $template_path, $default_path);

		if(file_exists($located)){
                    include( $located );
                }
	}

	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 * 		yourtheme		/	$template_path	/	$template_name
	 * 		$default_path	/	$template_name
	 */
	function locate_template($template_name, $template_path = '', $default_path = '') {
		$template = '';
		foreach ($this->get_directory_path('templates') as $dir) {
			if (is_file($dir . $template_name)) {
				$template = $dir . $template_name;
			}
		}

		// Get default template
		if (!$template){
					$template = $default_path . $template_name;
				}
			

		// Return what we found
		return apply_filters('themify_builder_locate_template', $template, $template_name, $template_path);
	}

	/**
	 * Get template for module
	 * @param $mod
	 * @param bool $echo
	 * @param bool $wrap
	 * @param null $class
	 * @param array $identifier
	 * @return bool|string
	 */
	function get_template_module($mod, $builder_id = 0, $echo = true, $wrap = true, $class = null, $identifier = array()) {
		/* allow addons to control the display of the modules */
		$display = apply_filters('themify_builder_module_display', true, $mod, $builder_id, $identifier);
		if (false === $display) {
			return false;
		}

		$mod['mod_name'] = isset($mod['mod_name']) ? $mod['mod_name'] : '';
		 // check whether module active or not
		if (!Themify_Builder_Model::check_module_active($mod['mod_name'])){
			return false;
		}
		$output = '';
		$mod['mod_settings'] = isset($mod['mod_settings']) ? $mod['mod_settings'] : array();

		$mod_id = $mod['mod_name'] . '-' . $builder_id . '-' . implode('-', $identifier);
		$output .= PHP_EOL; // add line break
	   
		$is_frontend = Themify_Builder_Model::is_frontend_editor_page() || ( isset($_GET['themify_builder_infinite_scroll']) && 'yes' === $_GET['themify_builder_infinite_scroll'] ) || $this->stylesheet->is_front_end_style_inline;
		if (!$is_frontend) {
			$post = get_post($builder_id);
			$is_frontend = is_object($post) && $post->post_type == 'tbuilder_layout_part';
		}
	   
		if ($wrap) {
			if ($is_frontend && $mod['mod_name']  && !isset($this->modules_styles[$mod['mod_name']])) {
				$styling = Themify_Builder_Model::$modules[$mod['mod_name']]->get_styling_settings();
				$all_rules = $this->stylesheet->make_styling_rules($styling, $mod['mod_settings'], 1);
				if (!empty($all_rules)) {
					foreach ($all_rules as $id=>$rule) {
						$this->modules_styles[$mod['mod_name']][$id] = array('prop' => $rule['prop'], 'selector' =>(array) $rule['selector']);
					}
				}
			}
			ob_start();
			?>

			<div class="themify_builder_module_front clearfix module-<?php echo esc_attr($mod['mod_name']); ?> active_module <?php echo esc_attr($class); ?>" data-module-name="<?php echo esc_attr($mod['mod_name']); ?>">
				<div class="themify_builder_module_front_overlay"></div>
			<?php
			themify_builder_edit_module_panel($mod['mod_name'], $mod['mod_settings']);
			$output .= ob_get_clean();
		}

		$module_args = apply_filters('themify_builder_module_args', array());
		$mod['mod_settings'] = wp_parse_args($mod['mod_settings'], $module_args);

		// render the module
		if ( !$is_frontend || ( !isset( $mod['mod_settings']['visibility_all'] ) || $mod['mod_settings']['visibility_all'] != 'hide_all' ) ) {
			$output .= Themify_Builder_Model::$modules[ $mod['mod_name'] ]->do_assets();
			$output .= Themify_Builder_Model::$modules[ $mod['mod_name'] ]->render( $mod_id, $builder_id, $mod['mod_settings'] );
		}

		$style_id = '.themify_builder .' . $mod_id;

		if ($is_frontend) {
			$output .= $this->stylesheet->get_custom_styling($style_id, $mod['mod_name'], $mod['mod_settings']);
			// responsive styling
			$output .= $this->stylesheet->render_responsive_style($style_id, $mod['mod_name'], $mod['mod_settings']);
		}
		if ($wrap){
					$output .= '</div>';
				}
		// add line break
		$output .= PHP_EOL;

		if ($echo) {
			echo $output;
		} else {
			return $output;
		}
	}

        public function add_minify_vars($vars){
            $vars['minify']['js']['themify.builder.script'] = themify_enque(THEMIFY_BUILDER_URI.'/js/themify.builder.script.js',true);
            $vars['minify']['js']['themify.scroll-highlight'] = themify_enque(THEMIFY_BUILDER_URI.'/js/themify.scroll-highlight.js',true);
            $vars['minify']['js']['themify-youtube-bg'] = themify_enque(THEMIFY_BUILDER_URI.'/js/themify-youtube-bg.js',true);
            $vars['minify']['js']['themify.parallaxit'] = themify_enque(THEMIFY_BUILDER_URI.'/js/premium/themify.parallaxit.js',true);
            $vars['minify']['css']['themify-builder-style'] = themify_enque(THEMIFY_BUILDER_URI.'/css/themify-builder-style.css',true);
            
            return $vars;
        }

	public function add_module_args( $args ) {
		$args['before_title'] = '<h3 class="module-title">';
		$args{'after_title'} = '</h3>';
		return $args;
	}

	/**
	 * Check whether theme loop template exist
	 * @param string $template_name 
	 * @param string $template_path 
	 * @return boolean
	 */
	function is_loop_template_exist($template_name, $template_path) {
		$template = locate_template(
				array(
					trailingslashit($template_path) . $template_name
				)
		);

		return !$template?false:true;
	}

	/**
	 * Get checkbox data
	 * @param $setting
	 * @return string
	 */
	function get_checkbox_data($setting) {
		return implode(' ', explode('|', $setting));
	}

	/**
	 * Return only value setting
	 * @param $string 
	 * @return string
	 */
	function get_param_value($string) {
		$val = explode('|', $string);
		return $val[0];
	}

	/**
	 * Includes this custom post to array of cpts managed by Themify
	 * @param Array
	 * @return Array
	 */
	function extend_post_types($types) {
		if (empty($this->public_post_types)) {
			$this->public_post_types = array_unique(array_merge(
							$this->registered_post_types, array_values(get_post_types(array(
				'public' => true,
				'_builtin' => false,
				'show_ui' => true,
							))), array('post', 'page')
			));
		}

		return array_unique(array_merge($types, $this->public_post_types));
	}

	/**
	 * Push the registered post types to object class
	 * @param $type
	 */
	function push_post_types($type) {
				$this->registered_post_types[] = $type;
	}

	/**
	 * Detect mobile browser
	 */
	function isMobile() {
		return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
	}

	/**
	 * Get images from gallery shortcode
	 * @return object
	 */
	function get_images_from_gallery_shortcode($shortcode) {
		preg_match('/\[gallery.*ids=.(.*).\]/', $shortcode, $ids);
		$ids = trim($ids[1], '\\');
		$ids = trim($ids, '"');
		$image_ids = explode(",", $ids);
		$orderby = $this->get_gallery_param_option($shortcode, 'orderby');
		$orderby = $orderby != '' ? $orderby : 'post__in';
		$order = $this->get_gallery_param_option($shortcode, 'order');
		$order = $order != '' ? $order : 'ASC';

		// Check if post has more than one image in gallery
		return get_posts(array(
			'post__in' => $image_ids,
			'post_type' => 'attachment',
			'post_mime_type' => 'image',
			'numberposts' => -1,
			'orderby' => $orderby,
			'order' => $order
		));
	}

	/**
	 * Get gallery shortcode options
	 * @param $shortcode
	 * @param $param
	 */
	function get_gallery_param_option($shortcode, $param = 'link') {
		$pattern = '/\[gallery .*?(?=' . $param . ')' . $param . '=.([^\']+)./si';
		preg_match($pattern, $shortcode, $out);

		$out = isset($out[1]) ? explode('"', $out[1]) : array('');
		return $out[0];
	}

	/**
	 * Reset builder query
	 * @param $action
	 */
	function reset_builder_query($action = 'reset') {
		if ('reset' === $action) {
			remove_filter('the_content', array(&$this, 'builder_show_on_front'), 11);
		} elseif ('restore' === $action) {
			add_filter('the_content', array(&$this, 'builder_show_on_front'), 11);
		}
	}

	/**
	 * Check whether image script is in use or not
	 * @return boolean
	 */
	function is_img_php_disabled() {
		return themify_check('setting-img_settings_use'); // Themify FW setting name
	}

	/**
	 * Checks whether the url is an img link or not.
	 * @param string $url
	 * @return bool
	 */
	function is_img_link($url) {
		$parsed_url = parse_url($url);
		$pathinfo = isset($parsed_url['path']) ? pathinfo($parsed_url['path']) : '';
		$extension = isset($pathinfo['extension']) ? strtolower($pathinfo['extension']) : '';
		$image_extensions = array('png', 'jpg', 'jpeg', 'gif');
		return in_array( $extension, $image_extensions );
	}

	/**
	 * Get query page
	 */
	function get_paged_query() {
		global $wp;
		$page = 1;
		$qpaged = get_query_var('paged');
		if (!empty($qpaged)) {
			$page = $qpaged;
		} else {
			$qpaged = wp_parse_args($wp->matched_query);
			if (isset($qpaged['paged']) && $qpaged['paged'] > 0) {
				$page = $qpaged['paged'];
			}
		}
		return $page;
	}

	/**
	 * Returns page navigation
	 * @param string Markup to show before pagination links
	 * @param string Markup to show after pagination links
	 * @param object WordPress query object to use
	 * @param original_offset number of posts configured to skip over
	 * @return string
	 */
	function get_pagenav( $before = '', $after = '', $query = false, $original_offset = 0 ) {
		global  $wp_query;

		if (false == $query) {
			$query = $wp_query;
		}

		$paged = intval($this->get_paged_query());
		$numposts = $query->found_posts;

		// $query->found_posts does not take offset into account, we need to manually adjust that
		if( (int) $original_offset ) {
			$numposts = $numposts - (int) $original_offset;
		}

		$max_page = ceil( $numposts / $query->query_vars['posts_per_page'] );
		$out = '';

		if (empty($paged) || $paged == 0) {
			$paged = 1;
		}
		$pages_to_show = apply_filters('themify_filter_pages_to_show', 5);
		$pages_to_show_minus_1 = $pages_to_show - 1;
		$half_page_start = floor($pages_to_show_minus_1 / 2);
		$half_page_end = ceil($pages_to_show_minus_1 / 2);
		$start_page = $paged - $half_page_start;
		if ($start_page <= 0) {
			$start_page = 1;
		}
		$end_page = $paged + $half_page_end;
		if (($end_page - $start_page) != $pages_to_show_minus_1) {
			$end_page = $start_page + $pages_to_show_minus_1;
		}
		if ($end_page > $max_page) {
			$start_page = $max_page - $pages_to_show_minus_1;
			$end_page = $max_page;
		}
		if ($start_page <= 0) {
			$start_page = 1;
		}

		if ($max_page > 1) {
			$out .= $before . '<div class="pagenav clearfix">';
			if ($start_page >= 2 && $pages_to_show < $max_page) {
				$first_page_text = "&laquo;";
				$out .= '<a href="' . esc_url(get_pagenum_link()) . '" title="' . esc_attr($first_page_text) . '" class="number">' . $first_page_text . '</a>';
			}
			if ($pages_to_show < $max_page)
				$out .= get_previous_posts_link('&lt;');
			for ($i = $start_page; $i <= $end_page; $i++) {
				if ($i == $paged) {
					$out .= ' <span class="number current">' . $i . '</span> ';
				} else {
					$out .= ' <a href="' . esc_url(get_pagenum_link($i)) . '" class="number">' . $i . '</a> ';
				}
			}
			if ($pages_to_show < $max_page)
				$out .= get_next_posts_link('&gt;');
			if ($end_page < $max_page) {
				$last_page_text = "&raquo;";
				$out .= '<a href="' . esc_url(get_pagenum_link($max_page)) . '" title="' . esc_attr($last_page_text) . '" class="number">' . $last_page_text . '</a>';
			}
			$out .= '</div>' . $after;
		}
		return $out;
	}

	/**
	 * Check is plugin active
	 */
	function builder_is_plugin_active($plugin) {
		return in_array($plugin, apply_filters('active_plugins', get_option('active_plugins')));
	}

	/**
	 * Include builder in search
	 * @param string $where 
	 * @param string $query
	 * @return string
	 */
	function do_search($where, $wp_query) {
	 
		if (!is_admin() && $wp_query->is_search() && $wp_query->is_main_query()) {
			global $wpdb;
			$query = get_search_query();
			if (method_exists($wpdb, 'esc_like')) {
				$query = $wpdb->esc_like($query);
			} else {
				/**
				 * If this is not WP 4.0 or above, use old method to escape db query.
				 * @since 2.0.2
				 */
				$do = 'like';
				$it = 'escape';
				$query = call_user_func($do . '_' . $it, $query);
			}
			$types = Themify_Builder_Model::get_post_types();

			$where .= " OR {$wpdb->posts}.ID IN (
												SELECT {$wpdb->postmeta}.post_id FROM {$wpdb->posts}, {$wpdb->postmeta}";

			global $sitepress;
			if (isset($sitepress) && method_exists($sitepress, 'get_current_language')) {
				$current_language = $sitepress->get_current_language();
				$where .= " LEFT JOIN {$wpdb->prefix}icl_translations ON( {$wpdb->prefix}icl_translations.element_id = {$wpdb->postmeta}.post_id )
												WHERE {$wpdb->prefix}icl_translations.language_code = '$current_language'
												AND";
			} else {
				$where .= ' WHERE'; // if WPML doesn't exist, execution enters this branch and is needed for proper query
			}

			$where .= " {$wpdb->postmeta}.meta_key = '_themify_builder_settings_json' AND `post_status`='publish'
												AND {$wpdb->postmeta}.meta_value LIKE '%$query%' AND {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id
												AND {$wpdb->posts}.post_type IN ('" . implode("', '", $types) . "'))";
		}
		return $where;
	}

	/**
	 * Builder Import Lightbox
	 */
	function builder_import_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'nonce');

		$type = $_POST['type'];
		$data = array();

		if ('post' === $type) {
			$post_types = get_post_types(array('_builtin' => false, 'public' => true));
			$data[] = array(
				'post_type' => 'post',
				'label' => __('Post', 'themify'),
				'items' => get_posts(array('posts_per_page' => -1, 'post_type' => 'post'))
			);
			foreach ($post_types as $post_type) {
				$data[] = array(
					'post_type' => $post_type,
					'label' => ucfirst($post_type),
					'items' => get_posts(array('posts_per_page' => -1, 'post_type' => $post_type))
				);
			}
		} else if ('page' === $type) {
			$data[] = array(
				'post_type' => 'page',
				'label' => __('Page', 'themify'),
				'items' => get_pages()
			);
		} else {
			die();
		}

		include_once THEMIFY_BUILDER_INCLUDES_DIR . '/themify-builder-import.php';
		die();
	}

	/**
	 * Process import builder
	 */
	function builder_import_submit_ajaxify() {
		check_ajax_referer('tfb_load_nonce', 'nonce');
		$import_to = (int) $_POST['importTo'];
		if( ! current_user_can( 'edit_post', $import_to ) ) {
			die;
		}

		parse_str($_POST['data'], $imports);
		$import_type = $_POST['importType'];

		if (is_array($imports) && !empty($imports)) {
			$meta_values = array();

			if ( 'append' === $import_type ) {
				// get current page builder data
				$meta_values[] = $this->get_builder_data($import_to);
			}

			foreach ($imports as $post_id) {
				if (!empty($post_id)){
					$builder_data = $this->get_builder_data($post_id);
					$meta_values[] = $builder_data;
				}
			}

			if (!empty($meta_values)) {
				$result = array();
				foreach ($meta_values as $meta) {
					$result = array_merge($result, (array) $meta);
				}

				$GLOBALS['ThemifyBuilder_Data_Manager']->save_data( $result, $import_to );
			}
		}

		die();
	}

	/**
	 * Get google fonts
	 */
	function get_custom_google_fonts() {
		global $themify;
		$fonts = array();
		if (!empty($themify->builder_google_fonts)){
                        $themify->builder_google_fonts = substr($themify->builder_google_fonts, 0, -1);
                        $fonts = explode('|', $themify->builder_google_fonts);
                }
		return $fonts;
	}

	/**
	 * Add custom Themify Builder button after Add Media btn
	 * @param string $context 
	 * @return string
	 */
	function add_custom_switch_btn($context) {
		global $pagenow;
		$post_types = themify_post_types();
		if ('post.php' === $pagenow && in_array(get_post_type(), $post_types)) {
			$context .= sprintf('<a href="#" class="button themify_builder_switch_btn">%s</a>', __('Themify Builder', 'themify'));
		}
		return $context;
	}

	/**
	 * Check if the given array is an empty Builder row (no modules AND no styling settings)
	 *
	 * @return bool
	 */
	function is_empty_row( $row ) {
		if ( ( isset( $row['cols'] ) && empty( $row['cols'] ) && ! isset( $row['styling'] ) )
			|| ( isset( $row['cols'] ) && count( $row['cols'] ) == 1 && empty( $row['cols'][0]['modules'] ) && empty( $row['cols'][0]['styling'] ) && ( ! isset( $row['styling'] ) || empty( $row['styling'] ) ) ) // there's only one column and it's empty
		) {
			return true;
		}

		return false;
	}

	/**
	 * Get template row
	 *
	 * @param array  $rows
	 * @param array  $row
	 * @param string $builder_id
	 * @param bool   $echo
	 *
	 * @return string
	 */
	public function get_template_row($rows, $row, $builder_id, $echo = false, $frontedit_active = null) {
		/* allow addons to control the display of the rows */
		$display = apply_filters('themify_builder_row_display', true, $row, $builder_id);
		if (false === $display) {
			return false;
		}

		/* ensure $row is a valid Builder row */
		if ( ! ( isset( $row['row_order'] ) || isset( $row['cols'] ) || isset( $row['styling'] ) ) ) {
			return '';
		}

		if (null === $frontedit_active) {
			$frontedit_active = self::$frontedit_active;
		}

		if ( !$frontedit_active && ( isset( $row['styling']['visibility_all'] ) && $row['styling']['visibility_all'] == 'hide_all' ) ) {
			return '';
		}

		// prevent empty rows from being rendered
		if ( ! $frontedit_active ) {
			if( $this->is_empty_row( $row ) ) {
				return '';
			}
		}

		$row['row_order'] = isset($row['row_order']) ? $row['row_order'] : uniqid();
		$row_classes = array('themify_builder_row', 'themify_builder_' . $builder_id . '_row', 'module_row', 'module_row_' . $row['row_order'], 'module_row_' . $builder_id . '-' . $row['row_order'], 'clearfix');
		$class_fields = array('custom_css_row', 'background_repeat', 'animation_effect', 'row_height');
		$row_gutter_class =!empty($row['gutter']) ? $row['gutter'] : 'gutter-default';
		$row_column_alignment = !empty($row['column_alignment']) ? $row['column_alignment'] : '';
		$row_anchor = !empty($row['styling']['row_anchor']) ? " #" . $row['styling']['row_anchor'] : '';
		$col_desktop_dir = !empty($row['desktop_dir']) ? $row['desktop_dir'] : 'ltr';
		$col_tablet_dir = !empty($row['tablet_dir']) ? $row['tablet_dir'] : 'ltr';
		$col_mobile_dir = !empty($row['mobile_dir']) ? $row['mobile_dir'] : 'ltr';
		$col_mobile = !empty($row['col_mobile']) && $row['col_mobile']!=='mobile-auto' ? $row['col_mobile'] : false;
		$col_tablet = !empty($row['col_tablet']) && $row['col_tablet']!=='tablet-auto' ? $row['col_tablet'] : false;

		// Set Gutter Class
		if ('' != $row_gutter_class)
			$row_classes[] = $row_gutter_class;

		// Set column alignment
		if ('' != $row_column_alignment) {
			$row_classes[] = $row_column_alignment;
		}

		// Class for Scroll Highlight
		if (isset($row['styling']) && isset($row['styling']['row_anchor']) && '' != $row['styling']['row_anchor']) {
			$row_classes[] = 'tb_section-' . $row['styling']['row_anchor'];
		}

		// @backward-compatibility
		if (!isset($row['styling']['background_type']) && !empty($row['styling']['background_video'])) {
			$row['styling']['background_type'] = 'video';
		} elseif(isset($row['styling']['background_type']) && $row['styling']['background_type']==='image' && isset($row['styling']['background_zoom']) && $row['styling']['background_zoom']==='zoom' && $row['styling']['background_repeat']=='repeat-none'){
			$row_classes[] = 'themify-bg-zoom';
		}

		foreach ($class_fields as $field) {
			if (!empty($row['styling'][$field])) {
				if ( Themify_Builder_Model::is_animation_active() && 'animation_effect' === $field) {
					$row_classes[] = 'wow';
				}
				$row_classes[] = $row['styling'][$field];
			}
		}

		if (!empty($row['styling']['animation_effect_delay']) && Themify_Builder_Model::is_animation_active() ) {
			$row_classes[] = 'animation_effect_delay_' . $row['styling']['animation_effect_delay'];
		}

		if (!empty($row['styling']['animation_effect_repeat']) && Themify_Builder_Model::is_animation_active() ) {
			$row_classes[] = 'animation_effect_repeat_' . $row['styling']['animation_effect_repeat'];
		}

		if (!empty($row['styling']['background_image'])  && !empty($row['styling']['background_position'])) {
			$row_classes[] = 'bg-position-' . $row['styling']['background_position'];
		}

		/**
		 * Row Width class
		 * To provide backward compatibility, the CSS classname and the option label do not match. See #5284
		 */
		if( isset( $row['styling']['row_width'] ) ) {
			if( 'fullwidth' == $row['styling']['row_width'] ) {
				$row_classes[] = 'fullwidth_row_container';
			} elseif( 'fullwidth-content' == $row['styling']['row_width'] ) {
				$row_classes[] = 'fullwidth';
			}
		}

		$row_classes = apply_filters('themify_builder_row_classes', $row_classes, $row, $builder_id);
		$row_attributes = apply_filters( 'themify_builder_row_attributes', array(
			'data-gutter' => $row_gutter_class,
			'class' => implode(' ', $row_classes),
			'data-column-alignment' => $row_column_alignment,
						'data-desktop_dir'=>$col_desktop_dir,
						'data-tablet_dir'=>$col_tablet_dir,
						'data-mobile_dir'=>$col_mobile_dir
		), isset( $row['styling'] ) ? $row['styling'] : array() );
				
		if ($row_anchor) {
                    $row_attributes['data-anchor'] = $row['styling']['row_anchor'];
		}

		// background video
                $video_data = isset($row['styling']) && Themify_Builder_Model::is_premium()?Themify_Builder_Include::get_video_background($row['styling']):'';
		if ( ! $echo ) {
			$output = PHP_EOL; // add line break
			ob_start();
		}
				$count = isset($row['cols'])?count($row['cols']):0;
				
				if(!$frontedit_active){
					$row_content_classes = array($row_gutter_class);
					$row_content_classes[] = 'tablet-col-direction-'.$col_tablet_dir;
					$row_content_classes[] = 'desktop-col-direction-'.$col_desktop_dir;
					$row_content_classes[] = 'mobile-col-direction-'.$col_mobile_dir;
					if($col_tablet){
						$row_content_classes[] = $col_tablet;
						$rcols = $col_tablet==='tablet3-1'?3:($col_tablet==='tablet4-2'?2:substr_count($col_tablet,'-')-1);
						if($rcols===2 || $rcols===3){
							 $row_content_classes[] = 'tablet-'.($rcols===2?'2col':'3col');
						}
					}
					if($col_mobile){
						$row_content_classes[] = $col_mobile;
						$rcols =  $col_mobile==='mobile3-1'?3:($col_mobile==='mobile4-2'?2:substr_count($col_mobile,'-')-1);
						if($rcols===2 || $rcols===3){
							 $row_content_classes[] = 'mobile-'.($rcols===2?'2col':'3col');
						}
					}
					elseif($col_tablet && !empty($row['col_mobile']) && $row['col_mobile']==='mobile-auto'){
							$row_content_classes[] = $row['col_mobile'];
					}
					if($col_mobile || $col_tablet){
						$row_content_classes[] = 'tfb_grid_classes col-count-'.$count.' count-'.(($count%2)===0?'even':'odd');
					}
					$row_content_classes = implode(' ',$row_content_classes);
					
				}
		?>
			<!-- module_row -->
			<div <?php echo $video_data; echo self::get_element_attributes( $row_attributes ); ?>>

		<?php if ($frontedit_active): ?>
					<div class="themify_builder_row_top">

			<?php themify_builder_grid_lists('row', $row_gutter_class, $row_column_alignment, $row_anchor); ?>

						<ul class="row_action">
							<li><a href="#" data-title="<?php _e('Export', 'themify') ?>" class="themify_builder_export_component"
								   data-component="row" rel="themify-tooltip-bottom">
									<span class="ti-export"></span>
								</a></li>
							<li><a href="#" data-title="<?php _e('Import', 'themify') ?>" class="themify_builder_import_component"
								   data-component="row" rel="themify-tooltip-bottom">
									<span class="ti-import"></span>
								</a></li>
							<li class="separator"></li>
							<li><a href="#" data-title="<?php _e('Copy', 'themify') ?>" class="themify_builder_copy_component"
								   data-component="row" rel="themify-tooltip-bottom">
									<span class="ti-files"></span>
								</a></li>
							<li><a href="#" data-title="<?php _e('Paste', 'themify') ?>" class="themify_builder_paste_component"
								   data-component="row" rel="themify-tooltip-bottom">
									<span class="ti-clipboard"></span>
								</a></li>
							<li class="separator"></li>
							<li><a href="#" data-title="<?php _e('Options', 'themify') ?>" class="themify_builder_option_row"
								   rel="themify-tooltip-bottom">
									<span class="ti-pencil"></span>
								</a></li>
							<li><a href="#" data-title="<?php _e('Styling', 'themify') ?>" class="themify_builder_style_row"
								   rel="themify-tooltip-bottom">
									<span class="ti-brush"></span>
								</a></li>
							<li><a href="#" data-title="<?php _e('Duplicate', 'themify') ?>" class="themify_builder_duplicate_row"
								   rel="themify-tooltip-bottom">
									<span class="ti-layers"></span>
								</a></li>
							<li><a href="#" data-title="<?php _e('Delete', 'themify') ?>" class="themify_builder_delete_row"
								   rel="themify-tooltip-bottom">
									<span class="ti-close"></span>
								</a></li>
							<li class="separator"></li>
							<li><a href="#" data-title="<?php _e('Toggle Row', 'themify') ?>" class="themify_builder_toggle_row">
									<span class="ti-angle-up"></span>
								</a></li>
						</ul>
					</div>
					<!-- /row_top -->
		<?php endif; // builder edit active     ?>

				<?php
				if (isset($row['styling'])) {
									do_action('themify_builder_background_styling',$row,$row['row_order'],'row');	
				}
				?>

				<div class="row_inner_wrapper">
					<div class="row_inner <?php echo !$frontedit_active?$row_content_classes:''?>">

						<?php do_action('themify_builder_row_start', $builder_id, $row); ?>

						<?php if ($frontedit_active): ?>
							<div class="themify_builder_row_content">
						<?php endif; // builder edit active    ?>

							<?php
							if ($count > 0):


								switch ($count) {

									case 6:
										$order_classes = array('first', 'second', 'third', 'fourth', 'fifth', 'last');
										break;

									case 5:
										$order_classes = array('first', 'second', 'third', 'fourth', 'last');
										break;

									case 4:
										$order_classes = array('first', 'second', 'third', 'last');
										break;

									case 3:
										$order_classes = array('first', 'middle', 'last');
										break;

									case 2:
										$order_classes = array('first', 'last');
										break;

									default:
										$order_classes = array('first');
										break;
								}
                                                                if(!$frontedit_active && (($col_mobile_dir==='rtl' && themify_is_touch('phone')) || ($col_tablet_dir==='rtl' && themify_is_touch('tablet')) || ($col_desktop_dir==='rtl' && !themify_is_touch()))){
                                                                        $order_classes = array_reverse($order_classes);
                                                                 }
								foreach ($row['cols'] as $cols => $col):
									$this->get_template_column( $rows, $row, $cols, $col, $builder_id, $order_classes, true, $frontedit_active );
								endforeach; ?>

								<?php else: ?>

									<div class="themify_builder_col col-full first last">
										<?php if ($frontedit_active): ?>
											<div class="themify_module_holder">
												<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
											<?php endif; ?>

											<?php
											if (!$frontedit_active) {
												echo '&nbsp;'; // output empty space
											}
											?>

											<?php if ($frontedit_active): ?>
											</div>
											<!-- /module_holder -->
											<?php endif; ?>
									</div>
									<!-- /col -->

								<?php endif; // end col loop     ?>

								<?php if ($frontedit_active): ?>
								</div> <!-- /themify_builder_row_content -->

								<?php 
                                                                    $row_data_styling = array();
                                                                    if(isset($row['styling'])){
                                                                            $row_data_styling = Themify_Builder_Model::remove_empty_fields($row['styling']);
                                                                    }
								?>
								<div class="row-data-styling" data-styling="<?php echo esc_attr(json_encode($row_data_styling)); ?>"></div>
							<?php endif; ?>

							<?php do_action('themify_builder_row_end', $builder_id, $row); ?>

						</div>
						<!-- /row_inner -->
					</div>
					<!-- /row_inner_wrapper -->
				</div>
				<!-- /module_row -->
		<?php

		if ( ! $echo ) {
			$output .= ob_get_clean();
			// add line break
			$output .= PHP_EOL;
			return $output;
		}
	}

	/**
	 * Get template column.
	 * 
	 * @param int $rows Row key
	 * @param array $row 
	 * @param array $cols 
	 * @param array $col 
	 * @param string $builder_id 
	 */
	public function get_template_column( $rows, $row, $cols, $col, $builder_id, $order_classes = array(), $echo = false, $frontedit_active = null ) {
		if ( ! isset( $order_classes[ $cols ] ) ) $order_classes[ $cols ] = '';

		if (null === $frontedit_active) {
			$frontedit_active = self::$frontedit_active;
		}

		$column_count = isset( $row['cols'] ) ? count( $row['cols'] ) : 0;
		$grid_class = explode( ' ', $col['grid_class'] );
		$dynamic_class = array();
		$dynamic_class[0] = $frontedit_active ? 'themify_builder_col' : $order_classes[$cols];
		$dynamic_class[1] = $frontedit_active ? '' : 'tb-column';
		$dynamic_class[2] = ( isset($col['modules']) && count($col['modules']) > 0 ) ? '' : 'empty-column';
		$dynamic_class[3] = 'module_column tb_' . $builder_id . '_column'; // who's your daddy?

		if (isset($col['column_order'])) {
			$dynamic_class[] = 'module_column_' . $col['column_order'];
			$dynamic_class[] = 'module_column_' . $builder_id . '-' . $row['row_order'] . '-' . $col['column_order'];
		}

		if ( $column_count>1 && (($key = array_search('last', $grid_class) ) !== false || ( $key = array_search( 'first', $grid_class ) ) !== false) ) {
			unset($grid_class[$key]);
		}

		if (!empty($col['styling']['background_repeat'])) {
			$dynamic_class[] = $col['styling']['background_repeat'];
		}

		$print_column_classes = array_unique(array_merge($grid_class,$dynamic_class));
		// remove class "last" if the column is fullwidth
		if (1 == $column_count && ( $key = array_search('last', $print_column_classes) ) !== false ) {
			unset($print_column_classes[$key]);
		}

		if ( !empty($col['styling']['background_image']) ) {
			$print_column_classes[] = 'bg-position-' . ( empty( $col['styling']['background_position'] ) ? 'left-top' : $col['styling']['background_position'] );
		}

		if (isset($col['styling']['background_type']) && $col['styling']['background_type']==='image' && isset($col['styling']['background_zoom']) && $col['styling']['background_zoom']==='zoom' && $col['styling']['background_repeat']=='repeat-none') {
			$print_column_classes[] = 'themify-bg-zoom';
		}

		if (!empty($col['styling']['custom_css_column'])) {
			$print_column_classes[] = $col['styling']['custom_css_column'];
		}

		$print_column_classes = implode(' ', $print_column_classes);

		// background video
		$video_data = isset($col['styling']) && Themify_Builder_Model::is_premium()
			? Themify_Builder_Include::get_video_background( $col['styling'] ) : '';

		if ( ! $echo ) {
			$output = PHP_EOL; // add line break
			ob_start();
		}

		// Start Column Render ######
		?>

		<div <?php if(Themify_Builder_Model::is_frontend_editor_page() && !empty($col['grid_width'])):?>style="width:<?php echo $col['grid_width']?>%;"<?php endif;?> class="<?php  echo esc_attr($print_column_classes); ?>" <?php echo $video_data; ?>>

			<?php
			if (isset($col['styling'])) {
				$column_order = $row['row_order'] . '-' . $col['column_order'];
				do_action('themify_builder_background_styling',$col,$column_order,'col');	
			}
			?>

			<?php if ($frontedit_active) : ?>
								<div class="themify_grid_drag themify_drag_right"></div>
								<div class="themify_grid_drag themify_drag_left"></div>
				<ul class="themify_builder_column_action">
					<li><a href="#" class="themify_builder_option_column" data-title="<?php esc_html_e( 'Styling', 'themify' );?>" rel="themify-tooltip-bottom"><span class="ti-brush"></span></a></li>
					<li class="separator"></li>
					<li><a href="#" class="themify_builder_export_component" data-title="<?php esc_html_e( 'Export', 'themify' );?>" rel="themify-tooltip-bottom" data-component="column"><span class="ti-export"></span></a></li>
					<li><a href="#" class="themify_builder_import_component" data-title="<?php esc_html_e( 'Import', 'themify' );?>" rel="themify-tooltip-bottom" data-component="column"><span class="ti-import"></span></a></li>
					<li class="separator"></li>
					<li><a href="#" class="themify_builder_copy_component" data-title="<?php esc_html_e( 'Copy', 'themify' );?>" rel="themify-tooltip-bottom" data-component="column"><span class="ti-files"></span></a></li>
					<li><a href="#" class="themify_builder_paste_component" data-title="<?php esc_html_e( 'Paste', 'themify' );?>" rel="themify-tooltip-bottom" data-component="column"><span class="ti-clipboard"></span></a></li>
					<li class="separator last-sep"></li>
					<li class="themify_builder_column_dragger_li"><a href="#" class="themify_builder_column_dragger"><span class="ti-arrows-horizontal"></span></a></li>
				</ul>
			<?php endif; ?>

			<div class="tb-column-inner">

				<?php do_action('themify_builder_column_start', $builder_id, $row, $col); ?>

				<?php if ($frontedit_active): ?>
					<div class="themify_module_holder">
						<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
				<?php endif; ?>

					<?php
					if (!empty($col['modules'])) {

						foreach ($col['modules'] as $modules => $mod) {

							if (isset($mod['mod_name'])) {
								$w_class =  $frontedit_active  ? 'r' . $rows . 'c' . $cols . 'm' . $modules : '';
								$identifier = array($rows, $cols, $modules); // define module id
								$this->get_template_module($mod, $builder_id, true, $frontedit_active, $w_class, $identifier);
							}

							// Check for Sub-rows
							if (!empty($mod['cols'])) {
								$this->get_template_sub_row( $rows, $cols, $modules, $mod, $builder_id, true, $frontedit_active );	
							}
						}
					} elseif (!$frontedit_active) {
						echo '&nbsp;'; // output empty space
					}
					?>

				<?php if ( $frontedit_active ): ?>
					</div><!-- /themify_module_holder -->
				<?php endif; ?>

			</div><!-- /.tb-column-inner -->
			<?php if ($frontedit_active): 
                            $column_data_styling = array();
                            if(isset($col['styling'])){
                                    $column_data_styling = Themify_Builder_Model::remove_empty_fields($col['styling']);
                            }
			?>
                            <div class="column-data-styling" data-styling="<?php echo esc_attr(json_encode($column_data_styling)); ?>"></div>
			<?php endif; ?>
		</div>
		<!-- /.tb-column -->
		
		<?php
		// End Column Render ######

		if ( ! $echo ) {
			$output .= ob_get_clean();
			// add line break
			$output .= PHP_EOL;
			return $output;
		}
	}

	/**
	 * Get template Sub-Row.
	 * 
	 * @param int $rows 
	 * @param int $cols 
	 * @param int $modules 
	 * @param array $mod 
	 * @param string $builder_id 
	 * @param boolean $echo 
	 * @param boolean $frontedit_active 
	 */
	public function get_template_sub_row( $rows, $cols, $modules, $mod, $builder_id, $echo = false, $frontedit_active = null ) {
		if (null === $frontedit_active) {
                        $frontedit_active = self::$frontedit_active;
                }
		$print_sub_row_classes = array('module_subrow sub_row_' . $rows . '-' . $cols . '-' . $modules);
		$sub_row_gutter = !empty($mod['gutter']) ? $mod['gutter'] : 'gutter-default';
		$print_sub_row_classes[] = $sub_row_column_alignment = !empty($mod['column_alignment']) ? $mod['column_alignment'] : '';
		if (!empty($mod['styling']['background_repeat'])) {
			$print_sub_row_classes[] = $mod['styling']['background_repeat'];
		}
		if (!empty($mod['styling']['background_image']) && !empty($mod['styling']['background_position'])) {
			$print_sub_row_classes[] = 'bg-position-' . $mod['styling']['background_position'];
		}
		if(isset($mod['styling']['background_type']) && $mod['styling']['background_type']==='image' && isset($mod['styling']['background_zoom']) && $mod['styling']['background_zoom']==='zoom' && $mod['styling']['background_repeat']=='repeat-none'){
						$print_sub_row_classes[] = 'themify-bg-zoom';
		}
		if (!empty($mod['styling']['custom_css_column'])) {
			$print_sub_row_classes[] = $mod['styling']['custom_css_column'];
		}
		
		$sub_row_attr = $frontedit_active ? 'data-gutter="' . esc_attr($sub_row_gutter) . '"' : '';
		$sub_row_column_alignment_data = $frontedit_active ? 'data-column-alignment="' .esc_attr($sub_row_column_alignment) . '"' : '';
                $col_desktop_dir = !empty($mod['desktop_dir']) ? $mod['desktop_dir'] : 'ltr';
                $col_tablet_dir =  !empty($mod['tablet_dir']) ? $mod['tablet_dir'] : 'ltr';
                $col_mobile_dir = !empty($mod['mobile_dir']) ? $mod['mobile_dir'] : 'ltr';
                $col_mobile = !empty($mod['col_mobile'])  && $mod['col_mobile']!=='mobile-auto'? $mod['col_mobile'] :false;
                $col_tablet = !empty($mod['col_tablet']) && $mod['col_tablet']!=='tablet-auto'? $mod['col_tablet'] : false;;

                if(!$frontedit_active){
                        $row_content_classes = array($sub_row_gutter);
                        $row_content_classes[] = 'tablet-col-direction-'.$col_tablet_dir;
                        $row_content_classes[] = 'desktop-col-direction-'.$col_desktop_dir;
                        $row_content_classes[] = 'mobile-col-direction-'.$col_mobile_dir;
                        if($col_tablet){
                                $row_content_classes[] = $col_tablet;
                                $rcols =  $col_tablet==='tablet3-1'?3:($col_tablet==='tablet4-2'?2:substr_count($col_tablet,'-')-1);
                                if($rcols===2 || $rcols===3){
                                         $row_content_classes[] = 'tablet-'.($rcols===2?'2col':'3col');
                                }
                        }
                        if($col_mobile){
                                $row_content_classes[] = $col_mobile;
                                $rcols =  $col_mobile==='mobile3-1'?3:($col_mobile==='mobile4-2'?2:substr_count($col_mobile,'-')-1);
                                if($rcols===2 || $rcols===3){
                                         $row_content_classes[] = 'mobile-'.($rcols===2?'2col':'3col');
                                }
                        }
                        elseif($col_tablet && !empty($mod['col_mobile']) && $mod['col_mobile']==='mobile-auto'){
                                        $row_content_classes[] = $mod['col_mobile'];
                        }
                        if($col_mobile || $col_tablet){
                                $count = count($mod['cols']);
                                $row_content_classes[] = 'tfb_grid_classes col-count-'.$count.' count-'.(($count%2)===0?'even':'odd');
                        }
                        $row_content_classes = implode(' ',$row_content_classes);
                }
                else{
                        $sub_row_attr.= ' data-desktop_dir="' . $col_desktop_dir . '" data-tablet_dir="' . $col_tablet_dir . '" data-mobile_dir="' . $col_mobile_dir . '"'; 
                }
		$print_sub_row_classes = implode(' ', $print_sub_row_classes);
		
		// background video
                $video_data = isset($mod['styling']) && Themify_Builder_Model::is_premium()?Themify_Builder_Include::get_video_background($mod['styling']):'';

		if ( ! $echo ) {
			$output = PHP_EOL; // add line break
			ob_start();
		}

		// Start Sub-Row Render ######
		?>
		<div class="themify_builder_sub_row clearfix <?php echo esc_attr($print_sub_row_classes)?>"<?php echo $sub_row_attr?> <?php echo $sub_row_column_alignment_data?> <?php echo $video_data?>>
		<?php
			if (isset($mod['styling'])) {
				$mod['row_order'] = $modules;
				$sub_row_order = $rows . '-' . $cols . '-' . $modules;
				do_action('themify_builder_background_styling',$mod,$sub_row_order,'sub_row');
				do_action('themify_builder_sub_row_start', $builder_id, $rows, $cols, $mod);
			}
		?>
		
		<?php if ($frontedit_active): ?>
			<div class="themify_builder_sub_row_top">
			<?php themify_builder_grid_lists('sub_row', $sub_row_gutter, $sub_row_column_alignment); ?>
				<ul class="sub_row_action">
					<li><a href="#" data-title="<?php _e('Export', 'themify') ?>" rel="themify-tooltip-bottom"
						   class="themify_builder_export_component" data-component="sub-row">
							<span class="ti-export"></span>
						</a></li>
					<li><a href="#" data-title="<?php _e('Import', 'themify') ?>" rel="themify-tooltip-bottom"
						   class="themify_builder_import_component" data-component="sub-row">
							<span class="ti-import"></span>
						</a></li>
					<li class="separator"></li>
					<li><a href="#" data-title="<?php _e('Copy', 'themify') ?>" rel="themify-tooltip-bottom"
						   class="themify_builder_copy_component" data-component="sub-row">
							<span class="ti-files"></span>
						</a></li>
					<li><a href="#" data-title="<?php _e('Paste', 'themify') ?>" rel="themify-tooltip-bottom"
						   class="themify_builder_paste_component" data-component="sub-row">
							<span class="ti-clipboard"></span>
						</a></li>
					<li class="separator"></li>
					<li><a href="#" data-title="<?php _e('Styling', 'themify') ?>" class="themify_builder_style_subrow"
						   rel="themify-tooltip-bottom">
							<span class="ti-brush"></span>
						</a></li>
					<li><a href="#" data-title="<?php _e('Duplicate', 'themify') ?>" rel="themify-tooltip-bottom"
						   class="sub_row_duplicate">
							<span class="ti-layers"></span>
						</a></li>
					<li><a href="#" data-title="<?php _e('Delete', 'themify') ?>" rel="themify-tooltip-bottom"
						   class="sub_row_delete">
							<span class="ti-close"></span>
						</a></li>
				</ul>
			</div>
		<?php endif; ?>
		<div class="sub_row_inner_wrapper <?php echo !$frontedit_active?$row_content_classes:''?>">
		<?php if ($frontedit_active): ?>
			<div class="themify_builder_sub_row_content">
		<?php elseif(($col_mobile_dir==='rtl' && themify_is_touch('phone')) || ($col_tablet_dir==='rtl' && themify_is_touch('tablet')) || ($col_desktop_dir==='rtl' && !themify_is_touch())):?>
		<?php 
			if ( isset( $mod['cols'] ) ) {
				$first = key($mod['cols']);
				$last = count($mod['cols'])-1;
				$mod['cols'][$first]['grid_class'] = str_replace('first','last',$mod['cols'][$first]['grid_class']);
				$mod['cols'][$last]['grid_class'] = str_replace('last','first',$mod['cols'][$last]['grid_class']);
			}
		?>
		<?php endif; ?>

		<?php
		$should_render = ( $frontedit_active || strpos($row_content_classes, 'hide_all') === false);
		if (! empty( $mod['cols'] ) && $should_render ) {
			foreach ($mod['cols'] as $col_key => $sub_col) {
				$this->get_template_sub_column( $rows, $cols, $modules, $col_key, $sub_col, $builder_id, true, $frontedit_active );
			}
		}
		if ($frontedit_active) {
			echo '</div><!-- /themify_builder_sub_row_content -->';
		}
		
		echo "</div>";
		
		if ($frontedit_active) {
			$subrow_data_styling = array();
			if(isset($mod['styling'])){
				$subrow_data_styling = Themify_Builder_Model::remove_empty_fields($mod['styling']);
			}
			echo '<div class="subrow-data-styling" data-styling="'.esc_attr(json_encode($subrow_data_styling)).'"></div>';
		}

		echo '</div><!-- /themify_builder_sub_row -->';

		// End Sub-Row Render ######

		if ( ! $echo ) {
			$output .= ob_get_clean();
			// add line break
			$output .= PHP_EOL;
			return $output;
		}
	}

	/**
	 * Get template sub-column
	 * @param int|string $rows 
	 * @param int|string $cols 
	 * @param int|string $modules 
	 * @param int $col_key 
	 * @param array $sub_col 
	 * @param string $builder_id 
	 * @param boolean $echo 
	 * @param boolean $frontedit_active
	 */
	public function get_template_sub_column( $rows, $cols, $modules, $col_key, $sub_col, $builder_id, $echo = false, $frontedit_active = null ) {
		if (null === $frontedit_active){
			$frontedit_active = self::$frontedit_active;
                }
		$print_sub_col_classes = array();
		$print_sub_col_classes[] = $frontedit_active ? 'themify_builder_col ' . $sub_col['grid_class'] : $sub_col['grid_class'];
		$print_sub_col_classes[] = 'sub_column module_column sub_column_' . $rows . '-' . $cols . '-' . $modules . '-' . $col_key;
		$print_sub_col_classes[] = "sub_column_post_{$builder_id}";
		$sub_row_class = 'sub_row_' . $rows . '-' . $cols . '-' . $modules;

		if (!empty($sub_col['styling']['background_repeat'])) {
			$print_sub_col_classes[] = $sub_col['styling']['background_repeat'];
		}
		if (!empty($sub_col['styling']['background_image']) && !empty($sub_col['styling']['background_position'])) {
			$print_sub_col_classes[] = 'bg-position-' . $sub_col['styling']['background_position'];
		}
		if (!empty($sub_col['styling']['custom_css_column'])) {
			$print_sub_col_classes[] = $sub_col['styling']['custom_css_column'];
		}
		if(isset($sub_col['styling']['background_type']) && $sub_col['styling']['background_type']==='image' && isset($sub_col['styling']['background_zoom']) && $sub_col['styling']['background_zoom']==='zoom' && $sub_col['styling']['background_repeat']=='repeat-none'){
						$print_sub_col_classes[] = 'themify-bg-zoom';
		}
		$print_sub_col_classes = implode(' ', $print_sub_col_classes);

		// background video
		$video_data = isset($sub_col['styling']) && Themify_Builder_Model::is_premium()?Themify_Builder_Include::get_video_background($sub_col['styling']):'';

		if ( ! $echo ) {
			$output = PHP_EOL; // add line break
			ob_start();
		}
?>
		<div <?php if(Themify_Builder_Model::is_frontend_editor_page() && !empty($sub_col['grid_width'])):?>style="width:<?php echo $sub_col['grid_width']?>%;"<?php endif;?> class="<?php echo esc_attr($print_sub_col_classes)?>" <?php echo $video_data?>>
<?php
	
		if (isset($sub_col['styling'])) {
			$sub_column_order = $rows . '-' . $cols . '-' . $modules . '-' . $col_key;
						do_action('themify_builder_background_styling',$sub_col,$sub_column_order,'sub-col');
		}
		?>

		<div class="tb-column-inner">
		<?php do_action('themify_builder_sub_column_start', $builder_id, $rows, $cols, $modules, $sub_col); ?>

		<?php if ($frontedit_active): ?>
						<div class="themify_grid_drag themify_drag_right"></div>
						<div class="themify_grid_drag themify_drag_left"></div>
			<ul class="themify_builder_column_action">
				<li><a href="#" class="themify_builder_option_column" data-title="<?php esc_html_e( 'Styling', 'themify' );?>" rel="themify-tooltip-bottom"><span class="ti-brush"></span></a></li>
				<li class="separator"></li>
				<li><a href="#" class="themify_builder_export_component" data-title="<?php esc_html_e( 'Export', 'themify' );?>" rel="themify-tooltip-bottom" data-component="sub-column"><span class="ti-export"></span></a></li>
				<li><a href="#" class="themify_builder_import_component" data-title="<?php esc_html_e( 'Import', 'themify' );?>" rel="themify-tooltip-bottom" data-component="sub-column"><span class="ti-import"></span></a></li>
				<li class="separator"></li>
				<li><a href="#" class="themify_builder_copy_component" data-title="<?php esc_html_e( 'Copy', 'themify' );?>" rel="themify-tooltip-bottom" data-component="sub-column"><span class="ti-files"></span></a></li>
				<li><a href="#" class="themify_builder_paste_component" data-title="<?php esc_html_e( 'Paste', 'themify' );?>" rel="themify-tooltip-bottom" data-component="sub-column"><span class="ti-clipboard"></span></a></li>
				<li class="separator last-sep"></li>
				<li class="themify_builder_column_dragger_li"><a href="#" class="themify_builder_column_dragger"><span class="ti-arrows-horizontal"></span></a></li>
			</ul>
			<div class="themify_module_holder">
							<div class="empty_holder_text"><?php _e('drop module here', 'themify') ?></div><!-- /empty module text -->
		<?php endif; ?>
			<?php
			if (!empty($sub_col['modules'])) {
				foreach ($sub_col['modules'] as $sub_module_k => $sub_module) {
					$sw_class = $frontedit_active ? 'r' . $sub_row_class . 'c' . $col_key . 'm' . $sub_module_k : '';
					$sub_identifier = array($sub_row_class, $col_key, $sub_module_k); // define module id
					$this->get_template_module($sub_module, $builder_id, true, $frontedit_active, $sw_class, $sub_identifier);
				}
			}
			?>

			<?php if ($frontedit_active): ?>
                            </div>
			<?php 
                            $sub_column_data_styling = array();
                            if(isset($sub_col['styling'])){
                                    $sub_column_data_styling = Themify_Builder_Model::remove_empty_fields($sub_col['styling']);
                            }
			?>
                            <div class="column-data-styling" data-styling="<?php  echo esc_attr(json_encode($sub_column_data_styling)); ?>"></div>
			<!-- /module_holder -->
			<?php endif; ?>
		</div>
		<?php
		echo '</div><!-- /sub_column -->';

		// End Sub-Column Render ######

		if ( ! $echo ) {
			$output .= ob_get_clean();
			// add line break
			$output .= PHP_EOL;
			return $output;
		}
	}

	/**
	 * Return the correct animation css class name
	 * @param string $effect 
	 * @return string
	 */
	function parse_animation_effect($effect, $mod_settings = null) {
		if (!Themify_Builder_Model::is_animation_active()){
			return '';
				}
		$class = ( '' != $effect && !in_array($effect, array('fade-in', 'fly-in', 'slide-up')) ) ? 'wow ' . $effect : $effect;
		if (!empty($mod_settings['animation_effect_delay'])) {
                    $class .= ' animation_effect_delay_' . $mod_settings['animation_effect_delay'];
		}
		if (!empty($mod_settings['animation_effect_repeat'])) {
                    $class .= ' animation_effect_repeat_' . $mod_settings['animation_effect_repeat'];
		}

		return $class;
	}

	/**
	 * Add classes to post_class
	 * @param string|array $classes 
	 */
	function add_post_class($classes) {
		foreach ((array) $classes as $class) {
			$this->_post_classes[$class] = $class;
		}
	}

	/**
	 * Remove sepecified classnames from post_class
	 * @param string|array $classes 
	 */
	function remove_post_class($classes) {
		foreach ((array) $classes as $class) {
			unset($this->_post_classes[$class]);
		}
	}

	/**
	 * Filter post_class to add the classnames to posts
	 *
	 * @return array
	 */
	function filter_post_class($classes) {
            return array_merge($classes, $this->_post_classes);
	}

	/**
	 * Return whether this is a Themify theme or not.
	 *
	 * @return bool
	 */
	function is_themify_theme() {
		// Check if THEMIFY_BUILDER_VERSION constant is defined.
		if (defined('THEMIFY_BUILDER_VERSION')) {
			// Check if it's defined with an expected value and not something odd.
			if (preg_match('/[1-9].[0-9].[0-9]/', THEMIFY_BUILDER_VERSION)) {
				return false;
			}
		}
		// It's a Themify theme.
		return true;
	}

	function parse_slug_to_ids($slug_string, $post_type = 'post') {
		$slug_arr = explode(',', $slug_string);
		$return = array();
		if (!empty($slug_arr)) {
			foreach ($slug_arr as $slug) {
							$return[] = $this->get_id_by_slug(trim($slug), $post_type);
			}
		}
		return $return;
	}

	function get_id_by_slug($slug, $post_type = 'post') {
		$args = array(
			'name' => $slug,
			'post_type' => $post_type,
			'post_status' => 'publish',
			'numberposts' => 1
		);
		$my_posts = get_posts($args);
		return $my_posts?$my_posts[0]->ID:null;
	}

	/**
	 * Get a list of post types that can be accessed publicly
	 *
	 * does not include attachments, Builder layouts and layout parts,
	 * and also custom post types in Builder that have their own module.
	 *
	 * @return array of key => label pairs
	 */
	function get_public_post_types($exclude_builder_post_types = true) {
		$result = array();
		$post_types = get_post_types(array('public' => true, 'publicly_queryable' => 'true'), 'objects');
		$excluded_types = array('attachment', 'tbuilder_layout', 'tbuilder_layout_part', 'section');
		if ($exclude_builder_post_types) {
			$excluded_types = array_merge($this->builder_cpt, $excluded_types);
		}
		foreach ($post_types as $key => $value) {
			if (!in_array($key, $excluded_types)) {
				$result[$key] = $value->labels->singular_name;
			}
		}

		return apply_filters('builder_get_public_post_types', $result);
	}

	/**
	 * Get a list of taxonomies that can be accessed publicly
	 *
	 * does not include post formats, section categories (used by some themes),
	 * and also custom post types in Builder that have their own module.
	 *
	 * @return array of key => label pairs
	 */
	function get_public_taxonomies($exclude_builder_post_types = true) {
		$result = array();
		$taxonomies = get_taxonomies(array('public' => true), 'objects');
		$excludes = array('post_format', 'section-category');
		if ($exclude_builder_post_types) { // exclude taxonomies from Builder CPTs
			foreach ($this->builder_cpt as $value) {
				$excludes[] = "{$value}-category";
			}
		}
		foreach ($taxonomies as $key => $value) {
			if (!in_array($key, $excludes)) {
				$result[$key] = $value->labels->name;
			}
		}

		return apply_filters('builder_get_public_taxonomies', $result);
	}

	/**
	 * If installation is in debug mode, returns '' to load non-minified scripts and stylesheets.
	 *
	 * @since 1.0.3
	 */
	function minified() {
		return ( defined('WP_DEBUG') && WP_DEBUG ) ? '' : '.min';
	}

	/**
	 * Merge user defined arguments into defaults array
	 *
	 * @return array
	 */
	function parse_args($args, $defaults = '', $filter_key = '') {
		// Setup a temporary array from $args
		if (is_object($args)){
                        $r = get_object_vars($args);
                }
		elseif (is_array($args)){
                        $r = & $args;
                }
		else{
                        wp_parse_str($args, $r);
                }

		// Passively filter the args before the parse
		if (!empty($filter_key)){
                        $r = apply_filters('themify_builder_before_' . $filter_key . '_parse_args', $r);
                }

		// Parse
		if (is_array($defaults)){
                    $r = array_merge($defaults, $r);
                }

		// Aggressively filter the args after the parse
		if (!empty($filter_key)){
                        $r = apply_filters('themify_builder_after_' . $filter_key . '_parse_args', $r);
                }

		// Return the parsed results
		return $r;
	}


	/**
	 * Helper to get element attributes return as string.
	 * 
	 * @access public
	 * @param array $props 
	 * @return string
	 */
	public static function get_element_attributes( $props ) {
		$out = '';
		foreach( $props as $atts => $val ) { 
			$out .= ' '. $atts . '="' . esc_attr( $val ) . '"'; 
		}
		return $out;
	}

	public function check_for_old_ios( $classes ) {
		if( strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') 
			|| strpos($_SERVER['HTTP_USER_AGENT'],'iPad' ) 
			|| strpos($_SERVER['HTTP_USER_AGENT'], 'iPod' ) !== false ) {
			preg_match( '/OS\s(\d+)_\d/', $_SERVER['HTTP_USER_AGENT'], $version );
			if( !empty( $version[1] ) && $version[1] < 8 ) {
				$classes[] = 'ios7';
			}
		}

		return $classes;
	}

	// Fix for old add-ons with live preview FW
	public function get_rgba_color( $color ) {
		return $this->stylesheet->get_rgba_color( $color );
	}

	/**
	 * Render row styling.
	 *
	 * render row styling css.
	 *
	 * @deprecated 3.1.2 Use Themify_Builder_Stylesheet->render_row_styling()
	 * @see Themify_Builder_Stylesheet->render_row_styling()
	 *
	 * @param string $builder_id Builder ID.
	 * @param array $row Row data.
	 */
	public function render_row_styling( $builder_id, $row ) {
		$this->stylesheet->render_row_styling( $builder_id, $row );
	}

	/**
	 * Render Sub row styling.
	 *
	 * render sub-row styling css.
	 *
	 * @deprecated 3.1.2 Use Themify_Builder_Stylesheet->render_sub_row_styling()
	 * @see Themify_Builder_Stylesheet->render_sub_row_styling()
	 *
	 * @param string $builder_id Builder ID.
	 * @param array $row Row data.
	 * @param array $column column data.
	 * @param array $subrow subrow data.
	 */
	public function render_sub_row_styling( $builder_id, $row, $column, $subrow ) {
		$this->stylesheet->render_sub_row_styling( $builder_id, $row, $column, $subrow );
	}

	/**
	 * Render column styling.
	 *
	 * render column styling css.
	 *
	 * @deprecated 3.1.2 Use Themify_Builder_Stylesheet->render_column_styling()
	 * @see Themify_Builder_Stylesheet->render_column_styling()
	 *
	 * @param string $builder_id Builder ID.
	 * @param array $row Row data.
	 * @param array $column Column data.
	 */
	public function render_column_styling( $builder_id, $row, $column ) {
		$this->stylesheet->render_column_styling( $builder_id, $row, $column );
	}

	/**
	 * Render sub column styling.
	 *
	 * render sub column styling css.
	 *
	 * @deprecated 3.1.2 Use Themify_Builder_Stylesheet->render_sub_column_styling()
	 * @see Themify_Builder_Stylesheet->render_sub_column_styling()
	 *
	 * @param string $builder_id Builder ID.
	 * @param array $rows Row data.
	 * @param array $cols Column data.
	 * @param array $modules Modules data.
	 * @param array $sub_column Sub-column data.
	 */
	public function render_sub_column_styling( $builder_id, $rows, $cols, $modules, $sub_column ) {
		$this->stylesheet->render_sub_column_styling( $builder_id, $rows, $cols, $modules, $sub_column );
	}

	/**
	 * Gets the data from a "query_category" field and
	 * returns a formatted "tax_query" array expected by WP_Query.
	 *
	 * @return array
	 */
	public function parse_query_category_field( $value, $taxonomy = 'category' ) {
		$query = array();
		if ( '0' !== $value ) {
			$terms = array_map( 'trim', explode( ',', $value ) );
			$ids_in = array_filter( $terms, create_function( '$a', 'return is_numeric( $a ) && "-" !== $a[0];' ) );
			$ids_not_in = array_filter( $terms, create_function( '$a', 'return is_numeric( $a ) && "-" === $a[0];' ) );
			$slugs_in = array_filter( $terms, create_function( '$a', 'return ! is_numeric( $a ) && "-" !== $a[0];' ) );
			$slugs_not_in = array_filter( $terms, create_function( '$a', 'return ! is_numeric( $a ) && "-" === $a[0];' ) );

			if ( ! empty( $ids_in ) ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => $ids_in
				);
			}
			if ( ! empty( $ids_not_in ) ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'id',
					'terms' => array_map( 'abs', $ids_not_in ),
					'operator' => 'NOT IN'
				);
			}
			if ( ! empty( $slugs_in ) ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => $slugs_in
				);
			}
			if ( ! empty( $slugs_not_in ) ) {
				$query[] = array(
					'taxonomy' => $taxonomy,
					'field' => 'slug',
					'terms' => array_map( create_function( '$a', 'return substr( $a, 1 );' ), $slugs_not_in ), // remove the minus sign (first character)
					'operator' => 'NOT IN'
				);
			}
		}

		return $query;
	}
}
endif;
