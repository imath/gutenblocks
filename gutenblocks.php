<?php
/**
 * Plugin Name: GutenBlocks
 * Plugin URI: https://imathi.eu/tag/gutenblocks/
 * Description: Ma collection personnelle de blocs Gutenberg.
 * Version: 1.2.1
 * Requires at least: 4.9
 * Tested up to: 5.0
 * License: GNU/GPL 2
 * Author: imath
 * Author URI: https://imathi.eu/
 * Text Domain: gutenblocks
 * Domain Path: /languages/
 * GitHub Plugin URI: https://github.com/imath/gutenblocks/
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GutenBlocks' ) ) :
/**
 * Main plugin's class
 *
 * @package gutenblocks
 *
 * @since 1.0.0
 */
final class GutenBlocks {

	/**
	 * Plugin's main instance
	 *
	 * @var object
	 */
	protected static $instance;

	/**
	 * Initialize the plugin
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		$this->globals();
		$this->inc();
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return object A single instance of this class.
	 */
	public static function start() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Setups plugin's globals
	 *
	 * @since 1.0.0
	 */
	private function globals() {
		// Version
		$this->version = '1.2.1';

		// Domain
		$this->domain = 'gutenblocks';

		// Base name
		$this->file      = __FILE__;
		$this->basename  = plugin_basename( $this->file );

		// Path and URL
		$this->dir        = plugin_dir_path( $this->file );
		$this->url        = plugin_dir_url ( $this->file );
		$this->js_url     = trailingslashit( $this->url . 'js' );
		$this->assets_url = trailingslashit( $this->url . 'assets' );
		$this->inc_dir    = trailingslashit( $this->dir . 'inc' );

		// Permalinks type
		$this->pretty_urls = get_option( 'permalink_structure' );
	}

	/**
	 * Includes plugin's server functions
	 *
	 * @since 1.0.0
	 */
	private function inc() {
		spl_autoload_register( array( $this, 'autoload' ) );

		require $this->inc_dir . 'functions.php';
	}

	/**
	 * Class Autoload function
	 *
	 * @since  1.2.0
	 *
	 * @param  string $class The class name.
	 */
	public function autoload( $class ) {
		$name = str_replace( '_', '-', strtolower( $class ) );

		if ( false === strpos( $name, $this->domain ) ) {
			return;
		}

		$path = $this->inc_dir . "classes/{$name}.php";

		// Sanity check.
		if ( ! file_exists( $path ) ) {
			return;
		}

		require $path;
	}
}

endif;

if ( ! function_exists( 'gutenblocks' ) ) :
/**
 * Boot the plugin.
 *
 * @since 1.0.0
 */
function gutenblocks() {
	if ( ! function_exists( 'gutenberg_init' ) ) {
		return false;
	}

	return GutenBlocks::start();
}
add_action( 'plugins_loaded', 'gutenblocks', 5 );

endif;
