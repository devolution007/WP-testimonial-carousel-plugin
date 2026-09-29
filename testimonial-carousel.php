<?php
/**
 * Plugin Name: Testimonial Carousel
 * Description: Manage testimonials in WordPress and display them in a responsive autoplay carousel with animated popups.
 * Version: 1.1.0
 * Author: Devolution
 * Text Domain: testimonial-carousel
 * Requires at least: 6.2
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BMTC_VERSION', '1.1.0' );
define( 'BMTC_FILE', __FILE__ );
define( 'BMTC_DIR', plugin_dir_path( __FILE__ ) );
define( 'BMTC_URL', plugin_dir_url( __FILE__ ) );

final class Testimonial_Carousel {
	const POST_TYPE = 'bmtc_testimonial';
	const NONCE_ACTION = 'bmtc_save_testimonial';
	const NONCE_NAME = 'bmtc_testimonial_nonce';

	public static function init() {
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_action( 'init', array( __CLASS__, 'register_assets' ) );
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_meta_boxes' ) );
		add_action( 'save_post_' . self::POST_TYPE, array( __CLASS__, 'save_meta' ) );
		add_filter( 'manage_' . self::POST_TYPE . '_posts_columns', array( __CLASS__, 'admin_columns' ) );
		add_action( 'manage_' . self::POST_TYPE . '_posts_custom_column', array( __CLASS__, 'admin_column_content' ), 10, 2 );
		add_action( 'pre_get_posts', array( __CLASS__, 'admin_order' ) );
		add_action( 'admin_notices', array( __CLASS__, 'shortcode_notice' ) );
		add_action( 'admin_menu', array( __CLASS__, 'register_import_export_page' ) );
		add_action( 'admin_post_bmtc_export_testimonials', array( __CLASS__, 'export_testimonials' ) );
		add_action( 'admin_post_bmtc_import_testimonials', array( __CLASS__, 'import_testimonials' ) );
		add_shortcode( 'testimonial-carousel', array( __CLASS__, 'shortcode' ) );
	}

	public static function register_post_type() {
		$labels = array(
			'name'               => __( 'Testimonials', 'testimonial-carousel' ),
			'singular_name'      => __( 'Testimonial', 'testimonial-carousel' ),
			'add_new'            => __( 'Add New', 'testimonial-carousel' ),
			'add_new_item'       => __( 'Add New Testimonial', 'testimonial-carousel' ),
			'edit_item'          => __( 'Edit Testimonial', 'testimonial-carousel' ),
			'new_item'           => __( 'New Testimonial', 'testimonial-carousel' ),
			'view_item'          => __( 'View Testimonial', 'testimonial-carousel' ),
			'search_items'       => __( 'Search Testimonials', 'testimonial-carousel' ),
			'not_found'          => __( 'No testimonials found.', 'testimonial-carousel' ),
			'not_found_in_trash' => __( 'No testimonials found in Trash.', 'testimonial-carousel' ),
			'menu_name'          => __( 'Testimonials', 'testimonial-carousel' ),
		);

		register_post_type(
			self::POST_TYPE,
			array(
				'labels'             => $labels,
				'public'             => false,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'show_in_rest'       => true,
				'menu_icon'          => 'dashicons-format-quote',
				'supports'           => array( 'title', 'editor', 'page-attributes' ),
				'capability_type'    => 'post',
				'map_meta_cap'       => true,
				'has_archive'        => false,
				'rewrite'            => false,
				'publicly_queryable' => false,
			)
		);
	}

	public static function register_assets() {
		wp_register_style( 'bmtc-carousel', BMTC_URL . 'assets/css/carousel.css', array(), BMTC_VERSION );
		wp_register_script( 'bmtc-carousel', BMTC_URL . 'assets/js/carousel.js', array(), BMTC_VERSION, true );
	}

	public static function add_meta_boxes() {
		add_meta_box(
			'bmtc_details',
			__( 'Testimonial Footer Details', 'testimonial-carousel' ),
			array( __CLASS__, 'render_meta_box' ),
			self::POST_TYPE,
			'normal',
			'high'
		);
	}

	public static function render_meta_box( $post ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
		$author       = get_post_meta( $post->ID, '_bmtc_author', true );
		$relationship = get_post_meta( $post->ID, '_bmtc_relationship', true );
		$campus       = get_post_meta( $post->ID, '_bmtc_campus', true );
		$initials     = get_post_meta( $post->ID, '_bmtc_initials', true );
		?>
		<p>
			<label for="bmtc_author"><strong><?php esc_html_e( 'Author / footer name', 'testimonial-carousel' ); ?></strong></label><br>
			<input class="widefat" type="text" id="bmtc_author" name="bmtc_author" value="<?php echo esc_attr( $author ); ?>" placeholder="<?php esc_attr_e( 'e.g. Mother of Zaid', 'testimonial-carousel' ); ?>">
		</p>
		<p>
			<label for="bmtc_relationship"><strong><?php esc_html_e( 'Relationship line (optional)', 'testimonial-carousel' ); ?></strong></label><br>
			<input class="widefat" type="text" id="bmtc_relationship" name="bmtc_relationship" value="<?php echo esc_attr( $relationship ); ?>" placeholder="<?php esc_attr_e( 'e.g. Parent of Syed Moosa Bukhari & Syed Abdullah Bukhari', 'testimonial-carousel' ); ?>">
		</p>
		<p>
			<label for="bmtc_campus"><strong><?php esc_html_e( 'Campus', 'testimonial-carousel' ); ?></strong></label><br>
			<input class="widefat" type="text" id="bmtc_campus" name="bmtc_campus" value="<?php echo esc_attr( $campus ); ?>" placeholder="<?php esc_attr_e( 'e.g. Hifz Campus', 'testimonial-carousel' ); ?>">
		</p>
		<p>
			<label for="bmtc_initials"><strong><?php esc_html_e( 'Avatar initials', 'testimonial-carousel' ); ?></strong></label><br>
			<input type="text" id="bmtc_initials" name="bmtc_initials" maxlength="4" value="<?php echo esc_attr( $initials ); ?>" placeholder="MS">
		</p>
		<p class="description"><?php esc_html_e( 'Use the Order field in Page Attributes to control the card order. Lower numbers appear first. The main editor is the testimonial text; keep author details in these footer fields.', 'testimonial-carousel' ); ?></p>
		<?php
	}

	public static function save_meta( $post_id ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ self::NONCE_NAME ] ) ), self::NONCE_ACTION ) ) {
			return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		$fields = array(
			'bmtc_author'       => '_bmtc_author',
			'bmtc_relationship' => '_bmtc_relationship',
			'bmtc_campus'       => '_bmtc_campus',
			'bmtc_initials'     => '_bmtc_initials',
		);

		foreach ( $fields as $input => $meta_key ) {
			$value = isset( $_POST[ $input ] ) ? sanitize_text_field( wp_unslash( $_POST[ $input ] ) ) : '';
			if ( '' === $value ) {
				delete_post_meta( $post_id, $meta_key );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	public static function admin_columns( $columns ) {
		return array(
			'cb'          => $columns['cb'],
			'title'       => __( 'Title', 'testimonial-carousel' ),
			'bmtc_author' => __( 'Footer name', 'testimonial-carousel' ),
			'bmtc_campus' => __( 'Campus', 'testimonial-carousel' ),
			'menu_order'  => __( 'Order', 'testimonial-carousel' ),
			'date'        => $columns['date'],
		);
	}

	public static function admin_column_content( $column, $post_id ) {
		if ( 'bmtc_author' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_bmtc_author', true ) );
		} elseif ( 'bmtc_campus' === $column ) {
			echo esc_html( get_post_meta( $post_id, '_bmtc_campus', true ) );
		} elseif ( 'menu_order' === $column ) {
			echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
		}
	}

	public static function admin_order( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}
		if ( self::POST_TYPE === $query->get( 'post_type' ) && ! $query->get( 'orderby' ) ) {
			$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'ASC' ) );
		}
	}

	public static function shortcode_notice() {
		$screen = get_current_screen();
		if ( ! $screen || self::POST_TYPE !== $screen->post_type ) {
			return;
		}
		?>
		<div class="notice notice-info"><p><?php esc_html_e( 'Display the carousel with:', 'testimonial-carousel' ); ?> <code>[testimonial-carousel]</code></p></div>
		<?php
	}

	public static function register_import_export_page() {
		add_submenu_page(
			'edit.php?post_type=' . self::POST_TYPE,
			__( 'Import / Export Testimonials', 'testimonial-carousel' ),
			__( 'Import / Export', 'testimonial-carousel' ),
			'manage_options',
			'bmtc-import-export',
			array( __CLASS__, 'render_import_export_page' )
		);
	}

	public static function render_import_export_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$notice = get_transient( 'bmtc_import_notice_' . get_current_user_id() );
		if ( $notice ) {
			delete_transient( 'bmtc_import_notice_' . get_current_user_id() );
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Import / Export Testimonials', 'testimonial-carousel' ); ?></h1>
			<?php if ( is_array( $notice ) ) : ?>
				<div class="notice notice-<?php echo esc_attr( $notice['type'] ); ?> is-dismissible"><p><?php echo esc_html( $notice['message'] ); ?></p></div>
			<?php endif; ?>

			<div class="card" style="max-width:760px;padding:8px 22px 20px;margin-top:20px">
				<h2><?php esc_html_e( 'Export testimonials', 'testimonial-carousel' ); ?></h2>
				<p><?php esc_html_e( 'Download all published and unpublished testimonials, including their footer fields, order, and status, as a portable JSON file.', 'testimonial-carousel' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="bmtc_export_testimonials">
					<?php wp_nonce_field( 'bmtc_export_testimonials' ); ?>
					<?php submit_button( __( 'Download Export File', 'testimonial-carousel' ), 'primary', 'submit', false ); ?>
				</form>
				<p><a href="<?php echo esc_url( BMTC_URL . 'data/testimonial-carousel-import.json' ); ?>" download><?php esc_html_e( 'Download the bundled 12-testimonial import file', 'testimonial-carousel' ); ?></a></p>
			</div>

			<div class="card" style="max-width:760px;padding:8px 22px 20px;margin-top:20px">
				<h2><?php esc_html_e( 'Import testimonials', 'testimonial-carousel' ); ?></h2>
				<p><?php esc_html_e( 'Upload a JSON file exported by this plugin. The recommended mode updates matching testimonials and adds only new ones.', 'testimonial-carousel' ); ?></p>
				<form method="post" enctype="multipart/form-data" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="bmtc_import_testimonials">
					<?php wp_nonce_field( 'bmtc_import_testimonials' ); ?>
					<p><input type="file" name="bmtc_import_file" accept="application/json,.json" required></p>
					<p>
						<label for="bmtc_import_mode"><strong><?php esc_html_e( 'Import mode', 'testimonial-carousel' ); ?></strong></label><br>
						<select id="bmtc_import_mode" name="bmtc_import_mode">
							<option value="upsert"><?php esc_html_e( 'Update matching testimonials and add new ones (recommended)', 'testimonial-carousel' ); ?></option>
							<option value="append"><?php esc_html_e( 'Add every testimonial as new', 'testimonial-carousel' ); ?></option>
						</select>
					</p>
					<?php submit_button( __( 'Import Testimonials', 'testimonial-carousel' ), 'primary', 'submit', false ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	private static function migration_id( $author, $content ) {
		return 'tc-' . substr( hash( 'sha256', strtolower( trim( wp_strip_all_tags( $author ) ) ) . '|' . trim( wp_strip_all_tags( $content ) ) ), 0, 24 );
	}

	private static function portable_testimonial( $post_id ) {
		$author       = (string) get_post_meta( $post_id, '_bmtc_author', true );
		$relationship = (string) get_post_meta( $post_id, '_bmtc_relationship', true );
		$campus       = (string) get_post_meta( $post_id, '_bmtc_campus', true );
		$initials     = (string) get_post_meta( $post_id, '_bmtc_initials', true );
		$content      = (string) get_post_field( 'post_content', $post_id );
		$migration_id = (string) get_post_meta( $post_id, '_bmtc_migration_id', true );
		if ( ! $migration_id ) {
			$migration_id = self::migration_id( $author, $content );
			update_post_meta( $post_id, '_bmtc_migration_id', $migration_id );
		}
		return array(
			'migration_id' => $migration_id,
			'title'        => (string) get_the_title( $post_id ),
			'content'      => $content,
			'author'       => $author,
			'relationship' => $relationship,
			'campus'       => $campus,
			'initials'     => $initials,
			'order'        => (int) get_post_field( 'menu_order', $post_id ),
			'status'       => (string) get_post_status( $post_id ),
		);
	}

	public static function export_testimonials() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to export testimonials.', 'testimonial-carousel' ) );
		}
		check_admin_referer( 'bmtc_export_testimonials' );
		$posts = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
				'posts_per_page' => -1,
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
				'order'          => 'ASC',
			)
		);
		$items = array();
		foreach ( $posts as $post ) {
			$items[] = self::portable_testimonial( $post->ID );
		}
		$payload = array(
			'schema_version' => 1,
			'plugin'         => 'testimonial-carousel',
			'exported_at'    => gmdate( 'c' ),
			'testimonials'   => $items,
		);
		nocache_headers();
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="testimonial-carousel-export-' . gmdate( 'Y-m-d' ) . '.json"' );
		echo wp_json_encode( $payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON download.
		exit;
	}

	private static function import_notice( $type, $message ) {
		set_transient(
			'bmtc_import_notice_' . get_current_user_id(),
			array( 'type' => $type, 'message' => $message ),
			60
		);
		wp_safe_redirect( admin_url( 'edit.php?post_type=' . self::POST_TYPE . '&page=bmtc-import-export' ) );
		exit;
	}

	public static function import_testimonials() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to import testimonials.', 'testimonial-carousel' ) );
		}
		check_admin_referer( 'bmtc_import_testimonials' );
		if ( empty( $_FILES['bmtc_import_file'] ) || ! is_array( $_FILES['bmtc_import_file'] ) ) {
			self::import_notice( 'error', __( 'Please select a JSON import file.', 'testimonial-carousel' ) );
		}
		$file = $_FILES['bmtc_import_file']; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Validated as an uploaded temporary file below.
		if ( ! isset( $file['error'], $file['tmp_name'], $file['size'], $file['name'] ) || UPLOAD_ERR_OK !== (int) $file['error'] || ! is_uploaded_file( $file['tmp_name'] ) ) {
			self::import_notice( 'error', __( 'The uploaded file could not be read.', 'testimonial-carousel' ) );
		}
		if ( (int) $file['size'] > 5 * MB_IN_BYTES || 'json' !== strtolower( pathinfo( sanitize_file_name( $file['name'] ), PATHINFO_EXTENSION ) ) ) {
			self::import_notice( 'error', __( 'Upload a valid JSON file no larger than 5 MB.', 'testimonial-carousel' ) );
		}
		$decoded = json_decode( (string) file_get_contents( $file['tmp_name'] ), true );
		$items   = isset( $decoded['testimonials'] ) ? $decoded['testimonials'] : $decoded;
		if ( ! is_array( $items ) || count( $items ) > 2000 ) {
			self::import_notice( 'error', __( 'The import file has an invalid structure or too many records.', 'testimonial-carousel' ) );
		}
		$mode = isset( $_POST['bmtc_import_mode'] ) ? sanitize_key( wp_unslash( $_POST['bmtc_import_mode'] ) ) : 'upsert';
		if ( ! in_array( $mode, array( 'upsert', 'append' ), true ) ) {
			$mode = 'upsert';
		}

		$existing_by_id = array();
		if ( 'upsert' === $mode ) {
			$existing_ids = get_posts(
				array(
					'post_type'      => self::POST_TYPE,
					'post_status'    => 'any',
					'posts_per_page' => -1,
					'fields'         => 'ids',
				)
			);
			foreach ( $existing_ids as $existing_id ) {
				$key = (string) get_post_meta( $existing_id, '_bmtc_migration_id', true );
				if ( ! $key ) {
					$key = self::migration_id(
						(string) get_post_meta( $existing_id, '_bmtc_author', true ),
						(string) get_post_field( 'post_content', $existing_id )
					);
					update_post_meta( $existing_id, '_bmtc_migration_id', $key );
				}
				$existing_by_id[ $key ] = (int) $existing_id;
			}
		}

		$added = 0;
		$updated = 0;
		$skipped = 0;
		foreach ( $items as $index => $item ) {
			if ( ! is_array( $item ) ) {
				++$skipped;
				continue;
			}
			$author       = sanitize_text_field( $item['author'] ?? '' );
			$content      = wp_kses_post( $item['content'] ?? '' );
			$title        = sanitize_text_field( $item['title'] ?? $author );
			$relationship = sanitize_text_field( $item['relationship'] ?? '' );
			$campus       = sanitize_text_field( $item['campus'] ?? '' );
			$initials     = sanitize_text_field( $item['initials'] ?? '' );
			$migration_id = sanitize_key( $item['migration_id'] ?? '' );
			$migration_id = $migration_id ? $migration_id : self::migration_id( $author, $content );
			$status       = sanitize_key( $item['status'] ?? 'publish' );
			$status       = in_array( $status, array( 'publish', 'draft', 'pending', 'private' ), true ) ? $status : 'publish';
			if ( '' === $author || '' === trim( wp_strip_all_tags( $content ) ) ) {
				++$skipped;
				continue;
			}
			$postarr = array(
				'post_type'    => self::POST_TYPE,
				'post_status'  => $status,
				'post_title'   => $title ? $title : $author,
				'post_content' => wp_slash( $content ),
				'menu_order'   => isset( $item['order'] ) ? intval( $item['order'] ) : $index + 1,
			);
			if ( 'upsert' === $mode && isset( $existing_by_id[ $migration_id ] ) ) {
				$postarr['ID'] = $existing_by_id[ $migration_id ];
			}
			$post_id = wp_insert_post( $postarr, true );
			if ( is_wp_error( $post_id ) ) {
				++$skipped;
				continue;
			}
			if ( isset( $postarr['ID'] ) ) {
				++$updated;
			} else {
				++$added;
			}
			$meta = array(
				'_bmtc_author'       => $author,
				'_bmtc_relationship' => $relationship,
				'_bmtc_campus'       => $campus,
				'_bmtc_initials'     => $initials,
				'_bmtc_migration_id' => $migration_id,
			);
			foreach ( $meta as $key => $value ) {
				if ( '' === $value ) {
					delete_post_meta( $post_id, $key );
				} else {
					update_post_meta( $post_id, $key, $value );
				}
			}
		}

		self::import_notice(
			'success',
			sprintf(
				/* translators: 1: added count, 2: updated count, 3: skipped count. */
				__( 'Import complete: %1$d added, %2$d updated, %3$d skipped.', 'testimonial-carousel' ),
				$added,
				$updated,
				$skipped
			)
		);
	}

	private static function initials( $author ) {
		$words = preg_split( '/\s+/', trim( wp_strip_all_tags( $author ) ) );
		$words = array_values( array_filter( $words ) );
		if ( empty( $words ) ) {
			return 'TB';
		}
		$first = function_exists( 'mb_substr' ) ? mb_substr( $words[0], 0, 1 ) : substr( $words[0], 0, 1 );
		$last  = count( $words ) > 1 ? ( function_exists( 'mb_substr' ) ? mb_substr( end( $words ), 0, 1 ) : substr( end( $words ), 0, 1 ) ) : '';
		return strtoupper( $first . $last );
	}

	public static function shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'autoplay' => 'yes',
				'interval' => '4500',
				'campus'   => '',
			),
			$atts,
			'testimonial-carousel'
		);

		$args = array(
			'post_type'      => self::POST_TYPE,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'order'          => 'ASC',
			'no_found_rows'  => true,
		);
		if ( '' !== trim( $atts['campus'] ) ) {
			$args['meta_query'] = array(
				array(
					'key'     => '_bmtc_campus',
					'value'   => sanitize_text_field( $atts['campus'] ),
					'compare' => '=',
				),
			);
		}

		$query = new WP_Query( $args );
		if ( ! $query->have_posts() ) {
			return '';
		}

		wp_enqueue_style( 'bmtc-carousel' );
		wp_enqueue_script( 'bmtc-carousel' );

		$instance_id = wp_unique_id( 'bmtc-' );
		$interval    = max( 2000, min( 20000, absint( $atts['interval'] ) ) );
		$autoplay    = in_array( strtolower( (string) $atts['autoplay'] ), array( 'yes', 'true', '1', 'on' ), true );

		ob_start();
		?>
		<div id="<?php echo esc_attr( $instance_id ); ?>" class="bmtc-carousel" aria-label="<?php esc_attr_e( 'Parent testimonials', 'testimonial-carousel' ); ?>" data-autoplay="<?php echo $autoplay ? 'true' : 'false'; ?>" data-interval="<?php echo esc_attr( (string) $interval ); ?>">
			<div class="bmtc-viewport" tabindex="0" aria-label="<?php esc_attr_e( 'Scrollable testimonials', 'testimonial-carousel' ); ?>">
				<div class="bmtc-track">
					<?php
					while ( $query->have_posts() ) {
						$query->the_post();
						$post_id      = get_the_ID();
						$author       = get_post_meta( $post_id, '_bmtc_author', true );
						$relationship = get_post_meta( $post_id, '_bmtc_relationship', true );
						$campus       = get_post_meta( $post_id, '_bmtc_campus', true );
						$initials     = get_post_meta( $post_id, '_bmtc_initials', true );
						$author       = $author ? $author : get_the_title();
						$initials     = $initials ? $initials : self::initials( $author );
						$content      = wpautop( wp_kses_post( get_post_field( 'post_content', $post_id ) ) );
						?>
						<article class="bmtc-card">
							<div class="bmtc-top"><span class="bmtc-quote" aria-hidden="true">“</span><span class="bmtc-stars" aria-label="<?php esc_attr_e( 'Five stars', 'testimonial-carousel' ); ?>">★★★★★</span></div>
							<div class="bmtc-text"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Sanitized with wp_kses_post above. ?></div>
							<button class="bmtc-more" type="button"><?php esc_html_e( 'View more', 'testimonial-carousel' ); ?></button>
							<div class="bmtc-author">
								<span class="bmtc-avatar" aria-hidden="true"><?php echo esc_html( $initials ); ?></span>
								<div class="bmtc-author-copy">
									<strong><?php echo esc_html( $author ); ?></strong>
									<?php if ( $relationship ) : ?><span class="bmtc-relation"><?php echo esc_html( $relationship ); ?></span><?php endif; ?>
									<?php if ( $campus ) : ?><span><?php echo esc_html( $campus ); ?></span><?php endif; ?>
								</div>
							</div>
						</article>
						<?php
					}
					wp_reset_postdata();
					?>
				</div>
			</div>
			<div class="bmtc-controls">
				<button class="bmtc-arrow bmtc-prev" type="button" aria-label="<?php esc_attr_e( 'Previous testimonials', 'testimonial-carousel' ); ?>">‹</button>
				<p class="bmtc-hint"><?php esc_html_e( 'Swipe or use arrows', 'testimonial-carousel' ); ?></p>
				<button class="bmtc-arrow bmtc-next" type="button" aria-label="<?php esc_attr_e( 'Next testimonials', 'testimonial-carousel' ); ?>">›</button>
			</div>
			<div class="bmtc-modal" hidden role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Full testimonial', 'testimonial-carousel' ); ?>">
				<div class="bmtc-modal-panel" tabindex="-1">
					<button class="bmtc-modal-close" type="button" aria-label="<?php esc_attr_e( 'Close testimonial', 'testimonial-carousel' ); ?>">×</button>
					<div class="bmtc-modal-quote"></div>
					<div class="bmtc-modal-author"><div class="bmtc-modal-author-copy"></div></div>
				</div>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	public static function activate() {
		self::register_post_type();
		self::seed_testimonials();
		flush_rewrite_rules();
	}

	private static function seed_testimonials() {
		if ( get_option( 'bmtc_seeded_version' ) ) {
			return;
		}
		$existing = get_posts(
			array(
				'post_type'      => self::POST_TYPE,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( ! empty( $existing ) ) {
			update_option( 'bmtc_seeded_version', BMTC_VERSION );
			return;
		}
		$file = BMTC_DIR . 'data/seed-testimonials.json';
		if ( ! is_readable( $file ) ) {
			return;
		}
		$data = json_decode( (string) file_get_contents( $file ), true );
		if ( ! is_array( $data ) ) {
			return;
		}
		foreach ( $data as $index => $item ) {
			$post_id = wp_insert_post(
				array(
					'post_type'    => self::POST_TYPE,
					'post_status'  => 'publish',
					'post_title'   => sanitize_text_field( $item['author'] ?? sprintf( __( 'Testimonial %d', 'testimonial-carousel' ), $index + 1 ) ),
					'post_content' => wp_slash( wp_kses_post( $item['content'] ?? '' ) ),
					'menu_order'   => $index + 1,
				),
				true
			);
			if ( is_wp_error( $post_id ) ) {
				continue;
			}
			foreach ( array( 'author', 'relationship', 'campus', 'initials' ) as $field ) {
				if ( ! empty( $item[ $field ] ) ) {
					update_post_meta( $post_id, '_bmtc_' . $field, sanitize_text_field( $item[ $field ] ) );
				}
			}
			update_post_meta(
				$post_id,
				'_bmtc_migration_id',
				self::migration_id( (string) ( $item['author'] ?? '' ), (string) ( $item['content'] ?? '' ) )
			);
		}
		update_option( 'bmtc_seeded_version', BMTC_VERSION );
	}
}

Testimonial_Carousel::init();
register_activation_hook( __FILE__, array( 'Testimonial_Carousel', 'activate' ) );



