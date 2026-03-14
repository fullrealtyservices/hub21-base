<?php
/**
 * Customizer Layout & Component Settings
 *
 * Adds dimension controls and visibility toggles for sidebar components.
 *
 * @package Workspaces_Directory_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get default layout values
 */
function hub21_get_default_layout() {
    return array(
        'sidebar_width'             => 320,
        'header_bar_height'         => 60,
        'widget_area_mode'          => 'none',
        'widget_area_custom_height' => 180,
        'profile_card_height'       => 80,
        'toggle_button_width'       => 48,
        'show_header_widget'        => true,
        'show_nav_menu'             => true,
        'show_profile_completion'   => true,
        'show_user_profile_card'    => true,
        'show_datetime_display'     => true,
        // Inset admin look
        'inset_mode'                => false,
        'site_bg_color'             => '#d0d8e8',
        'content_border_radius'     => 12,
        'inset_padding'             => 5,
    );
}

/**
 * Sanitize widget area mode select
 */
function hub21_sanitize_widget_area_mode($value) {
    $valid = array('16:9', '4:3', '1:1', 'custom-height', 'none');
    return in_array($value, $valid, true) ? $value : '16:9';
}

/**
 * Sanitize number within range
 */
function hub21_sanitize_number_range($value, $setting) {
    $attrs = $setting->manager->get_control($setting->id)->input_attrs;
    $min = isset($attrs['min']) ? $attrs['min'] : 0;
    $max = isset($attrs['max']) ? $attrs['max'] : 9999;
    $value = absint($value);
    return max($min, min($max, $value));
}

/**
 * Register Customizer settings for layout and components
 */
add_action('customize_register', function($wp_customize) {
    $defaults = hub21_get_default_layout();

    // ─── Section: Sidebar Layout ─────────────────────────────────────────
    $wp_customize->add_section('hub21_layout', array(
        'title'       => __('Sidebar Layout', 'hub21-base'),
        'description' => __('Control sidebar dimensions and widget area sizing.', 'hub21-base'),
        'priority'    => 37,
    ));

    // Sidebar Width
    $wp_customize->add_setting('wd_sidebar_width', array(
        'default'           => $defaults['sidebar_width'],
        'sanitize_callback' => 'hub21_sanitize_number_range',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_sidebar_width', array(
        'label'       => __('Sidebar Width (px)', 'hub21-base'),
        'section'     => 'hub21_layout',
        'type'        => 'number',
        'input_attrs' => array('min' => 200, 'max' => 500, 'step' => 10),
    ));

    // Header Bar Height
    $wp_customize->add_setting('wd_header_bar_height', array(
        'default'           => $defaults['header_bar_height'],
        'sanitize_callback' => 'hub21_sanitize_number_range',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_header_bar_height', array(
        'label'       => __('Header Bar Height (px)', 'hub21-base'),
        'section'     => 'hub21_layout',
        'type'        => 'number',
        'input_attrs' => array('min' => 40, 'max' => 100, 'step' => 5),
    ));

    // Widget Area Mode
    $wp_customize->add_setting('wd_widget_area_mode', array(
        'default'           => $defaults['widget_area_mode'],
        'sanitize_callback' => 'hub21_sanitize_widget_area_mode',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_widget_area_mode', array(
        'label'   => __('Widget Area Size', 'hub21-base'),
        'section' => 'hub21_layout',
        'type'    => 'select',
        'choices' => array(
            '16:9'          => __('16:9 Aspect Ratio', 'hub21-base'),
            '4:3'           => __('4:3 Aspect Ratio', 'hub21-base'),
            '1:1'           => __('1:1 Square', 'hub21-base'),
            'custom-height' => __('Custom Height', 'hub21-base'),
            'none'          => __('Auto (no fixed size)', 'hub21-base'),
        ),
    ));

    // Widget Area Custom Height
    $wp_customize->add_setting('wd_widget_area_custom_height', array(
        'default'           => $defaults['widget_area_custom_height'],
        'sanitize_callback' => 'hub21_sanitize_number_range',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_widget_area_custom_height', array(
        'label'           => __('Widget Area Height (px)', 'hub21-base'),
        'section'         => 'hub21_layout',
        'type'            => 'number',
        'input_attrs'     => array('min' => 50, 'max' => 500, 'step' => 10),
        'active_callback' => function() {
            return get_theme_mod('wd_widget_area_mode', '16:9') === 'custom-height';
        },
    ));

    // Profile Card Height
    $wp_customize->add_setting('wd_profile_card_height', array(
        'default'           => $defaults['profile_card_height'],
        'sanitize_callback' => 'hub21_sanitize_number_range',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_profile_card_height', array(
        'label'       => __('Profile Card Height (px)', 'hub21-base'),
        'section'     => 'hub21_layout',
        'type'        => 'number',
        'input_attrs' => array('min' => 60, 'max' => 120, 'step' => 5),
    ));

    // Toggle Button Width
    $wp_customize->add_setting('wd_toggle_button_width', array(
        'default'           => $defaults['toggle_button_width'],
        'sanitize_callback' => 'hub21_sanitize_number_range',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_toggle_button_width', array(
        'label'       => __('Toggle Button Width (px)', 'hub21-base'),
        'section'     => 'hub21_layout',
        'type'        => 'number',
        'input_attrs' => array('min' => 32, 'max' => 80, 'step' => 4),
    ));

    // ─── Section: Inset Admin Look ───────────────────────────────────────
    $wp_customize->add_section('hub21_inset', array(
        'title'       => __('Inset Admin Look', 'hub21-base'),
        'description' => __('Float the app inside a rounded, shadowed container with a visible page background — like GitLab, Linear, or Notion.', 'hub21-base'),
        'priority'    => 37,
    ));

    // Enable Inset Mode
    $wp_customize->add_setting('wd_inset_mode', array(
        'default'           => $defaults['inset_mode'],
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_inset_mode', array(
        'label'       => __('Enable Inset Mode', 'hub21-base'),
        'description' => __('Adds padding around the entire app so the site background shows through.', 'hub21-base'),
        'section'     => 'hub21_inset',
        'type'        => 'checkbox',
    ));

    // Site Background Color
    $wp_customize->add_setting('wd_site_bg_color', array(
        'default'           => $defaults['site_bg_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'wd_site_bg_color', array(
        'label'       => __('Site Background Color', 'hub21-base'),
        'description' => __('The page background visible around the inset app container.', 'hub21-base'),
        'section'     => 'hub21_inset',
    )));

    // Content Area Border Radius
    $wp_customize->add_setting('wd_content_border_radius', array(
        'default'           => $defaults['content_border_radius'],
        'sanitize_callback' => 'hub21_sanitize_number_range',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_content_border_radius', array(
        'label'       => __('Content Area Border Radius (px)', 'hub21-base'),
        'description' => __('Rounded corners on the sidebar and content area.', 'hub21-base'),
        'section'     => 'hub21_inset',
        'type'        => 'number',
        'input_attrs' => array('min' => 0, 'max' => 32, 'step' => 1),
    ));

    // Inset Padding
    $wp_customize->add_setting('wd_inset_padding', array(
        'default'           => $defaults['inset_padding'],
        'sanitize_callback' => 'hub21_sanitize_number_range',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_inset_padding', array(
        'label'       => __('Inset Padding (px)', 'hub21-base'),
        'description' => __('Gap between the browser edge and the app container.', 'hub21-base'),
        'section'     => 'hub21_inset',
        'type'        => 'number',
        'input_attrs' => array('min' => 0, 'max' => 48, 'step' => 2),
    ));

    // ─── Section: Sidebar Components ─────────────────────────────────────
    $wp_customize->add_section('hub21_components', array(
        'title'       => __('Sidebar Components', 'hub21-base'),
        'description' => __('Toggle visibility of sidebar elements.', 'hub21-base'),
        'priority'    => 38,
    ));

    // Show Header Widget Area
    $wp_customize->add_setting('wd_show_header_widget', array(
        'default'           => $defaults['show_header_widget'],
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_show_header_widget', array(
        'label'   => __('Show Header Widget Area', 'hub21-base'),
        'section' => 'hub21_components',
        'type'    => 'checkbox',
    ));

    // Show Navigation Menu
    $wp_customize->add_setting('wd_show_nav_menu', array(
        'default'           => $defaults['show_nav_menu'],
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_show_nav_menu', array(
        'label'   => __('Show Navigation Menu', 'hub21-base'),
        'section' => 'hub21_components',
        'type'    => 'checkbox',
    ));

    // Show Profile Completion
    $wp_customize->add_setting('wd_show_profile_completion', array(
        'default'           => $defaults['show_profile_completion'],
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_show_profile_completion', array(
        'label'   => __('Show Profile Completion Widget', 'hub21-base'),
        'section' => 'hub21_components',
        'type'    => 'checkbox',
    ));

    // Show User Profile Card
    $wp_customize->add_setting('wd_show_user_profile_card', array(
        'default'           => $defaults['show_user_profile_card'],
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_show_user_profile_card', array(
        'label'   => __('Show User Profile Card', 'hub21-base'),
        'section' => 'hub21_components',
        'type'    => 'checkbox',
    ));

    // Show DateTime Display
    $wp_customize->add_setting('wd_show_datetime_display', array(
        'default'           => $defaults['show_datetime_display'],
        'sanitize_callback' => 'wp_validate_boolean',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control('wd_show_datetime_display', array(
        'label'   => __('Show Date/Time in Header Bar', 'hub21-base'),
        'section' => 'hub21_components',
        'type'    => 'checkbox',
    ));
});

/**
 * Output layout CSS variables and visibility rules
 */
add_action('wp_head', function() {
    $defaults = hub21_get_default_layout();

    $sidebar_width        = absint(get_theme_mod('wd_sidebar_width', $defaults['sidebar_width']));
    $header_bar_height    = absint(get_theme_mod('wd_header_bar_height', $defaults['header_bar_height']));
    $toggle_button_width  = absint(get_theme_mod('wd_toggle_button_width', $defaults['toggle_button_width']));
    $profile_card_height  = absint(get_theme_mod('wd_profile_card_height', $defaults['profile_card_height']));
    $widget_area_mode     = get_theme_mod('wd_widget_area_mode', $defaults['widget_area_mode']);
    $widget_custom_height = absint(get_theme_mod('wd_widget_area_custom_height', $defaults['widget_area_custom_height']));

    // Inset look
    $inset_mode           = (bool) get_theme_mod('wd_inset_mode', $defaults['inset_mode']);
    $site_bg_color        = get_theme_mod('wd_site_bg_color', $defaults['site_bg_color']);
    $content_radius       = absint(get_theme_mod('wd_content_border_radius', $defaults['content_border_radius']));
    $inset_padding        = absint(get_theme_mod('wd_inset_padding', $defaults['inset_padding']));

    // Compute widget area CSS based on mode
    switch ($widget_area_mode) {
        case '16:9':
            $widget_css = 'aspect-ratio: 16/9;';
            break;
        case '4:3':
            $widget_css = 'aspect-ratio: 4/3;';
            break;
        case '1:1':
            $widget_css = 'aspect-ratio: 1/1;';
            break;
        case 'custom-height':
            $widget_css = 'height: ' . $widget_custom_height . 'px; aspect-ratio: auto;';
            break;
        case 'none':
        default:
            $widget_css = '';
            break;
    }

    // Visibility toggles
    $show_header_widget      = get_theme_mod('wd_show_header_widget', $defaults['show_header_widget']);
    $show_nav_menu           = get_theme_mod('wd_show_nav_menu', $defaults['show_nav_menu']);
    $show_profile_completion = get_theme_mod('wd_show_profile_completion', $defaults['show_profile_completion']);
    $show_user_profile_card  = get_theme_mod('wd_show_user_profile_card', $defaults['show_user_profile_card']);
    $show_datetime_display   = get_theme_mod('wd_show_datetime_display', $defaults['show_datetime_display']);
    ?>
    <style id="workspaces-directory-custom-layout">
    :root {
        --wd-sidebar-width: <?php echo $sidebar_width; ?>px;
        --wd-bar-height: <?php echo $header_bar_height; ?>px;
        --wd-toggle-width: <?php echo $toggle_button_width; ?>px;
        --wd-profile-card-height: <?php echo $profile_card_height; ?>px;
        --wd-site-bg: <?php echo esc_attr($site_bg_color); ?>;
        --wd-content-radius: <?php echo $content_radius; ?>px;
        --wd-inset-padding: <?php echo $inset_padding; ?>px;
    }
    <?php if ($widget_css) : ?>
    .workspace-sidebar-header-widget { <?php echo $widget_css; ?> }
    <?php endif; ?>
    <?php if (!$show_header_widget) : ?>
    .workspace-sidebar-header-widget { display: none !important; }
    <?php endif; ?>
    <?php if (!$show_nav_menu) : ?>
    #workspace-nav { display: none !important; }
    <?php endif; ?>
    <?php if (!$show_profile_completion) : ?>
    .workspace-profile-completion { display: none !important; }
    <?php endif; ?>
    <?php if (!$show_user_profile_card) : ?>
    .user-menu-wrapper { display: none !important; }
    <?php endif; ?>
    <?php if (!$show_datetime_display) : ?>
    .workspace-header-datetime { display: none !important; }
    <?php endif; ?>

    </style>

    <?php if ($inset_mode) : ?>
    <script>document.body.classList.add('wd-inset');</script>
    <?php endif; ?>
    <?php
}, 21);

/**
 * Dynamic show/hide for custom height control in Customizer panel
 */
add_action('customize_controls_enqueue_scripts', function() {
    wp_add_inline_script('customize-controls', "
        wp.customize('wd_widget_area_mode', function(setting) {
            wp.customize.control('wd_widget_area_custom_height', function(control) {
                function toggleVisibility() {
                    control.active.set(setting.get() === 'custom-height');
                }
                toggleVisibility();
                setting.bind(toggleVisibility);
            });
        });
    ");
});
