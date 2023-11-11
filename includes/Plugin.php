<?php

namespace WP\SS;

// don't call the file directly.
defined( 'ABSPATH' ) || exit();

class Plugin {
	/**
	 * Class constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'slideshow_admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'slideshow_admin_menu_page' ) );
		add_action( 'admin_post_upload_image_form_submission', array(
			$this,
			'slideshow_image_upload_form_submission'
		) );
		add_action( 'wp_ajax_slideshow_image_remove', array( $this, 'slideshow_image_remove' ) );
		add_action( 'wp_ajax_slideshow_image_rearrange', array( $this, 'slideshow_image_rearrange' ) );
	}

	/**
	 * Slideshow page content function callback.
	 */
	public static function wp_slideshow_page_callback() {
		?>
        <div class="wrap">
            <h1><?php esc_html_e( 'WordPress Slideshow', '' ); ?></h1>
            <p><?php esc_html_e( 'You can upload images from this page', '' ); ?></p>
            <div class="wp_slide_settings_content">
                <div class="wp_slide_settings_notice">
					<?php
					if ( isset( $_GET['submission'] ) && 'success' == $_GET['submission'] ) {//phpcs:ignore
						echo '<div class="notice notice-success is-dismissible updated"><p>' . esc_html__( 'Image upload successful', '' ) . '</p></div>';
					} elseif ( isset( $_GET['submission'] ) && 'failed' == $_GET['submission'] && isset( $_GET['reason'] ) && 'empty' == $_GET['reason'] ) { //phpcs:ignore
						echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Empty image url provided', '' ) . '</p></div>';
					} elseif ( isset( $_GET['submission'] ) && 'failed' == $_GET['submission'] && isset( $_GET['reason'] ) && 'not_valid' == $_GET['reason'] ) { //phpcs:ignore
						echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid image url provided', '' ) . '</p></div>';
					} elseif ( isset( $_GET['submission'] ) && 'failed' == $_GET['submission'] && isset( $_GET['reason'] ) && 'duplicate' == $_GET['reason'] ) { //phpcs:ignore
						echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Duplicate image url provided', '' ) . '</p></div>';
					}

					?>
                </div>
                <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post"
                      enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_image_form_submission">
                    <h2><?php esc_html_e( 'Upload Images', '' ); ?></h2>
                    <img src="<?php echo WORDPRESS_SLIDESHOW_ASSETS . '/images/placeholder.png'; ?>"
                         class="upload-image" id="uploaded-image"><br>
                    <input type="hidden" id="upload-image-url" name="upload-image-url">
                    <input type="button" class="button button-add-media upload-image-button" id="upload-image-button"
                           name="upload-image" value="Browse Image">
                    <br>
                    <br>
					<?php wp_nonce_field( 'wp_slideshow_upload_image_nonce', 'wp_slideshow_upload_image_nonce_field' ); ?>
                    <input type="submit" name="upload-image-submit" value="Submit Image" class="button button-primary">
                    <script type="text/javascript">
                        jQuery(document).ready(function ($) {
                            let file_frame;
                            let wp_media_post_id = wp.media.model.settings.post.id;
                            let set_to_post_id = 0;

                            jQuery(document.body).on('click', '#upload-image-button', function (e) {
                                e.preventDefault();
                                if (file_frame) {
                                    file_frame.uploader.uploader.param('post_id', set_to_post_id);
                                    file_frame.open();
                                    return;
                                } else {
                                    wp.media.model.settings.post.id = set_to_post_id;
                                }

                                file_frame = wp.media.frames.file_frame = wp.media({
                                    title: 'select a image to upload',
                                    button: {
                                        text: 'Use this image'
                                    },
                                    multiple: false
                                });

                                file_frame.on('select', function () {
                                    var attachment = file_frame.state().get('selection').first().toJSON();
                                    $('#uploaded-image').attr('src', attachment.url).css('width', '200');
                                    $('#upload-image-url').val(attachment.url);

                                    wp.media.model.settings.post.id = wp_media_post_id;
                                });

                                file_frame.open();
                            });

                            jQuery('a.add_media').on('click', function () {
                                wp.media.model.settings.post.id = wp_media_post_id;
                            });
                            setTimeout(function () {
                                // Get the current URL
                                var currentURL = window.location.href;

                                // Remove the parameters by replacing the URL with the base URL
                                var baseURL = currentURL.split('?')[0] + '?page=wordpress_slideshow_page';
                                window.history.replaceState({}, document.title, baseURL);
                            }, 10000);
                        });
                    </script>
                </form>

                <div class="wp_slideshow_images">
                    <h3><?php esc_html_e( "Uploaded Images", "" ); ?></h3>
					<?php
					$all_images = get_option( 'slideshow_images' );
					?>
                    <ul id="all_img_sortable" class="wp_img_sortable">
						<?php if ( is_array( $all_images ) && ! empty( $all_images ) ) {
							foreach ( $all_images as $key => $single_image ) {
								echo '<li class="ui-state-default single-image" id="single_image_' . $key . '" data-option_id="' . $key . '">
								    <img src="' . $single_image . '" class="single_iamge" height="100" width="100"><br>
								    <button class="button button-link-delete remove_image">Remove Image</button>
								</li> ';
							}
						}


						?>
                    </ul>
                </div>
            </div>
        </div>
		<?php
	}

	/**
	 * Enqueue admin scripts and styles.
	 */
	public function slideshow_admin_scripts() {
		$js_url = wordpress_slideshow()->plugin_url() . '/assets/js';
		wp_enqueue_script( 'jquery' );
		wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'jquery-ui-core', false, array( 'jquery' ), '1.13.1', true );
		wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery' ), '1.13.1', true );
		wp_enqueue_script( 'wp_slideshow-admin-scripts', $js_url . '/admin-scripts.js', array(
			'jquery',
			'jquery-ui-sortable'
		), '1.0.0', array( 'in_footer' => true ) );
		wp_localize_script( 'wp_slideshow-admin-scripts', 'wp_slideshow_admin_ajax', array(
			'ajax_url' => admin_url( 'admin-ajax.php' )
		) );
	}

	/**
	 * Create an admin menu page from where the slideshow images will be updated.
	 *
	 * @return void
	 */
	public function slideshow_admin_menu_page() {
		add_menu_page(
			__( 'WordPress Slideshow', '' ),
			__( 'WordPress Slideshow', '' ),
			'manage_options',
			'wordpress_slideshow_page',
			array( __CLASS__, 'wp_slideshow_page_callback' ),
			'dashicons-slides',
			70
		);
	}

	/**
	 *
	 */
	public function slideshow_image_upload_form_submission() {
		if ( ! isset( $_POST['wp_slideshow_upload_image_nonce_field'] ) || ! wp_verify_nonce( $_POST['wp_slideshow_upload_image_nonce_field'], 'wp_slideshow_upload_image_nonce' ) ) {
			wp_die( 'Security check failed. Please try again.' );
		}
		if ( isset( $_POST['upload-image-submit'] ) ) {
			$submitted_image_url = esc_url_raw( $_POST['upload-image-url'] );
			if ( empty( $submitted_image_url ) ) {
				wp_safe_redirect( admin_url( 'admin.php?page=wordpress_slideshow_page&submission=failed&reason=empty' ) );
			} else {
				if ( false === filter_var( $submitted_image_url, FILTER_VALIDATE_URL ) ) {
					wp_safe_redirect( admin_url( 'admin.php?page=wordpress_slideshow_page&submission=failed&reason=not_valid' ) );
					exit;
				}

				if ( false !== filter_var( $submitted_image_url, FILTER_VALIDATE_URL ) ) {
					$slideshow_images = get_option( 'slideshow_images', array() );
					if ( in_array( $submitted_image_url, $slideshow_images, true ) ) {
						wp_safe_redirect( admin_url( 'admin.php?page=wordpress_slideshow_page&submission=failed&reason=duplicate' ) );
						exit;
					}
				}
				$slideshow_images   = get_option( 'slideshow_images', array() );
				$slideshow_images[] = $submitted_image_url;

				update_option( 'slideshow_images', $slideshow_images );

				wp_safe_redirect( admin_url( 'admin.php?page=wordpress_slideshow_page&submission=success' ) );
			}
			exit;
		}
	}

	public function slideshow_image_remove() {
		$position = isset( $_POST['position'] ) ? $_POST['position'] : '';

		$slideshow_images = get_option( 'slideshow_images', array() );
		if ( ! array_key_exists( $position, $slideshow_images ) ) {
			wp_send_json_error( array( 'error_message' => 'No position is given' ), 400 );
		}

		if ( array_key_exists( $position, $slideshow_images ) ) {
			unset( $slideshow_images[ $position ] );
			$slideshow_images = array_values( $slideshow_images );
		}

		update_option( 'slideshow_images', $slideshow_images );

		$updated_html = '';
		foreach ( $slideshow_images as $key => $slideshow_image ) {
			$updated_html .= '<li class="ui-state-default single-image ui-sortable" id="single_image_' . $key . '" data-option_id="' . $key . '"><img src="' . $slideshow_image . '" class="slideshow_image" height="100" width="100"><br><button class="button button-link-delete remove_image">Remove Image</button>';

		}
		wp_send_json_success( array(
			'success' => true,
			'html'    => $updated_html
		) );

		wp_die();
	}

	public function slideshow_image_rearrange() {
		$positions         = isset( $_POST['positions'] ) ? $_POST['positions'] : '';
		$positions         = explode( ",", $positions );
		$updated_positions = array();
		if ( is_array( $positions ) && ! empty( $positions ) ) {
			foreach ( $positions as $position ) {
				$index               = str_replace( "single_image_", "", $position );
				$updated_positions[] = (int) $index;
			}
		}

		$slideshow_images = get_option( 'slideshow_images', array() );
		array_multisort( $updated_positions, $slideshow_images );

		update_option( 'slideshow_images', $slideshow_images );

		$updated_html = '';
		foreach ( $slideshow_images as $key => $slideshow_image ) {
			$updated_html .= '<li class="ui-state-default single-image ui-sortable" id="single_image_' . $key . '" data-option_id="' . $key . '"><img src="' . $slideshow_image . '" class="slideshow_image" height="100" width="100"><br><button class="button button-link-delete remove_image">Remove Image</button>';

		}
		wp_send_json_success( array(
			'success' => true,
			'html'    => $updated_html
		) );

		wp_die();
	}
}

new Plugin();
