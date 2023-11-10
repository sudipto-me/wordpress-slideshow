<?php
/**
 * Plugin Name: WordPress-Slideshow
 * Plugin URI:  https://www.pluginever.com/plugins/wocommerce-serial-numbers-pro/
 * Description: This will show slideshows on the WordPress frontend.
 * Version:     1.0.0
 * Author:      Sudipto Shakhari
 * Author URI:  https://shakahri.cc/
 * License:     GPLv2+
 * Text Domain: wordpress-slideshow
 * Domain Path: /languages
 * Tested up to: 6.2
 *
 * @package WordPressSlideShow
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * The main plugin class
 */
final class WordPress_Slideshow {
	/**
	 * Plugin version
	 *
	 * @var string
	 */
	const VERSION = '1.0';

	/**
	 * @var string
	*/
	private $plugin_path;

	/**
	 * @var WordPressSettingsFramework
	*/
	private $wpsf;

	/**
	 * Class constructor.
	 */
	private function __construct() {
		$this->define_constants();
		$this->plugin_path = plugin_dir_path( __FILE__ );

		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
		require_once $this->plugin_path . 'lib/wp-settings-framework/wp-settings-framework.php';
		$this->wpsf = new WordPressSettingsFramework( $this->plugin_path . 'includes/admin/settings/settings-general.php', 'slideshow_settings_general' );
		add_action( 'admin_menu', array( $this, 'add_settings_page' ), 20 );
		add_filter( $this->wpsf->get_option_group() . '_settings_validate', array( &$this, 'validate_settings' ) );
	}

	/**
	 * Initializes a singleton instance.
	 *
	 * @return bool
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Define the required plugin constants
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'WORDPRESS_SLIDESHOW_VERSION', self::VERSION );
		define( 'WORDPRESS_SLIDESHOW_FILE', __FILE__ );
		define( 'WORDPRESS_SLIDESHOW_PATH', __DIR__ );
		define( 'WORDPRESS_SLIDESHOW_URL', plugins_url( '', WORDPRESS_SLIDESHOW_FILE ) );
		define( 'WORDPRESS_SLIDESHOW_ASSETS', WORDPRESS_SLIDESHOW_URL . '/assets' );
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {
		new WP\SS\Plugin();
	}

	/**
	 * Do stuff upon plugin activation
	 *
	 * @return void
	 */
	public function activate() {
		$installer = new \WP\SS\Installer();
		$installer->run();
	}

	public function add_settings_page() {
		$this->wpsf->add_settings_page(
			array(
				'page_slug'    => 'wp-slideshow',
				'page_title'   => __( 'WordPress Slideshow', 'wordpress-slideshow' ),
				'parent_title' => __( 'WordPress Slideshow', 'wordpress-slideshow' ),
				'menu_title'   => __( 'WordPress Slideshow', 'wordpress-slideshow' ),
				'capability'   => 'manage_options',
			)
		);
	}

	public function validate_settings( $input ) {
		return $input;
	}
}
/**
 * Initializes the main plugin
 *
 * @return bool
 */
function wordpress_slideshow() {
	return WordPress_Slideshow::init();
}

// kick-off the plugin
wordpress_slideshow();
