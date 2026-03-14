<?php
/**
 * Customizer Color Settings
 *
 * Adds color controls for sidebar and header bar styling.
 *
 * @package Workspaces_Directory_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get default color values
 */
function hub21_get_default_colors() {
    return array(
        'sidebar_bg'           => '#020014',
        'sidebar_text'         => 'rgba(255, 255, 255, 0.7)',
        'sidebar_text_hover'   => '#ffffff',
        'sidebar_accent'       => '#2563eb',
        'header_bar_bg'        => '#dce2eb',
        'header_bar_text'      => '#0b102c',
        'border_color'         => '#a8b4c8',
        'user_popup_bg'        => '#1a1f3c',
        'profile_gradient_start' => '#2563eb',
        'profile_gradient_end'   => '#2dd4da',
        // Brand cyan — accessible value for light-mode text/interactive use
        // #0A7E84 ≈ 4.6:1 on white (passes WCAG AA)
        'brand_cyan_light'       => '#0A7E84',
        // Brand cyan — vivid neon for dark-mode text/interactive use
        // #37DAE6 on #111518 dark ≈ 9:1 (passes WCAG AAA)
        'brand_cyan_dark'        => '#37DAE6',
        // Secondary/hover variants
        'brand_cyan_light_hover' => '#0A7E84',
        'brand_cyan_dark_hover'  => '#2DD4DA',
    );
}

/**
 * Register Customizer settings for colors
 */
add_action('customize_register', function($wp_customize) {
    $defaults = hub21_get_default_colors();

    // Add Sidebar Colors Section
    $wp_customize->add_section('hub21_colors', array(
        'title'       => __('Sidebar Colors', 'hub21-base'),
        'description' => __('Customize the colors of the sidebar and header bar.', 'hub21-base'),
        'priority'    => 36,
    ));

    // Sidebar Background Color
    $wp_customize->add_setting('sidebar_bg_color', array(
        'default'           => $defaults['sidebar_bg'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sidebar_bg_color', array(
        'label'   => __('Sidebar Background', 'hub21-base'),
        'section' => 'hub21_colors',
    )));

    // Sidebar Text Color
    $wp_customize->add_setting('sidebar_text_color', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sidebar_text_color', array(
        'label'       => __('Sidebar Text', 'hub21-base'),
        'description' => __('Text and icon color in sidebar', 'hub21-base'),
        'section'     => 'hub21_colors',
    )));

    // Sidebar Accent Color
    $wp_customize->add_setting('sidebar_accent_color', array(
        'default'           => $defaults['sidebar_accent'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sidebar_accent_color', array(
        'label'       => __('Sidebar Accent', 'hub21-base'),
        'description' => __('Active links and highlights', 'hub21-base'),
        'section'     => 'hub21_colors',
    )));

    // Header Bar Background Color
    $wp_customize->add_setting('header_bar_bg_color', array(
        'default'           => $defaults['header_bar_bg'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bar_bg_color', array(
        'label'   => __('Header Bar Background', 'hub21-base'),
        'section' => 'hub21_colors',
    )));

    // Header Bar Text Color
    $wp_customize->add_setting('header_bar_text_color', array(
        'default'           => $defaults['header_bar_text'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bar_text_color', array(
        'label'   => __('Header Bar Text', 'hub21-base'),
        'section' => 'hub21_colors',
    )));

    // Border Color
    $wp_customize->add_setting('sidebar_border_color', array(
        'default'           => $defaults['border_color'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'sidebar_border_color', array(
        'label'   => __('Border Color', 'hub21-base'),
        'section' => 'hub21_colors',
    )));

    // User Popup Background
    $wp_customize->add_setting('user_popup_bg_color', array(
        'default'           => $defaults['user_popup_bg'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'user_popup_bg_color', array(
        'label'   => __('User Menu Background', 'hub21-base'),
        'section' => 'hub21_colors',
    )));

    // Profile Widget Gradient Start
    $wp_customize->add_setting('profile_gradient_start', array(
        'default'           => $defaults['profile_gradient_start'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'profile_gradient_start', array(
        'label'   => __('Profile Widget Gradient Start', 'hub21-base'),
        'section' => 'hub21_colors',
    )));

    // Profile Widget Gradient End
    $wp_customize->add_setting('profile_gradient_end', array(
        'default'           => $defaults['profile_gradient_end'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'profile_gradient_end', array(
        'label'   => __('Profile Widget Gradient End', 'hub21-base'),
        'section' => 'hub21_colors',
    )));

    // ── Brand Cyan Section ───────────────────────────────────────────────
    // These control the accessible substitutions for the brand cyan (#37DAE6 /
    // #2DD4DA) which fail WCAG AA contrast on light backgrounds. Two values per
    // color: one for light mode (must pass 4.5:1 on white), one for dark mode
    // (vivid neon is fine on dark backgrounds).

    $wp_customize->add_section('hub21_brand_colors', array(
        'title'       => __('Brand Cyan & Accessibility', 'hub21-base'),
        'description' => __('The brand cyans (#37DAE6, #2DD4DA) fail WCAG contrast on white backgrounds (~2:1). Set an accessible dark-teal for light mode here. The vivid neons are automatically restored in Blocksy dark mode.', 'hub21-base'),
        'priority'    => 37,
    ));

    // Brand Cyan — Light Mode (accessible)
    $wp_customize->add_setting('brand_cyan_light', array(
        'default'           => $defaults['brand_cyan_light'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'brand_cyan_light', array(
        'label'       => __('Brand Cyan — Light Mode', 'hub21-base'),
        'description' => __('Used for links, buttons, form focus rings on white/light backgrounds. Must be dark enough for 4.5:1 contrast on white. Default: #0A7E84.', 'hub21-base'),
        'section'     => 'hub21_brand_colors',
    )));

    // Brand Cyan Hover — Light Mode (accessible)
    $wp_customize->add_setting('brand_cyan_light_hover', array(
        'default'           => $defaults['brand_cyan_light_hover'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'brand_cyan_light_hover', array(
        'label'       => __('Brand Cyan Hover — Light Mode', 'hub21-base'),
        'description' => __('Hover/secondary shade on light backgrounds. Also used for .has-rich-teal-color hover state.', 'hub21-base'),
        'section'     => 'hub21_brand_colors',
    )));

    // Brand Cyan — Dark Mode (vivid neon)
    $wp_customize->add_setting('brand_cyan_dark', array(
        'default'           => $defaults['brand_cyan_dark'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'brand_cyan_dark', array(
        'label'       => __('Brand Cyan — Dark Mode', 'hub21-base'),
        'description' => __('Primary cyan in Blocksy dark mode. Vivid neon is fine on dark backgrounds. Default: #37DAE6.', 'hub21-base'),
        'section'     => 'hub21_brand_colors',
    )));

    // Brand Cyan Hover — Dark Mode (vivid neon secondary)
    $wp_customize->add_setting('brand_cyan_dark_hover', array(
        'default'           => $defaults['brand_cyan_dark_hover'],
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'postMessage',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'brand_cyan_dark_hover', array(
        'label'       => __('Brand Cyan Hover — Dark Mode', 'hub21-base'),
        'description' => __('Hover/secondary cyan in dark mode. Default: #2DD4DA.', 'hub21-base'),
        'section'     => 'hub21_brand_colors',
    )));
});

/**
 * Output custom CSS variables based on Customizer settings
 */
add_action('wp_head', function() {
    $defaults = hub21_get_default_colors();

    $sidebar_bg        = get_theme_mod('sidebar_bg_color', $defaults['sidebar_bg']);
    $sidebar_text      = get_theme_mod('sidebar_text_color', '#ffffff');
    $sidebar_accent    = get_theme_mod('sidebar_accent_color', $defaults['sidebar_accent']);
    $header_bar_bg     = get_theme_mod('header_bar_bg_color', $defaults['header_bar_bg']);
    $header_bar_text   = get_theme_mod('header_bar_text_color', $defaults['header_bar_text']);
    $border_color      = get_theme_mod('sidebar_border_color', $defaults['border_color']);
    $user_popup_bg     = get_theme_mod('user_popup_bg_color', $defaults['user_popup_bg']);
    $gradient_start    = get_theme_mod('profile_gradient_start', $defaults['profile_gradient_start']);
    $gradient_end      = get_theme_mod('profile_gradient_end', $defaults['profile_gradient_end']);

    // Brand cyan — accessible light-mode and vivid dark-mode values
    $cyan_light       = get_theme_mod('brand_cyan_light', $defaults['brand_cyan_light']);
    $cyan_light_hover = get_theme_mod('brand_cyan_light_hover', $defaults['brand_cyan_light_hover']);
    $cyan_dark        = get_theme_mod('brand_cyan_dark', $defaults['brand_cyan_dark']);
    $cyan_dark_hover  = get_theme_mod('brand_cyan_dark_hover', $defaults['brand_cyan_dark_hover']);

    // Calculate text opacity variant (70% opacity for secondary text)
    $sidebar_text_muted = hub21_hex_to_rgba($sidebar_text, 0.7);
    ?>
    <style id="workspaces-directory-custom-colors">
    :root {
        --wd-sidebar-bg: <?php echo esc_attr($sidebar_bg); ?>;
        --wd-sidebar-text: <?php echo esc_attr($sidebar_text); ?>;
        --wd-sidebar-text-muted: <?php echo esc_attr($sidebar_text_muted); ?>;
        --wd-sidebar-accent: <?php echo esc_attr($sidebar_accent); ?>;
        --wd-header-bar-bg: <?php echo esc_attr($header_bar_bg); ?>;
        --wd-header-bar-text: <?php echo esc_attr($header_bar_text); ?>;
        --wd-border-color: <?php echo esc_attr($border_color); ?>;
        --wd-user-popup-bg: <?php echo esc_attr($user_popup_bg); ?>;
        --wd-profile-gradient: linear-gradient(135deg, <?php echo esc_attr($gradient_start); ?> 0%, <?php echo esc_attr($gradient_end); ?> 100%);

        /* Brand cyan — light mode (WCAG-accessible on white/light backgrounds) */
        --wd-brand-cyan: <?php echo esc_attr($cyan_light); ?>;
        --wd-brand-cyan-hover: <?php echo esc_attr($cyan_light_hover); ?>;

        /*
         * Override Blocksy palette-color-1 and -2 so all downstream consumers
         * (links, buttons, form focus rings, .has-palette-color-1-color text)
         * automatically use the accessible value in light mode.
         */
        --theme-palette-color-1: <?php echo esc_attr($cyan_light); ?>;
        --theme-palette-color-2: <?php echo esc_attr($cyan_light_hover); ?>;

        /* Override the --21c brand variable used in widget/card styles */
        --21c-rich-teal: <?php echo esc_attr($cyan_light); ?>;
    }

    /* Blocksy dark mode — restore vivid neon brand cyans */
    [data-color-mode*="dark"] {
        --wd-brand-cyan: <?php echo esc_attr($cyan_dark); ?>;
        --wd-brand-cyan-hover: <?php echo esc_attr($cyan_dark_hover); ?>;
        --theme-palette-color-1: <?php echo esc_attr($cyan_dark); ?>;
        --theme-palette-color-2: <?php echo esc_attr($cyan_dark_hover); ?>;
        --21c-rich-teal: <?php echo esc_attr($cyan_dark); ?>;
    }

    /* OS dark preference when Blocksy is set to "System" */
    @media (prefers-color-scheme: dark) {
        [data-color-mode="os-default"] {
            --wd-brand-cyan: <?php echo esc_attr($cyan_dark); ?>;
            --wd-brand-cyan-hover: <?php echo esc_attr($cyan_dark_hover); ?>;
            --theme-palette-color-1: <?php echo esc_attr($cyan_dark); ?>;
            --theme-palette-color-2: <?php echo esc_attr($cyan_dark_hover); ?>;
            --21c-rich-teal: <?php echo esc_attr($cyan_dark); ?>;
        }
    }

    /* Gutenberg .has-rich-teal-color block text class */
    .has-rich-teal-color {
        color: var(--wd-brand-cyan) !important;
    }

    /* Background version — keep vivid neon regardless of mode */
    .has-rich-teal-background-color {
        background-color: #20D4DA;
    }
    </style>
    <?php
}, 20);

/**
 * Convert hex color to rgba
 *
 * @param string $hex Hex color code
 * @param float $alpha Alpha value (0-1)
 * @return string RGBA color string
 */
function hub21_hex_to_rgba($hex, $alpha = 1) {
    $hex = ltrim($hex, '#');

    if (strlen($hex) === 3) {
        $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
    }

    $r = hexdec(substr($hex, 0, 2));
    $g = hexdec(substr($hex, 2, 2));
    $b = hexdec(substr($hex, 4, 2));

    return "rgba({$r}, {$g}, {$b}, {$alpha})";
}

/**
 * Enqueue Customizer preview script for live updates
 */
add_action('customize_preview_init', function() {
    wp_enqueue_script(
        'workspaces-directory-customizer-preview',
        get_stylesheet_directory_uri() . '/assets/js/customizer-preview.js',
        array('customize-preview', 'jquery'),
        HUB21_THEME_VERSION,
        true
    );
});
