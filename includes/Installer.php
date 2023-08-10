<?php // phpcs:ignore

namespace WP\SS;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

/**
 * Installer class
*/
class Installer {
	/**
	 * Run the installer
	 *
	 * @return void
	 */
	public function run() {
		$this->add_version();
	}

	/**
	 * Add time and version on DB.
	 *
	 * @return void.
	 */
	public function add_version() {
		$installed = get_option( 'wp_ss_version' );

		if ( ! $installed ) {
			update_option( 'wp_ss_installed', time() );
		}

		update_option( 'wp_ss_version', WORDPRESS_SLIDESHOW_VERSION );
	}
}
