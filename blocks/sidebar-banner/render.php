<?php
/**
 * Sidebar Banner Block - Server-side render
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block content.
 * @var WP_Block $block      Block instance.
 *
 * @package Workspaces_Directory_Theme
 */

$heading      = $attributes['heading'] ?? 'Welcome to Hub 21, {first_name}!';
$gif_url      = $attributes['gifUrl'] ?: content_url('/uploads/2025/08/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-2.gif');
$tint_color   = $attributes['tintColor'] ?? '#5CE1E6';
$tint_opacity = isset($attributes['tintOpacity']) ? $attributes['tintOpacity'] / 100 : 0.35;
$height       = $attributes['bannerHeight'] ?? 100;

// Replace {first_name} placeholder with actual user name
$current_user = wp_get_current_user();
$first_name   = '';

if ($current_user->ID > 0) {
    // Try FRS profile data first
    if (class_exists('FRSUsers\Models\Profile')) {
        $profile = \FRSUsers\Models\Profile::get_by_user_id($current_user->ID);
        if ($profile && !empty($profile->first_name)) {
            $first_name = $profile->first_name;
        }
    }
    // Fallback to WordPress
    if (empty($first_name)) {
        $first_name = $current_user->user_firstname ?: $current_user->display_name;
    }
}

// Replace placeholder
$rendered_heading = str_replace('{first_name}', esc_html($first_name), $heading);

// If user is logged out, clean up trailing comma + space before the !
if (empty($first_name)) {
    $rendered_heading = str_replace(', !', '!', $rendered_heading);
}

$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; ?>>
    <div class="sidebar-header-banner" style="height: <?php echo absint($height); ?>px;">
        <img
            src="<?php echo esc_url($gif_url); ?>"
            alt=""
            class="sidebar-header-banner-bg"
        />
        <div class="sidebar-header-banner-tint" style="background: <?php echo esc_attr($tint_color); ?>;"></div>
        <div class="sidebar-header-banner-glass"></div>
        <div class="sidebar-header-banner-content">
            <h2 class="sidebar-header-banner-heading"><?php echo wp_kses_post($rendered_heading); ?></h2>
        </div>
    </div>
</div>
