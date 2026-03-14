<?php
/**
 * BuddyPress Compatibility Layer
 * Theme-level layout and styling for BuddyPress within the sidebar frame.
 *
 * @package hub21-base
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! function_exists( 'buddypress' ) ) return;

/**
 * Check if a user is currently online (active in the last 5 minutes).
 *
 * @param int $user_id The user ID to check.
 * @return bool True if user was active in last 5 minutes.
 */
function hub21_is_user_online( $user_id ) {
	if ( ! function_exists( 'bp_get_user_last_activity' ) ) {
		return false;
	}

	$last_activity = bp_get_user_last_activity( $user_id );

	if ( empty( $last_activity ) ) {
		return false;
	}

	$last_active_timestamp = strtotime( $last_activity );
	$five_minutes_ago      = time() - ( 5 * MINUTE_IN_SECONDS );

	return ( $last_active_timestamp >= $five_minutes_ago );
}

/**
 * Render an online status indicator dot for a user.
 *
 * @param int $user_id The user ID.
 * @return string HTML for the status indicator.
 */
function hub21_user_status( $user_id ) {
	if ( ! hub21_is_user_online( $user_id ) ) {
		return '';
	}

	return '<span class="hub21-online-indicator" aria-label="' . esc_attr__( 'Online', 'hub21-base' ) . '"></span>';
}

/**
 * Render a cover image for a member directory card.
 *
 * @return string HTML for the cover image area.
 */
function hub21_render_member_cover_image() {
	$cover_url = '';

	if ( function_exists( 'bp_attachments_get_attachment' ) ) {
		$cover_url = bp_attachments_get_attachment( 'url', array(
			'object_dir' => 'members',
			'item_id'    => bp_get_member_user_id(),
		) );
	}

	if ( ! empty( $cover_url ) ) {
		return '<div class="hub21-cover-image" style="background-image: url(' . esc_url( $cover_url ) . ');"></div>';
	}

	return '<div class="hub21-cover-image hub21-cover-image--fallback"></div>';
}

/**
 * Render a cover image for a group directory card.
 *
 * @return string HTML for the cover image area.
 */
function hub21_render_group_cover_image() {
	$cover_url = '';

	if ( function_exists( 'bp_attachments_get_attachment' ) ) {
		$cover_url = bp_attachments_get_attachment( 'url', array(
			'object_dir' => 'groups',
			'item_id'    => bp_get_group_id(),
		) );
	}

	if ( ! empty( $cover_url ) ) {
		return '<div class="hub21-cover-image" style="background-image: url(' . esc_url( $cover_url ) . ');"></div>';
	}

	return '<div class="hub21-cover-image hub21-cover-image--fallback"></div>';
}

/**
 * Map a BuddyPress activity component to a CSS modifier class.
 *
 * @return string CSS class name.
 */
function hub21_bp_get_activity_css_first_class() {
	$component = bp_get_activity_object_name();

	$map = array(
		'activity' => 'primary',
		'groups'   => 'success',
		'friends'  => 'info',
		'profile'  => 'warning',
	);

	$class = isset( $map[ $component ] ) ? $map[ $component ] : 'primary';

	return 'hub21-activity--' . $class;
}

/**
 * Register BuddyPress sidebar widget area.
 */
function hub21_register_bp_sidebar() {
	register_sidebar( array(
		'name'          => __( 'BuddyPress Sidebar', 'hub21-base' ),
		'id'            => 'buddypress-sidebar',
		'description'   => __( 'Widgets displayed on BuddyPress pages.', 'hub21-base' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'hub21_register_bp_sidebar' );

/**
 * Enqueue BuddyPress-specific stylesheet only on BP pages.
 */
function hub21_enqueue_bp_styles() {
	if ( ! function_exists( 'is_buddypress' ) || ! is_buddypress() ) {
		return;
	}

	wp_enqueue_style(
		'hub21-buddypress',
		get_stylesheet_directory_uri() . '/assets/css/buddypress.css',
		array( 'parent-style' ),
		filemtime( get_stylesheet_directory() . '/assets/css/buddypress.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'hub21_enqueue_bp_styles' );

/**
 * Add BuddyPress-specific body classes.
 *
 * @param array $classes Existing body classes.
 * @return array Modified body classes.
 */
function hub21_bp_body_classes( $classes ) {
	if ( ! function_exists( 'is_buddypress' ) || ! is_buddypress() ) {
		return $classes;
	}

	$classes[] = 'hub21-buddypress';

	if ( function_exists( 'bp_is_directory' ) && bp_is_directory() ) {
		$classes[] = 'hub21-bp-directory';
	}

	if ( function_exists( 'bp_is_single_item' ) && bp_is_single_item() ) {
		$classes[] = 'hub21-bp-single';
	}

	return $classes;
}
add_filter( 'body_class', 'hub21_bp_body_classes' );
