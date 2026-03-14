<?php
/**
 * Sidebar State Meta Box
 *
 * Allows per-page control of sidebar default state (open/closed).
 *
 * @package Workspaces_Directory_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the sidebar state meta box for all public post types
 */
add_action('add_meta_boxes', function() {
    $post_types = get_post_types(array('public' => true), 'names');

    foreach ($post_types as $post_type) {
        add_meta_box(
            'hub21_sidebar_state',
            __('Sidebar Settings', 'hub21-base'),
            'hub21_sidebar_state_callback',
            $post_type,
            'side',
            'default'
        );
    }
});

/**
 * Render the sidebar state meta box
 *
 * @param WP_Post $post The current post object
 */
function hub21_sidebar_state_callback($post) {
    // Get current values
    $sidebar_state = get_post_meta($post->ID, '_sidebar_default_state', true);
    $hide_sidebar = get_post_meta($post->ID, '_hide_sidebar', true);
    $hide_titlebar = get_post_meta($post->ID, '_hide_titlebar', true);

    // Add nonce for security
    wp_nonce_field('hub21_sidebar_state', 'hub21_sidebar_state_nonce');
    ?>
    <p>
        <label>
            <input type="checkbox" name="hide_sidebar" value="1" <?php checked($hide_sidebar, '1'); ?>>
            <strong><?php esc_html_e('Hide Sidebar', 'hub21-base'); ?></strong>
        </label>
    </p>
    <p>
        <label>
            <input type="checkbox" name="hide_titlebar" value="1" <?php checked($hide_titlebar, '1'); ?>>
            <strong><?php esc_html_e('Hide Title Bar', 'hub21-base'); ?></strong>
        </label>
    </p>

    <hr style="margin: 15px 0;">

    <p>
        <label for="sidebar_default_state">
            <strong><?php esc_html_e('Default Sidebar State', 'hub21-base'); ?></strong>
        </label>
    </p>
    <p>
        <select name="sidebar_default_state" id="sidebar_default_state" style="width: 100%;">
            <option value="" <?php selected($sidebar_state, ''); ?>>
                <?php esc_html_e('Use Default (from theme settings)', 'hub21-base'); ?>
            </option>
            <option value="open" <?php selected($sidebar_state, 'open'); ?>>
                <?php esc_html_e('Open (sidebar visible)', 'hub21-base'); ?>
            </option>
            <option value="closed" <?php selected($sidebar_state, 'closed'); ?>>
                <?php esc_html_e('Closed (sidebar collapsed)', 'hub21-base'); ?>
            </option>
        </select>
    </p>
    <p class="description">
        <?php esc_html_e('Choose whether the sidebar should be open or closed by default when viewing this page.', 'hub21-base'); ?>
    </p>
    <?php
}

/**
 * Save the sidebar state meta box data
 *
 * @param int $post_id The ID of the post being saved
 */
add_action('save_post', function($post_id) {
    // Check nonce
    if (!isset($_POST['hub21_sidebar_state_nonce']) ||
        !wp_verify_nonce($_POST['hub21_sidebar_state_nonce'], 'hub21_sidebar_state')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Save hide sidebar
    if (isset($_POST['hide_sidebar']) && $_POST['hide_sidebar'] === '1') {
        update_post_meta($post_id, '_hide_sidebar', '1');
    } else {
        delete_post_meta($post_id, '_hide_sidebar');
    }

    // Save hide titlebar
    if (isset($_POST['hide_titlebar']) && $_POST['hide_titlebar'] === '1') {
        update_post_meta($post_id, '_hide_titlebar', '1');
    } else {
        delete_post_meta($post_id, '_hide_titlebar');
    }

    // Save sidebar state
    if (isset($_POST['sidebar_default_state'])) {
        $value = sanitize_text_field($_POST['sidebar_default_state']);

        if (in_array($value, array('', 'open', 'closed'), true)) {
            if ($value === '') {
                delete_post_meta($post_id, '_sidebar_default_state');
            } else {
                update_post_meta($post_id, '_sidebar_default_state', $value);
            }
        }
    }
});

/**
 * Get the sidebar default state for the current page
 *
 * @return string 'open', 'closed', or empty string for default
 */
function hub21_get_sidebar_state() {
    // Check post meta first
    $post_id = get_queried_object_id();
    if ($post_id) {
        $state = get_post_meta($post_id, '_sidebar_default_state', true);
        if ($state) {
            return $state;
        }
    }

    // Check term meta for taxonomy archives
    $current_term = hub21_get_current_term();
    if ($current_term) {
        $state = get_term_meta($current_term->term_id, '_sidebar_default_state', true);
        if ($state) {
            return $state;
        }
    }

    // Return default from theme settings
    return get_theme_mod('sidebar_default_state', 'open');
}

/**
 * Add Customizer setting for global default sidebar state
 */
add_action('customize_register', function($wp_customize) {
    // Add to existing Sidebar Menu Settings section
    $wp_customize->add_setting('sidebar_default_state', array(
        'default'           => 'open',
        'sanitize_callback' => 'hub21_sanitize_sidebar_state',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('sidebar_default_state', array(
        'label'       => __('Default Sidebar State', 'hub21-base'),
        'description' => __('Global default. Can be overridden per-page.', 'hub21-base'),
        'section'     => 'hub21_sidebar_menu',
        'type'        => 'select',
        'choices'     => array(
            'open'   => __('Open (sidebar visible)', 'hub21-base'),
            'closed' => __('Closed (sidebar collapsed)', 'hub21-base'),
        ),
    ));
});

/**
 * Sanitize sidebar state value
 *
 * @param string $value The value to sanitize
 * @return string Sanitized value
 */
function hub21_sanitize_sidebar_state($value) {
    return in_array($value, array('open', 'closed'), true) ? $value : 'open';
}

/**
 * Add term meta box for taxonomy terms
 * Works with all public taxonomies since the flexible menu system supports any taxonomy
 */
add_action('init', function() {
    // Get all public taxonomies
    $taxonomies = get_taxonomies(array('public' => true), 'names');

    foreach ($taxonomies as $taxonomy) {
        // Skip post_format
        if ($taxonomy === 'post_format') {
            continue;
        }

        // Add fields to term add form
        add_action("{$taxonomy}_add_form_fields", 'hub21_term_sidebar_field_add');

        // Add fields to term edit form
        add_action("{$taxonomy}_edit_form_fields", 'hub21_term_sidebar_field_edit', 10, 2);

        // Save term meta
        add_action("created_{$taxonomy}", 'hub21_save_term_sidebar_state');
        add_action("edited_{$taxonomy}", 'hub21_save_term_sidebar_state');
    }
}, 20); // Run late to ensure taxonomies are registered

/**
 * Render sidebar state field for term add form
 */
function hub21_term_sidebar_field_add() {
    ?>
    <div class="form-field">
        <label for="term_sidebar_state"><?php esc_html_e('Default Sidebar State', 'hub21-base'); ?></label>
        <select name="term_sidebar_state" id="term_sidebar_state">
            <option value=""><?php esc_html_e('Use Global Default', 'hub21-base'); ?></option>
            <option value="open"><?php esc_html_e('Open (sidebar visible)', 'hub21-base'); ?></option>
            <option value="closed"><?php esc_html_e('Closed (sidebar collapsed)', 'hub21-base'); ?></option>
        </select>
        <p class="description"><?php esc_html_e('Default sidebar state for pages in this term.', 'hub21-base'); ?></p>
    </div>
    <?php
}

/**
 * Render sidebar state field for term edit form
 *
 * @param WP_Term $term The term being edited
 */
function hub21_term_sidebar_field_edit($term) {
    $value = get_term_meta($term->term_id, '_sidebar_default_state', true);
    ?>
    <tr class="form-field">
        <th scope="row"><label for="term_sidebar_state"><?php esc_html_e('Default Sidebar State', 'hub21-base'); ?></label></th>
        <td>
            <select name="term_sidebar_state" id="term_sidebar_state">
                <option value="" <?php selected($value, ''); ?>><?php esc_html_e('Use Global Default', 'hub21-base'); ?></option>
                <option value="open" <?php selected($value, 'open'); ?>><?php esc_html_e('Open (sidebar visible)', 'hub21-base'); ?></option>
                <option value="closed" <?php selected($value, 'closed'); ?>><?php esc_html_e('Closed (sidebar collapsed)', 'hub21-base'); ?></option>
            </select>
            <p class="description"><?php esc_html_e('Default sidebar state for pages in this term.', 'hub21-base'); ?></p>
        </td>
    </tr>
    <?php
}

/**
 * Save term sidebar state meta
 *
 * @param int $term_id The term ID being saved
 */
function hub21_save_term_sidebar_state($term_id) {
    if (isset($_POST['term_sidebar_state'])) {
        $value = sanitize_text_field($_POST['term_sidebar_state']);

        if (in_array($value, array('', 'open', 'closed'), true)) {
            if ($value === '') {
                delete_term_meta($term_id, '_sidebar_default_state');
            } else {
                update_term_meta($term_id, '_sidebar_default_state', $value);
            }
        }
    }
}
