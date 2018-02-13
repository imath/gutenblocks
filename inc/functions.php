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
		'gutenblocks-release' => array(
			'location' => sprintf( '%1$sblocks/release%2$s.js', $url, $min ),
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
			esc_url( $url ),
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
		'release' => array(
			'title'              => _x( 'GitHub Release',        'Release Block Title',        'gutenblocks' ),
			'namePlaceholder'    => _x( 'Nom du dépôt',          'Release Block Slug',         'gutenblocks' ),
			'labelPlaceholder'   => _x( 'Nom de l’extension…',   'Release Block Name',         'gutenblocks' ),
			'tagPlaceholder'     => _x( 'Numéro de version',     'Release Block Tag',          'gutenblocks' ),
			'notesPlaceholder'   => _x( 'Notes sur la version…', 'Release Block Notes',        'gutenblocks' ),
			'ghUsername'         => gutenblocks_github_release_get_username(),
			'downloadHTML'       => _x( 'Télécharger la version %s', 'Release Block Download', 'gutenblocks' ),
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
		'gutenblocks-wp-embed',
		'gutenblocks-release',
	);

	foreach ( $blocks as $block ) {
		wp_enqueue_script( $block );
	}

	$handle = reset( $blocks );
	wp_localize_script( $handle, 'gutenBlocksStrings', gutenblocks_l10n() );

	/**
	 * Unregister the Gutenberg Block as it doesn't work for self embeds.
	 *
	 * @see https://github.com/WordPress/gutenberg/pull/4226
	 */
	wp_add_inline_script(
		'wp-editor',
		'
		( function( wp ) {
			if ( wp.blocks ) {
				wp.blocks.unregisterBlockType( \'core-embed/wordpress\' );
			}
		} )( window.wp || {} );
		',
		'after'
	);

	// Make sure the wp-embed.js script is loaded once.
	wp_enqueue_script( 'wp-embed' );
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
 * Get the GitHub username.
 *
 * @since  1.1.0
 *
 * @return string The GitHub username.
 */
function gutenblocks_github_release_get_username() {
	/**
	 * Filter here to customize with your GitHub username.
	 *
	 * @since  1.1.0
	 *
	 * @param  string $value Your GitHub username.
	 */
	return apply_filters( 'gutenblocks_github_release_get_username', 'imath' );
}

/**
 * Get base64 encoded SVG icon.
 *
 * @since 1.1.0
 *
 * @param  string $name The name of the SVG icon.
 * @return string       Base 64 encoded SVG icon.
 */
function gutenblocks_github_release_icon( $name = 'default_icon' ) {
	$icons = array(
		'default_icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><g><path d="M1 4.27v7.47c0 .45.3.84.75.97l6.5 1.73c.16.05.34.05.5 0l6.5-1.73c.45-.13.75-.52.75-.97V4.27c0-.45-.3-.84-.75-.97l-6.5-1.74a1.4 1.4 0 0 0-.5 0L1.75 3.3c-.45.13-.75.52-.75.97zm7 9.09l-6-1.59V5l6 1.61v6.75zM2 4l2.5-.67L11 5.06l-2.5.67L2 4zm13 7.77l-6 1.59V6.61l2-.55V8.5l2-.53V5.53L15 5v6.77zm-2-7.24L6.5 2.8l2-.53L15 4l-2 .53z"/></g></svg>',
		'download_icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="0" fill="none" width="20" height="20"/><g><path fill="#FFFFFF" d="M14.01 4v6h2V2H4v8h2.01V4h8zm-2 2v6h3l-5 6-5-6h3V6h4z"/></g></svg>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return 'data:image/svg+xml;base64,' . base64_encode( $icons[ $name ] );
}

/**
 * Dynamic GutenBlock's GitHub Release callback.
 *
 * @since 1.1.0
 *
 * @param  array $attributes The GutenBlock attributes.
 * @return string            The content to output on front-end
 */
function gutenblocks_github_release_callback( $attributes = array() ) {
	// Merge defaults with attributes
	$a = wp_parse_args( $attributes, array(
		'name'  => '',
		'label' => '',
		'tag'   => '',
		'logo'  => '',
		'notes' => '',
	) );

	if ( empty( $a['name'] ) || empty( $a['tag'] ) ) {
		return;
	}

	// sanitize the name.
	$name = sanitize_key( remove_accents( $a['name'] ) );
	$tag  = $a['tag'];

	// Try the transient first (dayly updated).
	$release_data   = get_site_transient( 'github_release_data_' . $name );
	$download_count = 0;

	// The transient is not set yet or it's a new release.
	if ( ! isset( $release_data->tag_name ) || version_compare( $tag, $release_data->tag_name, '>' ) ) {
		$gh_releases_url = sprintf( 'https://api.github.com/repos/imath/%s/releases', $name );
		$gh_response     = wp_remote_get( $gh_releases_url );

		if ( ! is_wp_error( $gh_response ) && 200 === (int) wp_remote_retrieve_response_code( $gh_response ) ) {
			$releases       = json_decode( wp_remote_retrieve_body( $gh_response ), true );

			if ( $releases ) {
				foreach ( (array) $releases as $release ) {
					$package  = array();

					// Count downloads about all releases.
					if ( ! empty( $release['assets'] ) ) {
						$package         = reset( $release['assets'] );
						$download_count += (int) $package['download_count'];
					}

					// Only keep the requested release
					if ( $tag !== $release['tag_name'] ) {
						continue;
					}

					$release_data = (object) array(
						'id'       => $release['id'],
						'url'      => $release['html_url'],
						'name'     => $release['name'],
						'tag_name' => $release['tag_name'],
						'package'  => $package,
					);
				}

				if ( isset( $release_data->id ) ) {
					$release_data->downloads = $download_count;
					set_site_transient( 'github_release_data_' . $name, $release_data, DAY_IN_SECONDS );
				}
			}
		}
	}

	$label = $name;
	if ( $a['label'] ) {
		$label = esc_html( $a['label'] );
	}

	$release_url  = sprintf( 'https://github.com/imath/%1$s/releases/tag/%2$s', $name, $tag );
	$download_url = sprintf( 'https://github.com/imath/%1$s/releases/download/%2$s/%1$s.zip', $name, $tag );

	$count = '';
	if ( isset( $release_data->downloads ) ) {
		$count = sprintf( '<p class="description">%s</p>',
			sprintf(
				_n( '%d téléchargement', '%d téléchargements', $release_data->downloads, 'gutenblocks' ),
				number_format_i18n( $release_data->downloads )
			)
		);
	}

	$logo = sprintf( '<img class="release-icon" src="%s">', gutenblocks_github_release_icon() );
	if ( ! empty( $a['logo'] ) ) {
		$logo = sprintf( '<img class="release-icon" src="%s">', esc_url( $a['logo'] ) );
	}

	if ( ! empty( $a['notes'] ) ) {
		$notes  = str_replace( array( '<p>', '</p>' ), '', wpautop( trim( $a['notes'], "\n" ) ) );
		$count  = sprintf( '<p class="description">%s</p>', wp_kses( $notes, array( 'br' => true ) ) ) ."\n" . $count;
	}

	return sprintf( '
		<div class="plugin-card">
			<div class="plugin-card-top">
				<div class="name column-name">
					<h3>
						<a href="%1$s">
							%2$s
							%3$s
						</a>
					</h3>
				</div>
				<div class="desc column-description">
					%4$s
					<p class="description"><a href="%5$s" target="_blank">%6$s</a></p>
				</div>
				<div class="download">
					<button class="button submit gh-download-button">
						<img src="%7$s" class="gh-release-download-icon">
						<a href="%1$s">%8$s</a>
					</button>
				</div>
			</div>
		</div>',
		esc_url( $download_url ),
		$logo,
		$label,
		$count,
		esc_url( $release_url ),
		esc_html__( 'Afficher la page GitHub de la version', 'gutenblocks' ),
		gutenblocks_github_release_icon( 'download_icon' ),
		sprintf( esc_html__( 'Télécharger la version %s', 'gutenblocks' ), $tag )
	);
}

/**
 * Register dynamic Gutenberg blocks.
 *
 * @since  1.1.0
 */
function gutenblocks_register_dynamic_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return false;
	}

	register_block_type( 'gutenblocks/release', array(
		'render_callback' => 'gutenblocks_github_release_callback',
	) );
}
add_action( 'init', 'gutenblocks_register_dynamic_blocks' );

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

if ( ! function_exists( 'gutenberg_filter_oembed_result' ) ) :
/**
 * Make sure the data-secret Attribute is added to the WordPress embed html response.
 *
 * There's a Pull Request fixing this issue that is still under review.
 * @see https://github.com/WordPress/gutenberg/pull/4226
 *
 * @since 1.1.1
 *
 * @param  WP_HTTP_Response|WP_Error $response The REST Request response.
 * @param  WP_REST_Server            $handler  ResponseHandler instance (usually WP_REST_Server).
 * @param  WP_REST_Request           $request  Request used to generate the response.
 * @return WP_HTTP_Response|object|WP_Error    The REST Request response.
 */
function gutenblocks_filter_oembed_result( $response, $handler, $request ) {
	if ( 'GET' !== $request->get_method() || ! $request->get_param( 'url' ) ) {
		return $response;
	}

	if ( is_wp_error( $response ) && 'oembed_invalid_url' !== $response->get_error_code() ) {
		return $response;
	}

	$rest_route = $request->get_route();
	if ( '/oembed/1.0/proxy' !== $rest_route && '/oembed/1.0/embed' !== $rest_route ) {
		return $response;
	}

	// Make sure the response is an object.
	$embed_response = (object) $response;

	if ( ! isset( $embed_response->html ) || ! preg_match( '/wp-embedded-content/', wp_unslash( $embed_response->html ) ) ) {
		return $response;
	}

	$embed_response->html = wp_filter_oembed_result( $embed_response->html, $embed_response, $request->get_param( 'url' ) );

	return $embed_response;
}
add_filter( 'rest_request_after_callbacks', 'gutenblocks_filter_oembed_result', 10, 3 );

endif;
