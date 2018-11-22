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
 * Get DB version.
 *
 * @since  1.4.0
 *
 * @return string the DB version.
 */
function gutenblocks_db_version() {
	return gutenblocks()->db_version;
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
 * Checks if the Core WP Embed block is fixed.
 *
 * @since  1.1.2
 * @deprecated 1.2.3
 *
 * @return boolean True if fixed, false otherwise.
 */
function gutenblocks_wp_embed_is_fixed() {
	_deprecated_function( __FUNCTION__, '1.2.3' );
	return true;
}

/**
 * Are permalinks pretty ?
 *
 * @since  1.2.0
 *
 * @return boolean True if permalinks are pretty. False otherwise.
 */
function gutenblocks_are_urls_pretty() {
	return !! gutenblocks()->pretty_urls;
}

/**
 * Get the available locales.
 *
 * @since  1.2.0
 *
 * @return boolean|array False if no other locales than en_US, the list of locales otherwise.
 */
function gutenblocks_get_languages() {
	$languages = get_available_languages();
	$locale    = get_locale();

	if ( ! $languages ) {
		return false;
	}

	$default = array( 'en_US' );
	if ( 'en_US' !== $locale ) {
		array_unshift( $default, $locale );
	}

	return array_unique( array_merge( $default, $languages ) );
}

/**
 * Is the given string a locale ?
 *
 * @since  1.2.0
 *
 * @param  string $locale A potential locale string
 * @return boolean        True if it's a locale. False otherwise.
 */
function gutenblocks_is_locale( $locale = '' ) {
	if ( $locale && in_array( $locale, gutenblocks_get_languages(), true ) ) {
		return true;
	}

	return false;
}

/**
 * Adds a Flag to inform about the i18n Block language being edited
 *
 * @since  1.2.0
 *
 * @param  array  $languages The list of available locales.
 * @return string            Inline style.
 */
function gutenblocks_style_languages( $languages = array() ) {
	$flag_master = new GutenBlocks_flagMaster;

	$style = '';
	foreach( $languages as $language ) {
		$style .= sprintf( '
			body.wp-admin [data-type="gutenblocks/i18n"] .layout-row-%1$s {
				position: relative;
				margin-bottom: 10px;
			}

			body.wp-admin [data-type="gutenblocks/i18n"] .layout-row-%1$s:before {
				content: "%2$s";
				position: absolute;
				top: -12px;
				left: 10px;
			}

			.gutenblocks-i18n-switcher li.nav-%1$s a:before {
				content: "%2$s";
			}
		%3$s', $language, $flag_master::emojiFlag( strtolower( substr( $language, -2 ) ) ), "\n" );
	}

	return $style;
}

/**
 * l10n for GutenBlocks.
 *
 * @since 1.0.0
 *
 * @return  array The GutenBlocks l10n strings.
 */
function gutenblocks_l10n() {
	$g_l10n = array(
		'gist' => array(
			'title'              => _x( 'GitHub Gist',  'Gist Block Title',  'gutenblocks' ),
			'inputPlaceholder'   => _x( 'URL du gist…', 'Gist Block Input',  'gutenblocks' ),
			'buttonPlaceholder'  => _x( 'Insérer',      'Gist Block Button', 'gutenblocks' ),
			'description'        => _x( 'Ce bloc vous permet d’embarquer facilement vos codes sources hébergés sur gist.GitHub.com', 'Gist Block description', 'gutenblocks' ),
		),
		'release' => array(
			'title'              => _x( 'GitHub Release',        'Release Block Title',        'gutenblocks' ),
			'namePlaceholder'    => _x( 'Nom du dépôt',          'Release Block Slug',         'gutenblocks' ),
			'labelPlaceholder'   => _x( 'Nom de l’extension…',   'Release Block Name',         'gutenblocks' ),
			'tagPlaceholder'     => _x( 'Numéro de version',     'Release Block Tag',          'gutenblocks' ),
			'notesPlaceholder'   => _x( 'Notes sur la version…', 'Release Block Notes',        'gutenblocks' ),
			'ghUsername'         => gutenblocks_github_release_get_username(),
			'downloadHTML'       => _x( 'Télécharger la version %s', 'Release Block Download', 'gutenblocks' ),
			'description'        => _x( 'Ce bloc vous permet de créer une fiche descriptive de votre extension hébergée sur GitHub.com', 'Release Block description', 'gutenblocks' ),
		),
	);

	return $g_l10n;
}

/**
 * Registers JavaScripts and Styles.
 *
 * @since 1.0.0
 * @since 1.2.0 Register the editor script for the i18n Block.
 */
function gutenblocks_register_scripts() {
	$min = gutenblocks_min_suffix();
	$v   = gutenblocks_version();

	/** JavaScripts **********************************************************/
	$url = gutenblocks_js_url();

	$scripts = apply_filters( 'gutenblocks_register_javascripts', array(
		'gutenblocks-gist' => array(
			'location' => sprintf( '%1$sblocks/gist%2$s.js', $url, $min ),
			'deps'     => array( 'wp-blocks', 'wp-element' ),
		),
		'gutenblocks-release' => array(
			'location' => sprintf( '%1$sblocks/release%2$s.js', $url, $min ),
			'deps'     => array( 'wp-blocks', 'wp-element' ),
		),
		'gutenblocks-i18n' => array(
			'location' => sprintf( '%1$sblocks/i18n%2$s.js', $url, $min ),
			'deps'     => array( 'wp-blocks', 'wp-element' ),
		),
	), $url, $min, $v );

	foreach ( $scripts as $js_handle => $script ) {
		$in_footer = true;

		if ( isset( $script['footer'] ) ) {
			$in_footer = $script['footer'];
		}

		wp_register_script( $js_handle, $script['location'], $script['deps'], $v, $in_footer );
	}

	$handles = array_keys( $scripts );
	$handle  = reset( $handles );
	wp_localize_script( $handle, 'gutenBlocksStrings', gutenblocks_l10n() );

	/** Style ****************************************************************/

	wp_register_style( 'gutenblocks',
		sprintf( '%1$sblocks%2$s.css', gutenblocks_assets_url(), $min ),
		array( 'wp-blocks' ),
		$v
	);

	$languages = gutenblocks_get_languages();

	if ( ! $languages ) {
		wp_deregister_script( 'gutenblocks-i18n' );
	} else {
		wp_localize_script( 'gutenblocks-i18n', 'gutenblocksI18n', array(
			'languages'   => $languages,
			'title'       => _x( 'Doubleur (Expérimental)', 'i18n Block Title',  'gutenblocks' ),
			'description' => _x( 'Ce bloc vous permet de proposer votre contenu en plusieurs langue.', 'Dubber Block description', 'gutenblocks' ),
		) );

		if ( ! wp_doing_ajax() ) {
			wp_add_inline_style( 'gutenblocks', gutenblocks_style_languages( $languages ) );
		}
	}
}
add_action( 'init', 'gutenblocks_register_scripts', 12 );

/**
 * Register dynamic Gutenberg blocks.
 *
 * @since  1.1.0
 * @since  1.2.0 Register the i18n Block
 * @since  1.4.0 Use this function for all blocks.
 */
function gutenblocks_register_dynamic_blocks() {
	if ( ! function_exists( 'register_block_type' ) ) {
		return false;
	}

	register_block_type( 'gutenblocks/gist', array(
		'editor_script' => 'gutenblocks-gist',
	) );

	register_block_type( 'gutenblocks/release', array(
		'render_callback' => 'gutenblocks_github_release_callback',
		'editor_script'   => 'gutenblocks-release',
	) );

	if ( wp_scripts()->query( 'gutenblocks-i18n' ) ) {
		register_block_type( 'gutenblocks/i18n', array(
			'editor_script' => 'gutenblocks-i18n',
		) );
	}
}
add_action( 'init', 'gutenblocks_register_dynamic_blocks', 20 );

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
 * Enqueues the Gutenberg blocks script.
 *
 * @since 1.0.0
 * @deprecated 1.2.3
 */
function gutenblocks_editor() {
	_deprecated_function( __FUNCTION__, '1.2.3' );
}

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
 * @since 1.2.0 Add a layout class if used within a nested block.
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

	$container_class = 'plugin-card';

	if ( isset( $a['layout'] ) && $a['layout'] ) {
		$container_class .= ' layout-' . $a['layout'];

		$locale = str_replace( 'row-', '', $a['layout'] );
		if ( gutenblocks_is_locale( $locale ) && $locale !== get_locale() && $locale === gutenblocks_get_locale() ) {
			switch_to_locale( $locale );

			if ( ! is_textdomain_loaded( 'gutenblocks' ) ) {
				gutenblocks_load_textdomain();
			}
		}
	}

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

	$output = sprintf( '<div class="%1$s"><div class="plugin-card-top"><div class="name column-name"><h3><a href="%2$s">%3$s %4$s</a></h3></div><div class="desc column-description">%5$s<p class="description"><a href="%6$s" target="_blank">%7$s</a></p></div><div class="download"><button class="button submit gh-download-button"><img src="%8$s" class="gh-release-download-icon"><a href="%2$s">%9$s</a></button></div></div></div>',
		$container_class,
		esc_url( $download_url ),
		$logo,
		$label,
		$count,
		esc_url( $release_url ),
		esc_html__( 'Afficher la page GitHub de la version', 'gutenblocks' ),
		gutenblocks_github_release_icon( 'download_icon' ),
		sprintf( esc_html__( 'Télécharger la version %s', 'gutenblocks' ), $tag )
	);

	if ( is_locale_switched() ) {
		restore_current_locale();
	}

	return $output;
}

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

/**
 * Add rewrite rules for Post and Page translations.
 *
 * @since 1.2.0
 */
function gutenblocks_translate_rewrite_rules() {
	global $wp_rewrite;

	add_rewrite_tag(
		'%translate%',
		'([^/]+)'
	);

	$patterns = array(
		'/%year%/%monthnum%' => array(
			'pattern' => '([0-9]{4})/([0-9]{1,2})/',
			'query'   => '?year=$matches[1]&monthnum=$matches[2]&name=$matches[3]&translate=$matches[4]',
		),
		'/%year%/%monthnum%/%day%' => array(
			'pattern' => '([0-9]{4})/([0-9]{1,2})/([0-9]{1,2})/',
			'query'   => '?year=$matches[1]&monthnum=$matches[2]&day=$matches[3]&name=$matches[4]&translate=$matches[5]',
		),
		'/archives/%post_id%' => array(
			'pattern' => 'archives/',
			'query'   => '?p=$matches[1]&translate=$matches[2]',
		),
	);

	$permalink_structure = rtrim( str_replace( '%postname%/', '', $wp_rewrite->permalink_structure ), '/' );
	if ( isset( $patterns[ $permalink_structure ] )  ) {
		$post_base  = $patterns[ $permalink_structure ]['pattern'] . '([^/]+)/';
		$post_query = $patterns[ $permalink_structure ]['query'];
	} else {
		$post_base  = '(.?.+?)/';
		$post_query = '?name=$matches[1]&translate=$matches[2]';
	}

	$rewrite_rules = array(
		// Page
		'([^/]+)/translate/?([^/]+)/?$'       => '?pagename=$matches[1]&translate=$matches[2]',
		'([^/]+)/translate/?([^/]+)/embed/?$' => '?pagename=$matches[1]&translate=$matches[2]&embed=true',
		// Post
		$post_base . 'translate/?([^/]+)/?$'       => $post_query,
		$post_base . 'translate/?([^/]+)/embed/?$' => $post_query . '&embed=true',
	);

	foreach ( $rewrite_rules as $regex => $rewrite_rule ) {
		add_rewrite_rule(
			$regex,
			$wp_rewrite->index . $rewrite_rule,
			'top'
		);
	}
}
add_action( 'init', 'gutenblocks_translate_rewrite_rules' );

/**
 * Upgrade the DB Version.
 *
 * @since 1.4.0
 */
function gutenblocks_upgrade_db_version() {
	update_option( 'gutenblocks_version', gutenblocks_version() );

	return 1;
}

/**
 * Perform some upgrade tasks if needed.
 *
 * @since  1.2.0
 */
function gutenblocks_upgrade() {
	$db_version = gutenblocks_db_version();
	$version    = gutenblocks_version();

	if ( ! version_compare( $db_version, $version, '<' ) ) {
		return;
	}

	if ( (float) $db_version < 1.2 ) {
		delete_option( 'rewrite_rules' );
		flush_rewrite_rules( false );
	}

	if ( (float) $db_version < 1.4 ) {
		return;
	}

	// Update version.
	gutenblocks_upgrade_db_version();
}
add_action( 'admin_init', 'gutenblocks_upgrade', 100 );

/**
 * Build a page or post link to its translated version.
 *
 * @since  1.2.0
 *
 * @param  string $link     The permalink of the Post or the Page.
 * @param  string $language The locale of the translated version.
 * @return string           The link to display the translated version.
 */
function gutenblocks_translate_post_link( $link = '', $language = '' ) {
	if ( ! $language ) {
		$language = strtolower( str_replace( '_', '-', get_locale() ) );
	}

	if ( gutenblocks_are_urls_pretty() ) {
		$link = trailingslashit( $link ) . 'translate/' . $language . '/';
	} else {
		$link = add_query_arg( 'translate', $language, $link );
	}

	return $link;
}

/**
 * Build the language switcher on singular templates.
 *
 * @since  1.2.0
 *
 * @param  string $current The current locale to use for the blocks.
 * @return string          HTML Output.
 */
function gutenblocks_get_language_switcher( $current = '' ) {
	$locales     = gutenblocks_get_languages();
	$site_locale = get_locale();

	if ( ! $locales || ! did_action( 'wp_head' ) || ! is_singular() ) {
		return;
	}

	if ( ! $current ) {
		$current = $site_locale;
	}

	$switcher = '<ul class="gutenblocks-i18n-switcher">';

	foreach ( $locales as $locale ) {
		$class = '';
		if ( $current === $locale ) {
			$class = ' current-locale';
		}

		$link     = get_permalink();
		$language = strtolower( str_replace( '_', '-', $locale ) );

		if ( $site_locale !== $locale ) {
			$link = gutenblocks_translate_post_link( $link, $language );
		}

		$switcher .= sprintf( '<li class="nav-%1$s%2$s">
			<a href="%3$s">
				<span class="screen-reader-text">%4$s</span></a>
			</li>%5$s',
			$locale,
			$class,
			$link,
			$locale,
			"\n"
		);
	}

	return $switcher .= "</ul>\n";
}

/**
 * Gets the locale for GutenBlocks.
 *
 * @since  1.2.0
 *
 * @return string The locale for GutenBlocks.
 */
function gutenblocks_get_locale() {
	$locale = get_locale();
	$qv     = gutenblocks_get_locale_from_slug( get_query_var( 'translate' ) );

	if ( $qv ) {
		$locale = $qv;
	}

	return $locale;
}

/**
 * Returns a locale out of a language slug.
 *
 * @since  1.2.0
 *
 * @param  string $slug   The language slug.
 * @return boolean|string False if no locale was found. The locale otherwise.
 */
function gutenblocks_get_locale_from_slug( $slug = '' ) {
	if ( ! $slug ) {
		return false;
	}

	$country = substr( $slug, -2 );
	$locale = str_replace( '-' . $country, '_' . strtoupper( $country ), $slug );

	if ( false !== array_search( $locale, gutenblocks_get_languages() ) ) {
		return $locale;
	}

	return false;
}

/**
 * Returns the locale out of a URI.
 *
 * @since  1.2.0
 *
 * @param  string $uri    The URI to get the locale from.
 * @return boolean|string False if no locale was found. The locale otherwise.
 */
function gutenblocks_get_locale_from_uri( $uri = '' ) {
	if ( ! $uri ) {
		$uri = $_SERVER['REQUEST_URI'];
	}

	$parts        = wp_parse_url( $uri );
	$needs_switch = false;

	if ( gutenblocks_are_urls_pretty() ) {
		$path    = explode( '/', trim( $parts['path'], '/' ) );
		$t_index = array_search( 'translate', $path );

		if ( false !== $t_index ) {
			$needs_switch = $path[ $t_index + 1 ];
		}
	} else {
		$q = wp_parse_args( $parts['query'], array(
			'translate' => false,
		) );

		if ( $q['translate'] ) {
			$needs_switch = $q['translate'];
		}
	}

	return gutenblocks_get_locale_from_slug( $needs_switch );
}

/**
 * Only keeps the current locale version of the i18n Block.
 *
 * @since 1.2.0
 * @since 1.2.5 Adapts to InnerBlocks changes introduced in Gutenberg 3.5.
 * @since 1.2.6 Casts WP_Block_Parser_Block objects as arrays to keep
 *              compatibility with Gutenberg < 3.8.
 *
 * @param  string $content The Post content.
 * @return string          The Post content for the current locale.
 */
function gutenblocks_translate_blocks( $content = '' ) {
	if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST && false !== strpos( wp_parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_PATH ), '/wp-admin/' ) ) ) {
		return $content;
	}

	preg_match_all( '/\<section class\=\"wp-block-gutenblocks-i18n\"\>([\s\S]*?)\<\/section\>/', $content, $matches );

	if ( $matches[1] ) {
		$locale      = gutenblocks_get_locale();
		$localeblock = sprintf( 'gutenblocks/language-%s', str_replace( '_', '-', strtolower( $locale ) ) );

		foreach ( $matches[1] as $k => $m ) {
			$blocks = gutenberg_parse_blocks( $m );

			foreach( $blocks as $block ) {
				if ( is_object( $block ) ) {
					$block = (array) $block;
				}

				if ( is_object( $block['attrs'] ) ) {
					$block['attrs'] = (array) $block['attrs'];
				}

				if ( empty( $block['blockName'] ) || $localeblock === $block['blockName'] ) {
					continue;
				}

				if ( empty( $block['innerBlocks'] ) ) {
					continue;
				}

				foreach ( $block['innerBlocks'] as $innerblock ) {
					if ( is_object( $innerblock ) ) {
						$innerblock = (array) $innerblock;
					}

					// Remove all other languages blocks' content.
					$content = str_replace( $innerblock['innerHTML'], '', $content );
				}

				$parts = explode( '<!-- wp:' . $block['blockName'] .' -->', $content );

				if ( isset( $parts[1] ) ) {
					$content = reset( $parts );

					foreach ( $parts as $part ) {
						$end = explode( '<!-- /wp:' . $block['blockName'] .' -->', $part );

						if ( isset( $end[1] ) ) {
							$content .= end( $end );
						}
					}
				}
			}
		}

		if ( ! is_front_page() ) {
			$content = gutenblocks_get_language_switcher( $locale ) . $content;
		}

		if ( is_embed() && preg_match( '/\<span id\=\"more-\d*\"\>\<\/span\>/', $content, $more_matches ) ) {
			$teaser  = explode( $more_matches[0], $content, 2 );
			$content = reset( $teaser );
		}
	}

	return $content;
}
add_filter( 'the_content', 'gutenblocks_translate_blocks', 6 );

/**
 * Get the locale out of the URI and switch the site's one if needed.
 *
 * @since  1.2.0
 *
 * @param  string $url The requested URL.
 */
function gutenblocks_oembed_add_translate_filters( $url = '' ) {
	$locale = gutenblocks_get_locale_from_uri( $url );

	if ( $locale ) {
		switch_to_locale( $locale );

		// Make sure Post and Page permalinks are translated.
		add_filter( 'post_link', 'gutenblocks_translate_post_link', 10, 1 );
		add_filter( 'page_link', 'gutenblocks_translate_post_link', 10, 1 );
	}
}
add_action( 'embed_content_meta', 'gutenblocks_oembed_add_translate_filters', 9, 0 );

/**
 * Restore the site's locale & Page and Post permalinks if needed.
 *
 * @since 1.2.0
 */
function gutenblocks_oembed_remove_translate_filters() {
	if ( is_locale_switched() ) {
		remove_filter( 'post_link', 'gutenblocks_translate_post_link', 10, 1 );
		remove_filter( 'page_link', 'gutenblocks_translate_post_link', 10, 1 );
		restore_current_locale();
	}
}
add_action( 'embed_footer', 'gutenblocks_oembed_remove_translate_filters', 11 );

/**
 * Checks if switching locale is required for an embed request.
 *
 * @since 1.2.0
 *
 * @param  integer $page_id The current post type ID being embedded.
 * @param  string  $url     The embed url.
 * @return integer          The current post type ID being embedded.
 */
function gutenblocks_oembed_post_request_id( $page_id = 0, $url = '' ) {
	if ( ! $page_id ) {
		return $page_id;
	}

	gutenblocks_oembed_add_translate_filters( $url );

	return $page_id;
}
add_filter( 'oembed_request_post_id', 'gutenblocks_oembed_post_request_id', 10, 2 );

/**
 * Restore the current locale if an embed request needs it.
 *
 * @since 1.2.0
 *
 * @param  array  $data The embed data.
 * @return array        The embed data.
 */
function gutenblocks_oembed_response_data( $data = array() ) {
	gutenblocks_oembed_remove_translate_filters();

	return $data;
}
add_filter( 'oembed_response_data', 'gutenblocks_oembed_response_data', 11, 1 );
