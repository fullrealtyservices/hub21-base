<?php
/**
 * Menu Item Icons
 *
 * Adds Lucide icon selection to WordPress menu items with a proper
 * modal-based picker featuring search and SVG previews.
 *
 * @package Workspaces_Directory_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

if (class_exists('Menu_Item_Icons')) {
    return;
}

class Menu_Item_Icons {

    private static $instance = null;

    public static function instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('wp_nav_menu_item_custom_fields', array($this, 'add_icon_field'), 10, 5);
        add_action('wp_update_nav_menu_item', array($this, 'save_icon_field'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_footer', array($this, 'render_icon_picker_modal'));
        add_action('wp_ajax_hub21_download_icon', array($this, 'ajax_download_icon'));
        add_action('wp_ajax_hub21_get_lucide_icons', array($this, 'ajax_get_lucide_icons'));
    }

    /**
     * Enqueue icon picker assets on nav-menus screen
     */
    public function enqueue_assets($hook) {
        if ($hook !== 'nav-menus.php') {
            return;
        }

        wp_enqueue_style(
            'workspaces-directory-icon-picker',
            HUB21_THEME_URL . '/assets/css/icon-picker.css',
            array(),
            HUB21_THEME_VERSION
        );

        wp_enqueue_script(
            'workspaces-directory-icon-picker',
            HUB21_THEME_URL . '/assets/js/icon-picker.js',
            array('jquery'),
            HUB21_THEME_VERSION,
            true
        );

        // Get icons sorted alphabetically
        $icons = Lucide_Icons::get_all_icons();
        sort($icons);

        // Build icons data with SVG paths
        $icons_data = array();
        foreach ($icons as $icon_name) {
            $icons_data[$icon_name] = $this->get_icon_svg($icon_name);
        }

        // Get any custom downloaded icons
        $custom_icons = get_option('hub21_custom_icons', array());

        wp_localize_script('workspaces-directory-icon-picker', 'workspaceIconPicker', array(
            'icons' => $icons_data,
            'customIcons' => $custom_icons,
            'categories' => Lucide_Icons::get_categorized_icons(),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hub21_icons'),
            'i18n' => array(
                'selectIcon' => __('Select Icon', 'hub21-base'),
                'search' => __('Search icons...', 'hub21-base'),
                'searchAll' => __('Search all Lucide icons...', 'hub21-base'),
                'noResults' => __('No icons found', 'hub21-base'),
                'all' => __('All', 'hub21-base'),
                'downloaded' => __('Downloaded', 'hub21-base'),
                'browseMore' => __('Browse More Icons', 'hub21-base'),
                'downloading' => __('Downloading...', 'hub21-base'),
                'downloaded_success' => __('Icon added!', 'hub21-base'),
                'back' => __('Back to Local', 'hub21-base'),
            ),
        ));
    }

    /**
     * Get SVG markup for an icon
     */
    private function get_icon_svg($name) {
        return Lucide_Icons::render($name, 24, '', 'currentColor');
    }

    /**
     * Add icon field to menu item
     */
    public function add_icon_field($item_id, $menu_item, $depth, $args, $current_object_id) {
        $icon = get_post_meta($item_id, '_menu_item_icon', true);
        $icon_preview = $icon ? $this->get_icon_svg($icon) : '';
        $icon_label = $icon ? ucwords(str_replace('-', ' ', $icon)) : __('Select Icon', 'hub21-base');
        ?>
        <p class="field-icon description description-wide">
            <label for="edit-menu-item-icon-<?php echo esc_attr($item_id); ?>">
                <?php esc_html_e('Icon', 'hub21-base'); ?>
            </label>
            <span class="icon-picker-field">
                <input
                    type="hidden"
                    id="edit-menu-item-icon-<?php echo esc_attr($item_id); ?>"
                    name="menu-item-icon[<?php echo esc_attr($item_id); ?>]"
                    value="<?php echo esc_attr($icon); ?>"
                    class="icon-picker-input"
                >
                <button type="button" class="button icon-picker-trigger" data-target="edit-menu-item-icon-<?php echo esc_attr($item_id); ?>">
                    <span class="icon-preview"><?php echo $icon_preview; ?></span>
                    <span class="icon-label"><?php echo esc_html($icon_label); ?></span>
                </button>
                <?php if ($icon) : ?>
                    <button type="button" class="button-link icon-picker-clear" title="<?php esc_attr_e('Clear icon', 'hub21-base'); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                <?php endif; ?>
            </span>
        </p>
        <style>
            .icon-picker-field {
                display: flex;
                align-items: center;
                gap: 8px;
                margin-top: 4px;
            }
            .icon-picker-trigger {
                display: inline-flex !important;
                align-items: center;
                gap: 8px;
                padding: 6px 12px !important;
                min-width: 160px;
            }
            .icon-picker-trigger .icon-preview {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 20px;
                height: 20px;
            }
            .icon-picker-trigger .icon-preview svg {
                width: 20px;
                height: 20px;
            }
            .icon-picker-trigger .icon-preview:empty::before {
                content: '\2014';
                color: #999;
            }
            .icon-picker-clear {
                color: #a00;
                padding: 4px;
            }
            .icon-picker-clear:hover {
                color: #dc3232;
            }
        </style>
        <?php
    }

    /**
     * Save icon field
     */
    public function save_icon_field($menu_id, $menu_item_id) {
        if (isset($_POST['menu-item-icon'][$menu_item_id])) {
            $icon = sanitize_text_field($_POST['menu-item-icon'][$menu_item_id]);
            if ($icon) {
                update_post_meta($menu_item_id, '_menu_item_icon', $icon);
            } else {
                delete_post_meta($menu_item_id, '_menu_item_icon');
            }
        }
    }

    /**
     * Render the icon picker modal in admin footer
     */
    public function render_icon_picker_modal() {
        $screen = get_current_screen();
        if (!$screen || $screen->base !== 'nav-menus') {
            return;
        }
        ?>
        <div id="workspace-icon-picker-modal" class="workspace-icon-picker-modal" style="display: none;">
            <div class="modal-overlay"></div>
            <div class="modal-container">
                <div class="modal-header">
                    <h2 class="modal-title"><?php esc_html_e('Select Icon', 'hub21-base'); ?></h2>
                    <button type="button" class="modal-close" aria-label="<?php esc_attr_e('Close', 'hub21-base'); ?>">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="modal-search">
                    <input type="text" class="icon-search-input" placeholder="<?php esc_attr_e('Search icons...', 'hub21-base'); ?>" autofocus>
                </div>
                <div class="modal-categories">
                    <button type="button" class="category-btn active" data-category="all"><?php esc_html_e('All', 'hub21-base'); ?></button>
                </div>
                <div class="modal-grid">
                    <!-- Icons populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="button browse-more-btn">
                        <span class="dashicons dashicons-download"></span>
                        <?php esc_html_e('Browse More Icons', 'hub21-base'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * AJAX: Get full Lucide icon list from CDN
     */
    public function ajax_get_lucide_icons() {
        check_ajax_referer('hub21_icons', 'nonce');

        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Permission denied');
        }

        // Fetch icon list from Lucide API/CDN
        $cache_key = 'lucide_all_icons';
        $icons = get_transient($cache_key);

        if (false === $icons) {
            // Fetch the icon names from Lucide's tags.json
            $response = wp_remote_get('https://unpkg.com/lucide-static@latest/tags.json', array(
                'timeout' => 15,
            ));

            if (is_wp_error($response)) {
                wp_send_json_error('Failed to fetch icons: ' . $response->get_error_message());
            }

            $body = wp_remote_retrieve_body($response);
            $tags = json_decode($body, true);

            if (!is_array($tags)) {
                wp_send_json_error('Invalid response from Lucide');
            }

            // Get icon names (keys of the tags object)
            $icons = array_keys($tags);
            sort($icons);

            // Cache for 24 hours
            set_transient($cache_key, $icons, DAY_IN_SECONDS);
        }

        // Get already downloaded icons
        $local_icons = Lucide_Icons::get_all_icons();
        $custom_icons = array_keys(get_option('hub21_custom_icons', array()));
        $existing = array_merge($local_icons, $custom_icons);

        wp_send_json_success(array(
            'icons' => $icons,
            'existing' => $existing,
        ));
    }

    /**
     * AJAX: Download a specific icon from Lucide CDN
     */
    public function ajax_download_icon() {
        check_ajax_referer('hub21_icons', 'nonce');

        if (!current_user_can('edit_theme_options')) {
            wp_send_json_error('Permission denied');
        }

        $icon_name = sanitize_file_name($_POST['icon'] ?? '');

        if (empty($icon_name)) {
            wp_send_json_error('No icon specified');
        }

        // Check if already exists locally
        if (Lucide_Icons::exists($icon_name)) {
            $svg = Lucide_Icons::render($icon_name, 24, '', 'currentColor');
            wp_send_json_success(array(
                'icon' => $icon_name,
                'svg' => $svg,
                'already_exists' => true,
            ));
        }

        // Check if already downloaded
        $custom_icons = get_option('hub21_custom_icons', array());
        if (isset($custom_icons[$icon_name])) {
            wp_send_json_success(array(
                'icon' => $icon_name,
                'svg' => $custom_icons[$icon_name],
                'already_exists' => true,
            ));
        }

        // Fetch the icon SVG from Lucide CDN
        $url = 'https://unpkg.com/lucide-static@latest/icons/' . $icon_name . '.svg';
        $response = wp_remote_get($url, array('timeout' => 10));

        if (is_wp_error($response)) {
            wp_send_json_error('Failed to download icon: ' . $response->get_error_message());
        }

        $status = wp_remote_retrieve_response_code($response);
        if ($status !== 200) {
            wp_send_json_error('Icon not found (HTTP ' . $status . ')');
        }

        $svg = wp_remote_retrieve_body($response);

        // Validate it's an SVG
        if (strpos($svg, '<svg') === false) {
            wp_send_json_error('Invalid SVG response');
        }

        // Store the downloaded icon
        $custom_icons[$icon_name] = $svg;
        update_option('hub21_custom_icons', $custom_icons);

        wp_send_json_success(array(
            'icon' => $icon_name,
            'svg' => $svg,
            'already_exists' => false,
        ));
    }
}
