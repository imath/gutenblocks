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
		'gutenblocks-gist' => array(
			'location' => sprintf( '%1$sblocks/gist%2$s.js', $url, $min ),
			'deps'     => array( 'wp-blocks', 'wp-element' ),
		),
		'gutenblocks-wp-embed' => array(
			'location' => sprintf( '%1$sblocks/wp-embed%2$s.js', $url, $min ),
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
 * Returns the custom embed providers.
 *
 * @since  1.1.0
 *
 * @return array The list of custom providers.
 */
function gutenblocks_get_embed_providers() {
	return apply_filters( 'gutenblocks_register_embed_providers', array(
		'gutenblocks_gist' => array(
			'regex'    => '#(https://gist.github.com/(.*?)/([a-zA-Z0-9]+)?)(\#file(\-|_)(.+))?$#i',
			'callback' => 'gutenblocks_gist_handler',
			'format'   => '#https?://gist\.github\.com/.*#i',
			'provider' => 'https://gist.github.com',
			'validate' => 'gutenblocks_build_gist_src',
		),
	) );
}

/**
 * Register custom embed providers.
 *
 * @since  1.1.0
 */
function gutenblocks_register_embed_providers() {
	$custom_providers = gutenblocks_get_embed_providers();

	foreach ( $custom_providers as $provider => $data ) {
		// This is needed to fetch the Gist in Gutenberg.
		wp_oembed_add_provider( $data['format'], $data['provider'], true );

		// This is needed for the classic editor and on front-end.
		wp_embed_register_handler( $provider, $data['regex'], $data['callback'] );
	}
}
add_action( 'plugins_loaded', 'gutenblocks_register_embed_providers', 14 );

/**
 * Customize the oembed url for Gists.
 *
 * @since  1.1.0
 *
 * @param  string $provider The URL provider.
 * @param  string $url      The URL to embed.
 * @param  array $args      The oembed arguments.
 * @return string           The URL to request.
 */
function gutenblocks_oembed_fetch_url( $provider = '', $url, $args ) {
	$provider_data = wp_parse_url( $provider );

	if ( ! isset( $provider_data['host'] ) || 'gist.github.com' !== $provider_data['host'] ) {
		return $provider;
	}

	// It's a gist, make sure the reply will be a JSON content.
	add_filter( 'http_response', 'gutenblocks_embed_response', 10, 3 );

	return $url;
}
add_filter( 'oembed_fetch_url', 'gutenblocks_oembed_fetch_url', 10, 3 );

/**
 * Edit the JSON reply for the embed Gist.
 *
 * @since  1.1.0
 *
 * @param  array  $response The HTTP request response.
 * @param  array  $args     The HTTP request arguments.
 * @param  string $url      The requested URL.
 * @return array            The HTTP request response.
 */
function gutenblocks_embed_response( $response, $args, $url ) {
	remove_filter( 'http_response', 'gutenblocks_embed_response', 10, 3 );

	$handlers = wp_list_pluck( gutenblocks_get_embed_providers(), 'regex', 'validate' );

	if ( ! $handlers ) {
		return $response;
	}

	$body = '';
	$raw_url = str_replace( '?', '', remove_query_arg( array( 'format' ), $url ) );

	foreach ( $handlers as $validate => $regex ) {
		if ( ! preg_match( $regex, $raw_url, $matches ) ) {
			continue;
		}

		if ( ! function_exists( $validate ) ) {
			continue;
		}

		$body = json_encode( array(
			'url' => call_user_func_array( $validate, array( $matches ) ),
		) );
	}

	// We have a body, set it.
	if ( $body ) {
		$response['body'] = $body;
		$response['http_response']->set_data( $body );
	}

	return $response;
}

/**
 * Builds the src attribute of the gist to embed.
 * @param  array  $matches [description]
 * @return [type]          [description]
 */
function gutenblocks_build_gist_src( $matches = array() ) {
	$url = '';

	if ( isset( $matches[3] ) ) {
		$url = $matches[1] . '.js';

		if ( isset( $matches[6] ) ) {
			$url = add_query_arg( 'file', preg_replace( '/[\-\.]([a-z]+)$/', '.\1', $matches[6] ), $url );
		}
	}

	return $url;
}

/**
 * Gist embed handler callback.
 *
 * @since 1.1.0
 *
 * @param array  $matches The RegEx matches from the provided regex when calling
 *                        wp_embed_register_handler().
 * @param array  $attr    Embed attributes.
 * @param string $url     The original URL that was matched by the regex.
 * @param array  $rawattr The original unmodified attributes.
 * @return string The embed HTML.
 */
function gutenblocks_gist_handler( $matches, $attr, $url, $rawattr ) {
	// Only display full gists on single pages
	if ( is_front_page() || is_home() ) {
		return sprintf(
			'<p><a href="%1$s">%2$s</a></p>',
			esc_url( $src ),
			esc_html__( 'Afficher le code imbriqué', 'gutenblocks' )
		);
	}

	$src = gutenblocks_build_gist_src( $matches );

	if ( $url ) {
		return sprintf( '<script src="%s"></script>', esc_url( $src ) );
	}
}

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
			'title'              => _x( 'Photo (URL)',          'Photo Block Title',   'gutenblocks' ),
			'inputPlaceholder'   => _x( 'URL de la photo…',     'Photo Block Input',   'gutenblocks' ),
			'buttonPlaceholder'  => _x( 'Insérer',              'Photo Block Button',  'gutenblocks' ),
			'captionPlaceholder' => _x( 'Ajoutez une légende…', 'Photo Block Caption', 'gutenblocks' ),
			'editBubble'         => _x( 'Modifier',             'Photo Block Bubble',  'gutenblocks' ),
		),
		'gist' => array(
			'title'              => _x( 'GitHub Gist',  'Gist Block Title',  'gutenblocks' ),
			'inputPlaceholder'   => _x( 'URL du gist…', 'Gist Block Input',  'gutenblocks' ),
			'buttonPlaceholder'  => _x( 'Insérer',      'Gist Block Button', 'gutenblocks' ),
		),
		'wp_embed' => array(
			'title'              => _x( 'WordPress',         'WP Embed Block Title',  'gutenblocks' ),
			'inputPlaceholder'   => _x( 'URL de l’article…', 'WP Embed Block Input',  'gutenblocks' ),
			'buttonPlaceholder'  => _x( 'Insérer',           'WP Embed Block Button', 'gutenblocks' ),
		),
	);
}

/**
 * Enqueues the Gutenberg blocks script.
 *
 * @since 1.0.0
 */
function gutenblocks_editor() {
	$blocks = array(
		'gutenblocks-photo',
		'gutenblocks-gist',
		'gutenblocks-wp-embed'
	);

	foreach ( $blocks as $block ) {
		wp_enqueue_script( $block );
	}

	$handle = reset( $blocks );
	wp_localize_script( $handle, 'gutenBlocksStrings', gutenblocks_l10n() );
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
