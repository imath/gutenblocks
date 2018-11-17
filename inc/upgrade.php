<?php
/**
 * GutenBlocks Upgrade functions.
 *
 * @package GutenBlocks\inc
 *
 * @since  1.4.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Upgrade Photo blocks by replacing them with Image blocks.
 *
 * @since 1.4.0
 *
 * @param  integer $number The number of post objects to upgrade.
 * @return integer         The number of upgraded posts.
 */
function gutenblocks_upgrade_photo_block( $number = 50 ) {
	$posts    = gutenblocks_has_photo_block( $number );
	$upgraded = 0;

	if ( ! $posts ) {
		return $upgraded;
	}

	foreach ( $posts as $post ) {
		if ( ! has_block( 'gutenblocks/photo', $post ) ) {
			continue;
		}
		
		preg_match_all( '/<!--\s+wp:gutenblocks\/photo\s+(.*?)\s+-->([\s\S]*?)<!--\s+\/wp:gutenblocks\/photo\s+-->/', $post->post_content, $matches );

		if ( ! $matches || ! isset( $matches[1] ) || ! isset( $matches[2] ) ) {
			continue;
		}
		
		foreach ( $matches[1] as $k => $attrs ) {
			$attributes = json_decode( $attrs );
			$inner_html = $matches[2][ $k ];

			$replace = array(
				"\n",
				'wp-block-gutenblocks-photo',
				' style="max-width:100%"',
				sprintf( ' style="text-align:%s"', $attributes->alignment ),
				' align' . $attributes->alignment,
				sprintf( 'width="%1$d" height="%2$d"', $attributes->width, $attributes->height ),
			);

			$replace_by = array(
				'',
				'wp-block-image',
				'',
				'',
				'',
				'alt=""',
			);

			if ( isset( $attributes->alignment ) ) {
				if ( 'none' !== $attributes->alignment ) {
					$attributes->align = $attributes->alignment;
				}

				foreach ( array( 'width', 'height', 'alignment' ) as $key ) {
					unset( $attributes->{$key} );
				}
			}

			$img_attributes = ' ';
			if ( isset( $attributes->align ) ) {
				$img_attributes = ' ' . json_encode( $attributes ) . ' ';

				$replace_by[1] = 'align' . $attributes->align;
				$inner_html = '<div class="wp-block-image">' . $inner_html . '</div>';
			}

			$inner_html = str_replace( $replace, $replace_by, $inner_html );

			$image_block  = sprintf( '<!-- wp:image%1$s-->%2$s', $img_attributes, "\n" );
			$image_block .=  $inner_html . "\n";
			$image_block .=  '<!-- /wp:image -->';

			$post->post_content = str_replace( $matches[0][ $k ], $image_block, $post->post_content );
		}
		
		wp_update_post( $post );
		$upgraded += 1;
	}

	return $upgraded;
}

/**
 * Do we have at least one post using the Photo block ?
 *
 * @since 1.4.0
 *
 * @param  integer $number The number of posts to retrieve.
 * @return array           The post objects containing Photo blocks.
 */
function gutenblocks_has_photo_block( $number = 1 ) {
	return get_posts( array(
		's'           => 'wp:gutenblocks/photo',
		'numberposts' => $number,
		'post_type'   => 'any',
	) );
}

/**
 * Get the upgrade URL.
 *
 * @since 1.4.0
 *
 * @return string The upgrade URL.
 */
function gutenblocks_get_upgrade_url() {
	if ( function_exists( 'entrepot' ) ) {
		$upgrade_url = add_query_arg( array(
			'page'   => 'upgrade-repositories',
		), admin_url( 'plugins.php' ) );
	} else {
		$upgrade_url = add_query_arg( array(
			'page'   => 'gutenblocks-upgrade',
		), admin_url( 'index.php' ) );
	}

	return $upgrade_url;
}

/**
 * Manage upgrade actions & add inline script for the Auto upgrader.
 * 
 * @since 1.4.0
 */
function gutenblocks_upgrade_load() {
	$auto_upgrader = '';

	if ( isset( $_GET['action'] ) && 'upgrade' === $_GET['action'] ) {
		if ( 0 === gutenblocks_upgrade_photo_block() ) {
			// redirect using the upgraded action.
			wp_safe_redirect( add_query_arg( 'action', 'upgraded', gutenblocks_get_upgrade_url() ) );

			exit();
		}

		$auto_upgrader = "\n" . 'setTimeout( function() {
			var url = upgrader.getAttribute( \'href\' );
			upgrader.remove();

			// "Auto" Upgrade.
			location.href = url;
		}, 250 );';
	}

	wp_add_inline_script( 'common', sprintf( '
		( function() {
			var upgrader = document.querySelector( \'#gutenblocks-upgrade\' );

			upgrader.addEventListener( \'click\', function( event ) {
				event.target.remove();
			} );
			%s
		} )();
	', $auto_upgrader ) );
}
add_action( 'load-dashboard_page_gutenblocks-upgrade', 'gutenblocks_upgrade_load' );

/**
 * Upgrade Administration screen output.
 * 
 * @since 1.4.0
 */
function gutenblocks_upgrade_page() {
	$upgrade_url = add_query_arg( 'action', 'upgrade' ,gutenblocks_get_upgrade_url() );

	$upgrading = $upgraded = false;

	if ( isset( $_GET['action'] ) ) {
		$upgrading = 'upgrade'  === $_GET['action'];
		$upgraded  = 'upgraded' === $_GET['action'];
	}

	if ( $upgraded ) {
		$message = esc_html__( 'La mise à niveau est terminée. Merci de votre patience.', 'gutenblocks' );
		printf( '<div id="message" class="updated notice is-dismissible"><p>%s</p></div>', $message );
	}
	?>

	<div class="wrap">
		<h1><?php esc_html_e( 'Mise à niveau de GutenBlocks', 'gutenblocks' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Depuis la version 4.1 de Gutenberg, il est désormais possible d’utiliser une URL distante dans le bloc d’image.', 'gutenblocks' ); ?>
			<?php esc_html_e( 'Le bloc Photo n’est donc plus nécessaire et a été retiré de GutenBlocks.', 'gutenblocks' ); ?>
			<?php esc_html_e( 'Vous utilisez ce bloc dans certaines de vos publications, raison pour laquelle cette page de mise à niveau a été ajoutée à votre administration.', 'gutenblocks' ); ?>
			<?php esc_html_e( 'Cette mise à niveau consiste à remplacer toutes les occurences du bloc Photo par un bloc d’image.', 'gutenblocks' ); ?>
		</p>

		<?php if ( $upgrading ) : ?>
			<p><span class="attention"><?php esc_html_e( 'Mise à niveau en cours, merci de patienter. Si la page ne se recharge pas d’ici quelques secondes, merci de cliquer à nouveau sur le bouton « Mettre à niveau ».', 'gutenblocks' ); ?></span></p>
		<?php elseif ( ! $upgraded ) : ?>
			<p><span class="attention"><?php esc_html_e( 'Aussi, pour plus de précautions, il est recommandé de sauvegarder votre base de données avant de cliquer sur le bouton « Mettre à niveau ».', 'gutenblocks' ); ?></span></p>
		<?php endif; ?>
		
		<?php if ( ! $upgraded ) : ?>
			<div class="submit">
				<a href="<?php echo esc_url( $upgrade_url ); ?>" class="button button-primary" id="gutenblocks-upgrade">
					<?php esc_html_e( 'Mettre à niveau', 'gutenblocks' ); ?>
				</a>
			</div>
		<?php else :
			// Finally update the DB version to avoid loadinf this file at next page load.
			gutenblocks_upgrade_db_version();
		endif ; ?>
	</div>
	<?php
}

/**
 * Add a submenu to the dashboard to upgrade Gutenblocks.
 * 
 * @since 1.4.0
 */
function gutenblocks_upgrade_menu() {
	if ( is_network_admin() || function_exists( 'entrepot' ) ) {
		return;
	}

	$count = ' <span class="update-plugins update-count-1">1</span>';
	if ( isset( $_GET['action'] ) && 'upgraded' === $_GET['action'] ) {
		$count = '';
	}

	add_dashboard_page(
		__( 'Mise à niveau de GutenBlocks', 'gutenblocks' ),
		sprintf( __( 'Mise à niveau de GutenBlocks%s', 'gutenblocks' ), $count ),
		'manage_options',
		'gutenblocks-upgrade',
		'gutenblocks_upgrade_page'
	);
}
add_action( 'admin_menu', 'gutenblocks_upgrade_menu' );

/**
 * Get the number of post objects containing at least a Photo block.
 *
 * @since 1.4.0
 *
 * @return integer The number of post objects containing at least a Photo block.
 */
function gutenblocks_count_photo_blocks_inserted() {
	$wpdb = $GLOBALS['wpdb'];

	return (int) $wpdb->get_var( "SELECT count(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_content LIKE '%wp:gutenblocks/photo%'" );
}

/**
 * Registers upgrade routines into the Entrepôt Upgrade API.
 *
 * @since  1.4.0
 */
function gutenblocks_add_upgrade_routines() {
	$db_version = gutenblocks_db_version();
	// We are not using the Entrepôt Upgrade API for install.
	if ( 0 === (int) $db_version ) {
		return;
	}

	if ( version_compare( $db_version, gutenblocks_version(), '<' ) ) {
		entrepot_register_upgrade_tasks( 'gutenblocks', $db_version, array(
			'1.4.0' => array(
				array(
					'callback' => 'gutenblocks_upgrade_photo_block',
					'count'    => 'gutenblocks_count_photo_blocks_inserted',
					'message'  => _x( 'Remplacement des blocks Photo par des blocs d’image. Merci de patienter', 'Upgrader feedback message', 'gutenblocks' ),
					'number'   => 20,
				),
				array(
					'callback' => 'gutenblocks_upgrade_db_version',
					'count'    => '__return_true',
					'message'  => _x( 'Mise à jour de la version de l’extension', 'Upgrader feedback message', 'gutenblocks' ),
					'number'   => 1,
				),
			),
		) );
	}
}
add_action( 'entrepot_register_upgrade_tasks', 'gutenblocks_add_upgrade_routines' );

/**
 * Add an upgrade notice to the Dashboard.
 *
 * @since 1.4.0
 */
function gutenblocks_upgrade_notice() {
	$current_screen = get_current_screen();

	if ( isset( $current_screen->id ) && ( 'plugins_page_upgrade-repositories' === $current_screen->id || 'dashboard_page_gutenblocks-upgrade' === $current_screen->id ) ) {
		return;
	}
	?>
	<div id="message" class="update-nag">
		<p>
			<?php printf( __( 'Une mise à niveau est nécessaire pour Gutenblocks. Merci de vous rendre sur la page de %s.', 'gutenblocks' ),
				sprintf( '<a href="%1$s">%2$s</a>', esc_url( gutenblocks_get_upgrade_url() ), esc_html__( 'mise à niveau') )
			); ?>
		</p>
	</div>
	<?php
}
add_action( 'admin_notices', 'gutenblocks_upgrade_notice' );
