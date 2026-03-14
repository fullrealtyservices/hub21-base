<?php
/**
 * Hub21 Base Theme - Blocksy Child Theme
 *
 * Merges workspace portal sidebar system with GreenShift design framework.
 *
 * @package Hub21_Base_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Child theme constants
 */
define('HUB21_THEME_VERSION', '2.0.0');
define('HUB21_THEME_PATH', get_stylesheet_directory());
define('HUB21_THEME_URL', get_stylesheet_directory_uri());

/**
 * ============================================================================
 * 1. PARENT STYLE ENQUEUE
 * ============================================================================
 */
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
});

/**
 * ============================================================================
 * 2. GREENSHIFT FRAMEWORK LOADING
 * ============================================================================
 */
$gs_framework_dir = get_stylesheet_directory() . '/gs-framework';
$gs_framework_uri = get_stylesheet_directory_uri() . '/gs-framework';

if (function_exists('greenshift_render_variables')) {

    if (file_exists($gs_framework_dir . '/framework-groups.php')) {
        require_once $gs_framework_dir . '/framework-groups.php';
    }

    if (file_exists($gs_framework_dir . '/data-presets.php')) {
        require_once $gs_framework_dir . '/data-presets.php';
    }

    if (file_exists($gs_framework_dir . '/semantic-tokens.php')) {
        require_once $gs_framework_dir . '/semantic-tokens.php';
    }

    if (file_exists($gs_framework_dir . '/internal/color-token-swatches.php')) {
        require_once $gs_framework_dir . '/internal/color-token-swatches.php';
    }

    if (file_exists($gs_framework_dir . '/block-variations.php')) {
        require_once $gs_framework_dir . '/block-variations.php';
    }

    // Enqueue framework CSS - priority 999 ensures it loads after Blocksy's dynamic styles
    if (file_exists($gs_framework_dir . '/framework-groups.css')) {
        add_action('wp_enqueue_scripts', function () use ($gs_framework_uri, $gs_framework_dir) {
            wp_enqueue_style(
                'blocksy-framework-groups',
                $gs_framework_uri . '/framework-groups.css',
                array('parent-style'),
                filemtime($gs_framework_dir . '/framework-groups.css'),
                'all'
            );
        }, 999);
    }

    // Enqueue data preset dropdown enhancement in the block editor
    if (file_exists($gs_framework_dir . '/internal/data-preset-dropdown.js')) {
        add_action('enqueue_block_editor_assets', function () use ($gs_framework_uri, $gs_framework_dir) {
            wp_enqueue_script(
                'gs-data-preset-dropdown',
                $gs_framework_uri . '/internal/data-preset-dropdown.js',
                array('wp-data', 'wp-block-editor'),
                filemtime($gs_framework_dir . '/internal/data-preset-dropdown.js'),
                true
            );
        });
    }
}

/**
 * ============================================================================
 * 3. TAILWIND + CHILD STYLE + A11Y CSS ENQUEUE
 * ============================================================================
 */
add_action('wp_enqueue_scripts', function () {
    // Tailwind CSS (compiled)
    $tailwind_path = get_stylesheet_directory() . '/assets/css/tailwind.css';
    if (file_exists($tailwind_path)) {
        wp_enqueue_style(
            'hub21-base-tailwind',
            get_stylesheet_directory_uri() . '/assets/css/tailwind.css',
            [],
            filemtime($tailwind_path)
        );
    }

    wp_enqueue_style('hub21-base-style', get_stylesheet_uri(), ['hub21-base-tailwind']);

    // Accessibility color overrides (brand cyan WCAG fixes)
    $a11y_path = get_stylesheet_directory() . '/assets/css/accessibility-overrides.css';
    if (file_exists($a11y_path)) {
        wp_enqueue_style(
            'hub21-base-a11y',
            get_stylesheet_directory_uri() . '/assets/css/accessibility-overrides.css',
            ['hub21-base-style'],
            filemtime($a11y_path)
        );
    }

    // Enqueue Interactivity API for workspace navigation
    if (function_exists('hub21_is_workspace_page') && hub21_is_workspace_page()) {
        wp_enqueue_script_module('@wordpress/interactivity-router');
    }
});

/**
 * ============================================================================
 * 4. LOAD INCLUDES
 * ============================================================================
 */
require_once HUB21_THEME_PATH . '/includes/taxonomy-menu-system.php';
require_once HUB21_THEME_PATH . '/includes/customizer-colors.php';
require_once HUB21_THEME_PATH . '/includes/customizer-layout.php';
require_once HUB21_THEME_PATH . '/includes/sidebar-state-metabox.php';
require_once HUB21_THEME_PATH . '/includes/sidebar-insights-slider.php';

/**
 * ============================================================================
 * 5. CUSTOM BLOCK REGISTRATION
 * ============================================================================
 */
add_action('init', function () {
    register_block_type(HUB21_THEME_PATH . '/blocks/sidebar-banner');
});

/**
 * ============================================================================
 * 6. SHORTCODES
 * ============================================================================
 */

/**
 * Time-based greeting shortcode
 * Usage: [time_greeting] outputs "Good Morning, Derin!"
 * Uses JavaScript for accurate local time (matches the clock in header bar)
 */
add_shortcode('time_greeting', function() {
    $first_name = wp_get_current_user()->user_firstname ?: 'there';
    $first_name_escaped = esc_js($first_name);

    return '<h2 id="time-greeting" style="color: #ffffff; font-size: 20px; font-weight: 600; margin: 0;">Hello, ' . esc_html($first_name) . '!</h2>
    <script>
    (function() {
        var hour = new Date().getHours();
        var greeting;
        if (hour >= 5 && hour < 12) greeting = "Good Morning";
        else if (hour >= 12 && hour < 17) greeting = "Good Afternoon";
        else if (hour >= 17 && hour < 21) greeting = "Good Evening";
        else greeting = "Good Night";
        document.getElementById("time-greeting").textContent = greeting + ", ' . $first_name_escaped . '!";
    })();
    </script>';
});

/**
 * ============================================================================
 * 7. WIDGET SHORTCODE FILTERS
 * ============================================================================
 */
add_filter('widget_text', 'do_shortcode');
add_filter('widget_custom_html_content', 'do_shortcode');

/**
 * ============================================================================
 * 8. LUCIDE ICONS + MENU ITEM ICONS
 * ============================================================================
 */
if (!class_exists('Lucide_Icons')) {
    require_once HUB21_THEME_PATH . '/includes/class-lucide-icons.php';
}

require_once HUB21_THEME_PATH . '/includes/class-menu-item-icons.php';
Menu_Item_Icons::instance();

/**
 * ============================================================================
 * 9. LRH PORTAL ASSET LOADING FILTER
 * ============================================================================
 */
add_filter('lrh_should_load_portal', function($should_load) {
    if (hub21_is_workspace_page()) {
        return true;
    }
    return $should_load;
});

/**
 * ============================================================================
 * 10. SWIFT CONTROL FILTER
 * ============================================================================
 */
add_filter('swift_control_frontend_display', function ($display) {
    $request_uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($request_uri, '/my-bookings') !== false) {
        return false;
    }
    return $display;
});

/**
 * ============================================================================
 * 11. WIDGET AREA REGISTRATION
 * ============================================================================
 */
add_action('widgets_init', function () {
    register_sidebar(array(
        'name'          => __('Workspace Below Sidebar', 'hub21-base'),
        'id'            => 'workspace-below-sidebar',
        'description'   => __('Widget area below the workspace sidebar component.', 'hub21-base'),
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ));

    register_sidebar(array(
        'name'          => __('Workspace Sidebar Header', 'hub21-base'),
        'id'            => 'workspace-sidebar-header',
        'description'   => __('16:9 header area at top of workspace sidebar. Use Cover block for image/video backgrounds.', 'hub21-base'),
        'before_widget' => '<div id="%1$s" class="widget workspace-header-widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '',
        'after_title'   => '',
    ));
});

/**
 * ============================================================================
 * 12. CUSTOMIZER: HEADER BACKGROUND SETTINGS
 * ============================================================================
 */
add_action('customize_register', function ($wp_customize) {
    // Add Header Background Section
    $wp_customize->add_section('workspaces_header_background', array(
        'title'       => __('Workspace Header Background', 'hub21-base'),
        'description' => __('Customize the header background image or video.', 'hub21-base'),
        'priority'    => 30,
    ));

    // Background Type Setting
    $wp_customize->add_setting('workspaces_header_bg_type', array(
        'default'           => 'none',
        'sanitize_callback' => 'hub21_sanitize_bg_type',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('workspaces_header_bg_type', array(
        'label'    => __('Background Type', 'hub21-base'),
        'section'  => 'workspaces_header_background',
        'type'     => 'select',
        'choices'  => array(
            'none'  => __('None', 'hub21-base'),
            'image' => __('Image', 'hub21-base'),
            'video' => __('Video', 'hub21-base'),
        ),
    ));

    // Background Image Setting
    $wp_customize->add_setting('workspaces_header_bg_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'workspaces_header_bg_image', array(
        'label'           => __('Header Background Image', 'hub21-base'),
        'section'         => 'workspaces_header_background',
        'active_callback' => function () {
            return get_theme_mod('workspaces_header_bg_type') === 'image';
        },
    )));

    // Background Video URL Setting
    $wp_customize->add_setting('workspaces_header_bg_video', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('workspaces_header_bg_video', array(
        'label'           => __('Header Background Video URL', 'hub21-base'),
        'description'     => __('Enter a URL to an MP4 video file.', 'hub21-base'),
        'section'         => 'workspaces_header_background',
        'type'            => 'url',
        'active_callback' => function () {
            return get_theme_mod('workspaces_header_bg_type') === 'video';
        },
    ));

    // Video Poster Image (fallback)
    $wp_customize->add_setting('workspaces_header_bg_video_poster', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'workspaces_header_bg_video_poster', array(
        'label'           => __('Video Poster Image (Fallback)', 'hub21-base'),
        'description'     => __('Displayed while video loads or on mobile.', 'hub21-base'),
        'section'         => 'workspaces_header_background',
        'active_callback' => function () {
            return get_theme_mod('workspaces_header_bg_type') === 'video';
        },
    )));

    // Overlay Color Setting
    $wp_customize->add_setting('workspaces_header_bg_overlay_color', array(
        'default'           => 'rgba(0, 0, 0, 0.3)',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('workspaces_header_bg_overlay_color', array(
        'label'           => __('Overlay Color', 'hub21-base'),
        'description'     => __('Use rgba format, e.g., rgba(0, 0, 0, 0.3)', 'hub21-base'),
        'section'         => 'workspaces_header_background',
        'type'            => 'text',
        'active_callback' => function () {
            return get_theme_mod('workspaces_header_bg_type') !== 'none';
        },
    ));

    // Header Height Setting
    $wp_customize->add_setting('workspaces_header_bg_height', array(
        'default'           => '400',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('workspaces_header_bg_height', array(
        'label'           => __('Header Height (px)', 'hub21-base'),
        'section'         => 'workspaces_header_background',
        'type'            => 'number',
        'input_attrs'     => array(
            'min'  => 100,
            'max'  => 1000,
            'step' => 10,
        ),
        'active_callback' => function () {
            return get_theme_mod('workspaces_header_bg_type') !== 'none';
        },
    ));
});

/**
 * Sanitize background type
 */
function hub21_sanitize_bg_type($value) {
    $valid = array('none', 'image', 'video');
    return in_array($value, $valid, true) ? $value : 'none';
}

/**
 * ============================================================================
 * 13. HEADER BACKGROUND CSS OUTPUT + RENDER FUNCTION
 * ============================================================================
 */
add_action('wp_head', function () {
    $bg_type = get_theme_mod('workspaces_header_bg_type', 'none');

    if ($bg_type === 'none') {
        return;
    }

    $overlay_color = get_theme_mod('workspaces_header_bg_overlay_color', 'rgba(0, 0, 0, 0.3)');
    $height = get_theme_mod('workspaces_header_bg_height', 400);

    if ($bg_type === 'image') {
        $image_url = get_theme_mod('workspaces_header_bg_image', '');
        if ($image_url) {
            echo '<style>
                .frs-header-background {
                    position: relative;
                    width: 100%;
                    height: ' . esc_attr($height) . 'px;
                    background-image: url(' . esc_url($image_url) . ');
                    background-size: cover;
                    background-position: center;
                }
                .frs-header-background::after {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: ' . esc_attr($overlay_color) . ';
                }
                .frs-header-background-content {
                    position: relative;
                    z-index: 1;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
            </style>';
        }
    } elseif ($bg_type === 'video') {
        $video_url = get_theme_mod('workspaces_header_bg_video', '');
        $poster_url = get_theme_mod('workspaces_header_bg_video_poster', '');

        if ($video_url) {
            echo '<style>
                .frs-header-background {
                    position: relative;
                    width: 100%;
                    height: ' . esc_attr($height) . 'px;
                    overflow: hidden;
                }
                .frs-header-background video {
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    min-width: 100%;
                    min-height: 100%;
                    width: auto;
                    height: auto;
                    transform: translate(-50%, -50%);
                    object-fit: cover;
                }
                .frs-header-background::after {
                    content: "";
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: ' . esc_attr($overlay_color) . ';
                    z-index: 1;
                }
                .frs-header-background-content {
                    position: relative;
                    z-index: 2;
                    height: 100%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                @media (max-width: 768px) {
                    .frs-header-background video {
                        display: none;
                    }
                    .frs-header-background {
                        background-image: url(' . esc_url($poster_url ?: $video_url) . ');
                        background-size: cover;
                        background-position: center;
                    }
                }
            </style>';
        }
    }
});

/**
 * Render header background section
 * Usage: hub21_render_header_background();
 */
function hub21_render_header_background() {
    $bg_type = get_theme_mod('workspaces_header_bg_type', 'none');

    if ($bg_type === 'none') {
        return;
    }

    echo '<div class="workspace-header-background">';

    if ($bg_type === 'video') {
        $video_url = get_theme_mod('workspaces_header_bg_video', '');
        $poster_url = get_theme_mod('workspaces_header_bg_video_poster', '');

        if ($video_url) {
            echo '<video autoplay muted loop playsinline';
            if ($poster_url) {
                echo ' poster="' . esc_url($poster_url) . '"';
            }
            echo '>';
            echo '<source src="' . esc_url($video_url) . '" type="video/mp4">';
            echo '</video>';
        }
    }

    echo '<div class="workspace-header-background-content">';
    do_action('workspaces_header_background_content');
    echo '</div>';
    echo '</div>';
}

/**
 * Auto-insert header background after header (optional)
 */
add_action('blocksy:header:after', function () {
    $bg_type = get_theme_mod('workspaces_header_bg_type', 'none');
    $auto_insert = apply_filters('workspaces_auto_insert_header_background', false);

    if ($bg_type !== 'none' && $auto_insert) {
        hub21_render_header_background();
    }
});

/**
 * ============================================================================
 * 14. SIDEBAR FRAME INJECTION (blocksy:header:after)
 * ============================================================================
 */
add_action('blocksy:header:after', function () {
    // Skip sidebar frame if hidden for this post
    $post_id = get_queried_object_id();
    if ($post_id && get_post_meta($post_id, '_hide_sidebar', true)) {
        return;
    }

    include get_stylesheet_directory() . '/workspace-sidebar-frame.php';
});

/**
 * ============================================================================
 * 15. BLOCKSY ACCOUNT MODAL
 * ============================================================================
 */
add_action('wp_footer', function () {
    // Only load for logged-out users on workspace pages
    if (is_user_logged_in()) {
        return;
    }

    if (!function_exists('hub21_is_workspace_page') || !hub21_is_workspace_page()) {
        return;
    }

    // Use Blocksy Pro's header class to render the modal
    if (class_exists('\Blocksy\Plugin') && class_exists('Blocksy_Header_Builder_Render')) {
        $plugin = \Blocksy\Plugin::instance();
        if ($plugin->header && method_exists($plugin->header, 'retrieve_account_modal')) {
            echo $plugin->header->retrieve_account_modal();
        }
    }
}, 5);

/**
 * ============================================================================
 * 16. SIDEBAR SCROLL JS
 * ============================================================================
 */
add_action('wp_footer', function () {
    ?>
    <script>
    (function() {
        const sidebar = document.querySelector('.ct-sidebar');
        if (!sidebar) return;

        sidebar.addEventListener('wheel', function(e) {
            const maxScroll = this.scrollHeight - this.clientHeight;
            const currentScroll = this.scrollTop;
            const delta = e.deltaY;

            // Only prevent default if we can scroll in that direction
            if ((delta > 0 && currentScroll < maxScroll) || (delta < 0 && currentScroll > 0)) {
                e.preventDefault();
                e.stopPropagation();
                this.scrollTop += delta;
            }
        }, { passive: false });
    })();
    </script>
    <?php
}, 100);

/**
 * ============================================================================
 * 17. NAV WALKERS (Workspace_Nav_Walker, User_Menu_Walker)
 *     Class names kept as-is per naming convention
 * ============================================================================
 */

/**
 * Custom Nav Walker for Workspace Sidebar Menu
 * Generates navigation with icons, dropdowns, and proper styling
 */
class Workspace_Nav_Walker extends Walker_Nav_Menu {

    /**
     * Override walk() to group items into accordion sections.
     */
    public function walk($elements, $max_depth, ...$args) {
        $top_level = [];
        $children_map = [];

        foreach ($elements as $el) {
            if (empty($el->menu_item_parent) || $el->menu_item_parent == 0) {
                $top_level[] = $el;
            } else {
                $children_map[$el->menu_item_parent][] = $el;
            }
        }

        $ungrouped = [];
        $sections = [];
        $current_section = -1;

        foreach ($top_level as $item) {
            $is_section_header = trim($item->url) === '#'
                && strtolower(trim($item->title)) !== 'divider';

            if ($is_section_header) {
                $current_section++;
                $sections[$current_section] = [
                    'title' => $item->title,
                    'items' => [],
                ];
                if (isset($children_map[$item->ID])) {
                    foreach ($children_map[$item->ID] as $child) {
                        $sections[$current_section]['items'][] = $child;
                    }
                }
            } elseif ($current_section < 0) {
                $ungrouped[] = $item;
            } else {
                $sections[$current_section]['items'][] = $item;
                if (isset($children_map[$item->ID])) {
                    foreach ($children_map[$item->ID] as $child) {
                        $sections[$current_section]['items'][] = $child;
                    }
                }
            }
        }

        $output = '';

        foreach ($ungrouped as $item) {
            $output .= $this->render_item($item);
        }

        foreach ($sections as $idx => $section) {
            $section_id = 'nav-section-' . $idx;
            $is_first = ($idx === 0);

            $output .= '<div class="frs-section-header' . ($is_first ? ' is-open' : '') . '" data-section="' . esc_attr($section_id) . '" onclick="switchSection(\'' . esc_attr($section_id) . '\', event)">';
            $output .= '<span class="frs-section-title">' . esc_html($section['title']) . '</span>';
            $output .= '<span class="frs-section-line"></span>';
            $output .= '<svg class="frs-section-chevron" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>';
            $output .= '</div>';

            $display = $is_first ? 'block' : 'none';
            $output .= '<div id="' . esc_attr($section_id) . '" class="frs-section-body" style="display: ' . $display . '; overflow: hidden;">';
            foreach ($section['items'] as $item) {
                $output .= $this->render_item($item);
            }
            $output .= '</div>';
        }

        return $output;
    }

    /**
     * Render a single flat nav item.
     */
    private function render_item($item) {
        $icon_name = get_post_meta($item->ID, '_menu_item_icon', true);
        $icon_html = $icon_name && class_exists('Lucide_Icons') ? Lucide_Icons::render($icon_name, 20) : '';
        $is_active = is_array($item->classes) && (in_array('current-menu-item', $item->classes) || in_array('current-page-ancestor', $item->classes));
        $active_class = $is_active ? ' active' : '';

        $html = '<a href="' . esc_url($item->url) . '" class="frs-nav-link flex items-center gap-2 px-4 py-3' . $active_class . '">';
        $html .= $icon_html;
        $html .= '<span>' . esc_html($item->title) . '</span>';
        $html .= '</a>';

        return $html;
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {}
    public function end_el(&$output, $item, $depth = 0, $args = null) {}
    public function start_lvl(&$output, $depth = 0, $args = null) {}
    public function end_lvl(&$output, $depth = 0, $args = null) {}
}

/**
 * Custom Nav Walker for User Popup Menu
 */
class User_Menu_Walker extends Walker_Nav_Menu {
    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $icon_name = get_post_meta($item->ID, '_menu_item_icon', true);
        $is_active = in_array('current-menu-item', $item->classes);
        $active_class = $is_active ? ' bg-white/10' : '';

        $icon_html = '';
        if ($icon_name && class_exists('Lucide_Icons')) {
            $icon_html = Lucide_Icons::render($icon_name, 18);
        }

        $output .= '<a href="' . esc_url($item->url) . '" class="flex items-center gap-3 px-4 py-2.5 text-white/70 hover:text-white hover:bg-white/5 transition-colors' . $active_class . '">';
        $output .= $icon_html;
        $output .= '<span class="text-sm">' . esc_html($item->title) . '</span>';
        $output .= '</a>';
    }

    public function end_el(&$output, $item, $depth = 0, $args = null) {
        // No closing tag needed
    }
}

/**
 * ============================================================================
 * 18. WORKSPACE PAGE DETECTION + TYPE
 * ============================================================================
 */

/**
 * Check if current page should use workspace frame
 */
function hub21_is_workspace_page() {
    if (function_exists('workspaces_is_in_workspace')) {
        return workspaces_is_in_workspace();
    }
    return false;
}

/**
 * Get the workspace type (lo or re)
 */
function hub21_get_workspace_type() {
    if (!is_page()) {
        return null;
    }

    global $post;

    if ($post->post_name === 'lo') {
        return 'lo';
    }
    if ($post->post_name === 're') {
        return 're';
    }

    if ($post->post_parent) {
        $parent = get_post($post->post_parent);
        if ($parent) {
            if ($parent->post_name === 'lo') {
                return 'lo';
            }
            if ($parent->post_name === 're') {
                return 're';
            }
        }
    }

    return null;
}

/**
 * ============================================================================
 * 19. INLINE WORKSPACE FRAME STYLES
 * ============================================================================
 */
add_action('template_redirect', function() {
    add_action('wp_print_scripts', function() {

    // Enqueue LRG portal sidebar assets
    if (class_exists('\LendingResourceHub\Assets\Frontend')) {
        \LendingResourceHub\Assets\Frontend::get_instance()->enqueue_portal_assets_public();
    }

    // Add workspace frame styles
    wp_add_inline_style('workspaces-style', '
        body.workspace-frame {
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        body.workspace-frame #main-container {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .workspace-frame-container {
            display: flex;
            height: 100vh;
            max-height: 100vh;
            width: 100vw;
            overflow: hidden;
            position: relative;
        }

        .workspace-sidebar-wrapper {
            width: 320px;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            height: 100vh;
            max-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 100;
        }

        .workspace-logo-section {
            background: #0b102c;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 0 1rem;
            flex-shrink: 0;
        }

        .workspace-logo-section img {
            height: 32px;
            width: auto;
        }

        .workspace-sidebar-container {
            flex: 1;
            background: #0b102c;
            overflow-y: auto;
        }

        .workspace-content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            margin-left: 320px;
            height: 100vh;
            max-height: 100vh;
        }

        .workspace-top-bar {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            flex-shrink: 0;
        }

        .workspace-top-bar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .workspace-top-bar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .workspace-content-area {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 1rem;
            min-height: 0;
        }

        .workspace-content-area > * {
            margin-top: 0 !important;
        }

        .workspace-content-area .entry-content {
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        .workspace-content-area h2,
        .workspace-content-area .wp-block-heading {
            margin-top: 0 !important;
        }

        @media (max-width: 768px) {
            .workspace-sidebar-wrapper {
                position: fixed;
                left: -280px;
                top: 0;
                height: 100vh;
                z-index: 1000;
                transition: left 0.3s ease;
            }

            .workspace-sidebar-wrapper.open {
                left: 0;
            }
        }
    ');
    }); // End wp_print_scripts
}); // End template_redirect

/**
 * ============================================================================
 * 20. BODY CLASS FILTER
 * ============================================================================
 */
add_filter('body_class', function($classes) {
    // Don't add has-workspace-sidebar class for frs_re_portal
    $queried_object = get_queried_object();
    if ($queried_object && isset($queried_object->post_type) && $queried_object->post_type === 'frs_re_portal') {
        return $classes;
    }

    // Check if sidebar is hidden for this post
    $post_id = get_queried_object_id();
    if ($post_id && get_post_meta($post_id, '_hide_sidebar', true)) {
        return $classes;
    }

    $classes[] = 'has-workspace-sidebar';

    // Check if title bar is hidden for this post
    if ($post_id && get_post_meta($post_id, '_hide_titlebar', true)) {
        $classes[] = 'hide-titlebar';
    }

    // Additional class for workspace-specific pages
    if (hub21_is_workspace_page()) {
        $classes[] = 'workspace-frame';
    }

    // Add sidebar-offcanvas class only for lesson pages (sidebar collapsed by default)
    if (is_singular('lesson')) {
        $classes[] = 'sidebar-offcanvas';
    }

    return $classes;
});

/**
 * ============================================================================
 * 21. INTERACTIVITY API SCRIPT ENQUEUING
 * ============================================================================
 */
add_action('wp_enqueue_scripts', function() {
    // Sidebar toggle is always available when theme is active
    wp_enqueue_script_module(
        'workspaces-sidebar',
        get_stylesheet_directory_uri() . '/assets/js/sidebar-view.js',
        ['@wordpress/interactivity'],
        HUB21_THEME_VERSION
    );
    // Mark module as compatible with client-side navigation (WordPress 6.9+)
    if (function_exists('wp_interactivity')) {
        wp_interactivity()->add_client_navigation_support_to_script_module('workspaces-sidebar');
    }

    // Header datetime display on workspace and course pages
    $is_course_page = (
        is_singular('courses') ||
        is_singular('lesson') ||
        is_singular('tutor_quiz') ||
        is_singular('tutor_assignments') ||
        is_post_type_archive('courses')
    );
    if (hub21_is_workspace_page() || $is_course_page) {
        wp_enqueue_script_module(
            'workspaces-datetime',
            get_stylesheet_directory_uri() . '/assets/js/datetime-view.js',
            ['@wordpress/interactivity'],
            HUB21_THEME_VERSION
        );
        if (function_exists('wp_interactivity')) {
            wp_interactivity()->add_client_navigation_support_to_script_module('workspaces-datetime');
        }
    }
});

/**
 * ============================================================================
 * 22. BLOCK ASSET LOADING FOR HOMEPAGE OBJECTS
 * ============================================================================
 */
add_action('wp_enqueue_scripts', function () {
    // Get current workspace from plugin
    $current_term = function_exists('workspaces_get_current') ? workspaces_get_current() : null;

    if (!$current_term) {
        return;
    }

    // Check if this term has a homepage object
    $homepage_id = get_term_meta($current_term->term_id, '_term_homepage', true);

    if (!$homepage_id) {
        $homepage_id = get_term_meta($current_term->term_id, '_workspace_homepage', true);
    }

    if (!$homepage_id) {
        return;
    }

    $homepage_post = get_post($homepage_id);
    if (!$homepage_post || $homepage_post->post_status !== 'publish') {
        return;
    }

    // Load Greenshift CSS for the homepage object
    $gspb_css = get_post_meta($homepage_id, '_gspb_post_css', true);
    if ($gspb_css) {
        if (function_exists('gspb_get_final_css')) {
            $gspb_css = gspb_get_final_css($gspb_css);
        }
        wp_register_style('greenshift-term-homepage-css', false);
        wp_enqueue_style('greenshift-term-homepage-css');
        wp_add_inline_style('greenshift-term-homepage-css', $gspb_css);
    }

    // Parse the homepage content for blocks and enqueue their assets
    $blocks = parse_blocks($homepage_post->post_content);
    hub21_enqueue_block_assets_recursive($blocks);

}, 20);

/**
 * ============================================================================
 * 23. BLOCKSY OFFCANVAS DRAWER FILTER
 * ============================================================================
 */
add_filter('blocksy:footer:offcanvas-drawer', function($els, $payload) {
    if ($payload['location'] !== 'start') {
        return $els;
    }

    if (is_user_logged_in()) {
        return $els;
    }

    if (!class_exists('Blocksy\Plugin') || !function_exists('blocksy_render_view')) {
        return $els;
    }

    foreach ($els as $el) {
        if (strpos($el, 'id="account-modal"') !== false) {
            return $els;
        }
    }

    $modal_path = WP_PLUGIN_DIR . '/blocksy-companion-pro/framework/features/header/account-modal.php';
    if (!file_exists($modal_path)) {
        return $els;
    }

    $atts = [
        'account_close_button_type' => 'type-1'
    ];

    $html = blocksy_render_view($modal_path, [
        'current_url' => blocksy_current_url(),
        'header_id' => null,
        'atts' => $atts
    ]);

    if ($html) {
        $els[] = $html;
    }

    return $els;
}, 20, 2);

/**
 * ============================================================================
 * 24. RECURSIVE BLOCK ASSET ENQUEUE HELPER
 * ============================================================================
 */

/**
 * Recursively enqueue assets for blocks and their inner blocks
 *
 * @param array $blocks Array of parsed blocks
 */
function hub21_enqueue_block_assets_recursive($blocks) {
    foreach ($blocks as $block) {
        if (!empty($block['blockName'])) {
            $block_name = $block['blockName'];
            $block_registry = WP_Block_Type_Registry::get_instance();
            $block_type = $block_registry->get_registered($block_name);

            if ($block_type) {
                if (!empty($block_type->style)) {
                    $styles = is_array($block_type->style) ? $block_type->style : [$block_type->style];
                    foreach ($styles as $style_handle) {
                        wp_enqueue_style($style_handle);
                    }
                }

                if (!empty($block_type->script)) {
                    $scripts = is_array($block_type->script) ? $block_type->script : [$block_type->script];
                    foreach ($scripts as $script_handle) {
                        wp_enqueue_script($script_handle);
                    }
                }

                if (!empty($block_type->view_script)) {
                    $view_scripts = is_array($block_type->view_script) ? $block_type->view_script : [$block_type->view_script];
                    foreach ($view_scripts as $script_handle) {
                        wp_enqueue_script($script_handle);
                    }
                }

                if (!empty($block_type->view_script_module)) {
                    $view_modules = is_array($block_type->view_script_module) ? $block_type->view_script_module : [$block_type->view_script_module];
                    foreach ($view_modules as $module_handle) {
                        if (function_exists('wp_enqueue_script_module')) {
                            wp_enqueue_script_module($module_handle);
                        }
                    }
                }
            }
        }

        if (!empty($block['innerBlocks'])) {
            hub21_enqueue_block_assets_recursive($block['innerBlocks']);
        }
    }
}
