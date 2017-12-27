<?php
/**
 * GutenBlocks functions.
 *
 * @package GutenBlocks\inc
 *
 * @since  1.0.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Get plugin's version.
 *
 * @since  1.0.0
 *
 * @return string the plugin's version.
 */
function gutenblocks_version() {
	return gutenblocks()->version;
}

/**
 * Get the plugin's JS Url.
 *
 * @since  1.0.0
 *
 * @return string the plugin's JS Url.
 */
function gutenblocks_js_url() {
	return gutenblocks()->js_url;
}

/**
 * Get the plugin's Assets Url.
 *
 * @since  1.0.0
 *
 * @return string the plugin's Assets Url.
 */
function gutenblocks_assets_url() {
	return gutenblocks()->assets_url;
}

/**
 * Get the JS minified suffix.
 *
 * @since  1.0.0
 *
 * @return string the JS minified suffix.
 */
function gutenblocks_min_suffix() {
	$min = '.min';

	if ( defined( 'SCRIPT_DEBUG' ) && true === SCRIPT_DEBUG )  {
		$min = '';
	}

	/**
	 * Filter here to edit the minified suffix.
	 *
	 * @since  1.0.0
	 *
	 * @param  string $min The minified suffix.
	 */
	return apply_filters( 'gutenblocks_min_suffix', $min );
}

/**
 * Registers JavaScripts and Styles.
 *
 * @since 1.0.0
 */
function gutenblocks_register_scripts() {
	$min = gutenblocks_min_suffix();
	$v   = gutenblocks_version();

	/** JavaScripts **********************************************************/
	$url = gutenblocks_js_url();

	$scripts = apply_filters( 'gutenblocks_register_javascripts', array(
		'gutenblocks-photo' => array(
			'location' => sprintf( '%1$sblocks/photo%2$s.js', $url, $min ),
			'deps'     => array( 'wp-blocks', 'wp-element' ),
		),
	), $url, $min, $v );

	foreach ( $scripts as $js_handle => $script ) {
		$in_footer = false;

		if ( isset( $script['footer'] ) ) {
			$in_footer = $script['footer'];
		}

		wp_register_script( $js_handle, $script['location'], $script['deps'], $v, $in_footer );
	}

	/** Style ****************************************************************/

	wp_register_style( 'gutenblocks',
		sprintf( '%1$sblocks%2$s.css', gutenblocks_assets_url(), $min ),
		array( 'wp-blocks' ),
		$v
	);
}
add_action( 'init', 'gutenblocks_register_scripts', 12 );

/**
 * l10n for GutenBlocks.
 *
 * @since 1.0.0
 *
 * @return  array The GutenBlocks l10n strings.
 */
function gutenblocks_l10n() {
	return array(
		'photo' => array(
			'title'              => __( 'Photo (URL)',          'gutenblocks' ),
			'inputPlaceholder'   => __( 'URL de la photo…',     'gutenblocks' ),
			'buttonPlaceholder'  => __( 'Insérer',              'gutenblocks' ),
			'captionPlaceholder' => __( 'Ajoutez une légende…', 'gutenblocks' ),
			'editBubble'         => __( 'Modifier',             'gutenblocks' ),
		)
	);
}

/**
 * Enqueues the Gutenberg blocks script.
 *
 * @since 1.0.0
 */
function gutenblocks_editor() {
	wp_enqueue_script( 'gutenblocks-photo' );
	wp_localize_script( 'gutenblocks-photo', 'gutenBlocksStrings', gutenblocks_l10n() );
}
add_action( 'enqueue_block_editor_assets', 'gutenblocks_editor' );

/**
 * Enqueues the Gutenberg blocks style.
 *
 * @since 1.0.0
 */
function gutenblocks_style() {
	wp_enqueue_style( 'gutenblocks' );
}
add_action( 'enqueue_block_assets', 'gutenblocks_style' );

/**
 * Loads translation.
 *
 * @since 1.0.0
 */
function gutenblocks_load_textdomain() {
	$g = gutenblocks();

	load_plugin_textdomain( $g->domain, false, trailingslashit( basename( $g->dir ) ) . 'languages' );
}
add_action( 'plugins_loaded', 'gutenblocks_load_textdomain', 9 );
