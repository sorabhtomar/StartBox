<?php
/**
 * StartBox - Main class
 *
 * Loads all includes, theme constants, adds/removes filters, etc.
 *
 * @package StartBox
 * @subpackage Functions
 * @since 2.4.5
 */
if ( ! class_exists('StartBox') ) {
	class StartBox {

		/**
		 * Initialization constructor for SBX
		 *
		 * @since 3.0.0
		 */
		public function __construct() {

			// Hook in all the different parts of our engine
			add_action( 'after_setup_theme', array( $this, 'constants' ), 1 );
			add_action( 'after_setup_theme', array( $this, 'core' ), 2 );
			add_action( 'after_setup_theme', array( $this, 'extensions' ), 3 );
			add_action( 'after_setup_theme', array( $this, 'i18n' ), 4 );
			add_action( 'after_setup_theme', array( $this, 'environment' ), 10 );
			add_action( 'init', array( $this, 'scripts_and_styles' ), 1 );

			// Available action for other processes to fire during init
			do_action( 'sb_init', $this );

		}

		/**
		 * Register the constants used throughout SBX
		 *
		 * @since 3.0.0
		 */
		public function constants() {

			define( 'SB_VERSION',      '3.0.0' );

			define( 'THEME_DIR',       trailingslashit( get_template_directory() ) );
			define( 'THEME_URI',       trailingslashit( get_template_directory_uri() ) );
			define( 'CHILD_THEME_DIR', trailingslashit( get_stylesheet_directory() ) );
			define( 'CHILD_THEME_URI', trailingslashit( get_stylesheet_directory_uri() ) );
			define( 'SB_DIR',          trailingslashit( trailingslashit( THEME_DIR ) . basename( dirname( __FILE__ ) ) ) );
			define( 'SB_URI',          trailingslashit( trailingslashit( THEME_URI ) . basename( dirname( __FILE__ ) ) ) );
			define( 'SB_ADMIN',        trailingslashit( SB_DIR . 'admin' ) );
			define( 'SB_CLASSES',      trailingslashit( SB_DIR . 'classes' ) );
			define( 'SB_CSS',          trailingslashit( SB_URI . 'css' ) );
			define( 'SB_EXTENSIONS',   trailingslashit( SB_DIR . 'extensions' ) );
			define( 'SB_IMAGES',       trailingslashit( SB_DIR . 'images' ) );
			define( 'SB_JS',           trailingslashit( SB_URI . 'js' ) );
			define( 'SB_LANGUAGES',    trailingslashit( SB_DIR . 'languages' ) );
			define( 'SB_WIDGETS',      trailingslashit( SB_DIR . 'widgets' ) );

		}

		/**
		 * Load core file requirements for SBX
		 *
		 * @since 3.0.0
		 */
		public function core() {
			require_once( SB_EXTENSIONS . 'conditionals.php' );
			require_once( SB_EXTENSIONS . 'custom.php' );
			require_once( SB_EXTENSIONS . 'hooks.php' );
			require_once( SB_EXTENSIONS . 'images.php' );
		}

		/**
		 * Load custom theme extensions, only if supported by the theme
		 *
		 * @since 3.0.0
		 */
		public function extensions() {
			require_if_theme_supports( 'sb-breadcrumbs',  SB_CLASSES . 'SB_Breadcrumbs.php' );
			require_if_theme_supports( 'sb-customizer',   SB_CLASSES . 'SB_Customizer.php' );
			require_if_theme_supports( 'sb-layouts',      SB_CLASSES . 'SB_Layouts.php' );
			require_if_theme_supports( 'sb-shortcodes',   SB_EXTENSIONS . 'shortcodes.php' );
			require_if_theme_supports( 'sb-sidebars',     SB_CLASSES . 'SB_Sidebars.php' );
			require_if_theme_supports( 'sb-updates',      SB_CLASSES . 'SB_Updater.php' );

			// Include all customization panels
			foreach ( glob( SB_ADMIN . '*.php') as $sb_admin )
				require_if_theme_supports( 'sb-customizer', $sb_admin );

			// Include all packaged widgets
			foreach ( glob( SB_WIDGETS . '*.php') as $sb_widget )
				require_if_theme_supports( 'sb-widgets', $sb_widget );

		}

		/**
		 * Setup theme translations
		 *
		 * @since 3.0.0
		 */
		public function i18n() {

			// Translate, if applicable
			load_theme_textdomain( 'sbx', SB_LANGUAGES );

		}


		/**
		 * Register the packaged scripts and styles
		 *
		 * @since 3.0.0
		 */
		public function scripts_and_styles() {

			// Register Default Scripts
			wp_register_script( 'colorbox',     SB_JS . 'jquery.colorbox.min.js', array( 'jquery' ), SB_VERSION );
			wp_register_script( 'smoothScroll', SB_JS . 'jquery.smooth-scroll.min.js', array( 'jquery' ), SB_VERSION );
			wp_register_script( 'startbox',     SB_JS . 'startbox.js', array( 'jquery' ), SB_VERSION );
			wp_register_script( 'widgets',      SB_JS . 'widgets.js', array( 'jquery' ), SB_VERSION );

			// Register Default Styles
			wp_register_style( 'colorbox',      SB_CSS . 'colorbox.css', null, SB_VERSION, 'screen' );
			wp_register_style( 'images',        SB_CSS . 'images.css', null, SB_VERSION );
			wp_register_style( 'layouts',       SB_CSS . 'layouts.css', null, SB_VERSION );
			wp_register_style( 'print',         SB_CSS . 'print.css', null, SB_VERSION, 'print' );
			wp_register_style( 'reset',         SB_CSS . 'reset.css', null, SB_VERSION );
			wp_register_style( 'shortcodes',    SB_CSS . 'shortcodes.css', null, SB_VERSION );
			wp_register_style( 'typography',    SB_CSS . 'typography.css', null, SB_VERSION );
		}

		// Setup the environment and register support for various WP features.
		public function environment() {

			// Add theme support for various WP-specific features
			add_editor_style( array(						// This sets up the content editor style to match the front-end design
				'/includes/styles/typography.css',			// Basic Typography
				'/includes/styles/editor.css'				// Content-specific styles (adapted from startbox.css)
			) );

			// Add theme support for StartBox Layouts, redefine this list of available layouts using the filter 'sb_layouts_defaults'
			$sb_default_layouts = array(
				'one-col'         => array( 'label' => '1 Column (no sidebars)', 			'img' => SB_IMAGES . 'layouts/one-col.png' ),
				'two-col-left'    => array( 'label' => '2 Columns, sidebar on left', 		'img' => SB_IMAGES . 'layouts/two-col-left.png' ),
				'two-col-right'   => array( 'label' => '2 Columns, sidebar on right', 		'img' => SB_IMAGES . 'layouts/two-col-right.png' ),
				'three-col-left'  => array( 'label' => '3 Columns, sidebar on left', 		'img' => SB_IMAGES . 'layouts/three-col-left.png' ),
				'three-col-right' => array( 'label' => '3 Columns, sidebar on right', 		'img' => SB_IMAGES . 'layouts/three-col-right.png' ),
				'three-col-both'  => array( 'label' => '3 Columns, sidebar on each side',	'img' => SB_IMAGES . 'layouts/three-col-both.png' )
			);

			add_theme_support( 'sb-layouts', apply_filters( 'sb_layouts_defaults', $sb_default_layouts) ); 				// Theme Layouts
			add_theme_support( 'sb-layouts-home', apply_filters( 'sb_layouts_defaults_home', $sb_default_layouts ) );	// Theme Layouts (homepage)

		}

	}
}
$GLOBALS['startbox'] = new StartBox;

// "God opposes the proud, but gives grace to the humble." - James 4:6b (ESV)