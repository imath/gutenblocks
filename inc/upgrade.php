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
 */
function gutenblocks_upgrade_photo_block() {
	$posts = gutenblocks_has_photo_block( 50 );

	if ( ! $posts ) {
		return true;
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
	}

	return false;
}

/**
 * Do we have at least one post using the Photo block ?
 * 
 * @since 1.4.0
 */
function gutenblocks_has_photo_block( $number = 1 ) {
	return get_posts( array(
		's'           => 'wp:gutenblocks/photo',
		'numberposts' => $number,
		'post_type'   => 'any',
	) );
}

/**
 * Manage upgrade actions & add inline script for the Auto upgrader.
 * 
 * @since 1.4.0
 */
function gutenblocks_upgrade_load() {
	$auto_upgrader = '';

	if ( isset( $_GET['action'] ) && 'upgrade' === $_GET['action'] ) {
		if ( gutenblocks_upgrade_photo_block() ) {
			// redirect using the upgraded action.
			wp_safe_redirect( add_query_arg( array(
				'page'   => 'gutenblocks-upgrade',
				'action' => 'upgraded',
			), admin_url( 'index.php' ) ) );

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
	$upgrade_url = add_query_arg( array(
		'page'   => 'gutenblocks-upgrade',
		'action' => 'upgrade',
	), admin_url( 'index.php' ) );

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
			update_option( 'gutenblocks_version', gutenblocks_version() );
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
	if ( is_network_admin() ) {
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
