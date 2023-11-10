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
		add_action( 'admin_post_upload_image_form_submission', array( $this, 'slideshow_image_upload_form_submission' ) );
	}

	public function slideshow_admin_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_media();
		wp_enqueue_script( 'media-upload' );
		wp_enqueue_script( 'jquery-ui-core', false, array( 'jquery' ), '1.13.1', true );
		wp_enqueue_script( 'jquery-ui-sortable', false, array( 'jquery' ), '1.13.1', true );
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
						} elseif ( isset( $_GET['submission'] ) && 'failed' == $_GET['submission'] && isset($_GET['reason']) && 'empty' == $_GET['reason'] ) { //phpcs:ignore
							echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Empty image url provided', '' ) . '</p></div>';
						} elseif ( isset( $_GET['submission'] ) && 'failed' == $_GET['submission'] && isset($_GET['reason']) && 'not_valid' == $_GET['reason'] ) { //phpcs:ignore
							echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Invalid image url provided', '' ) . '</p></div>';
						} elseif ( isset( $_GET['submission'] ) && 'failed' == $_GET['submission'] && isset($_GET['reason']) && 'duplicate' == $_GET['reason'] ) { //phpcs:ignore
							echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Duplicate image url provided', '' ) . '</p></div>';
						}

						?>
					</div>
					<form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" enctype="multipart/form-data">
						<input type="hidden" name="action" value="upload_image_form_submission">
						<h2>Upload Images</h2>
						<img src="https://picsum.photos/200" class="upload-image" id="uploaded-image"><br>
						<input type="hidden" id="upload-image-url" name="upload-image-url">
						<input type="button" class="button button-add-media upload-image-button" id="upload-image-button" name="upload-image" value="Browse Image">
						<br>
						<br>
						<?php wp_nonce_field( 'wp_slideshow_upload_image_nonce', 'wp_slideshow_upload_image_nonce_field' ); ?>
						<input type="submit" name="upload-image-submit" value="Submit Image" class="button button-primary">
						<script type="text/javascript">
							jQuery(document).ready( function ($){
								let file_frame;
								let wp_media_post_id = wp.media.model.settings.post.id;
								let set_to_post_id = 0;

								jQuery(document.body).on('click', '#upload-image-button', function(e) {
								   e.preventDefault();
								   if(file_frame ) {
									   file_frame.uploader.uploader.param('post_id', set_to_post_id);
									   file_frame.open();
									   return;
								   } else {
									   wp.media.model.settings.post.id = set_to_post_id;
								   }

								   file_frame = wp.media.frames.file_frame = wp.media({
									   title:'select a image to upload',
									   button: {
										   text: 'Use this image'
									   },
									   multiple: false
								   });

								   file_frame.on('select', function() {
									  var attachment = file_frame.state().get('selection').first().toJSON();
									  $('#uploaded-image').attr('src', attachment.url).css('width', '200');
									  $('#upload-image-url').val(attachment.url);

									  wp.media.model.settings.post.id = wp_media_post_id;
								   });

								   file_frame.open();
								});

								jQuery( 'a.add_media' ).on( 'click', function() {
									wp.media.model.settings.post.id = wp_media_post_id;
								});
								setTimeout(function() {
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
                        <?php
                            $all_images = get_option('slideshow_images');
                        ?>
						<ul id="all_img_sortable" class="wp_img_sortable">

						</ul>
					</div>
				</div>
			</div>
		<?php
	}

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
}

new Plugin();
