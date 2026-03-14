<?php
/**
 * Sidebar Menu Location System
 *
 * Create custom menu locations and assign conditions for when they appear.
 *
 * @package Workspaces_Directory_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register menu locations - base + custom
 */
add_action('init', function() {
    // Base locations
    $locations = array(
        'sidebar_menu' => __('Sidebar Menu (Default)', 'hub21-base'),
        'user_menu' => __('User Popup Menu', 'hub21-base'),
    );

    // Add custom locations
    $custom_locations = get_option('hub21_menu_locations', array());
    if (is_array($custom_locations)) {
        foreach ($custom_locations as $id => $location) {
            if (isset($location['name'])) {
                $locations[$id] = $location['name'];
            }
        }
    }

    register_nav_menus($locations);
}, 5);

/**
 * Get the menu location for the current context
 *
 * @return string Menu location ID
 */
function hub21_get_menu_location() {
    $custom_locations = get_option('hub21_menu_locations', array());

    // Check each custom location's conditions
    foreach ($custom_locations as $location_id => $location) {
        if (empty($location['conditions'])) {
            continue;
        }

        foreach ($location['conditions'] as $condition) {
            if (hub21_condition_matches($condition)) {
                return $location_id;
            }
        }
    }

    // Fallback to default
    return 'sidebar_menu';
}

/**
 * Check if a condition matches the current context
 *
 * @param array $condition Condition array with 'type' and 'value'
 * @return bool
 */
function hub21_condition_matches($condition) {
    $type = $condition['type'] ?? '';
    $value = $condition['value'] ?? '';

    if (empty($type) || empty($value)) {
        return false;
    }

    switch ($type) {
        case 'taxonomy_term':
            // Value format: taxonomy:term_slug
            list($taxonomy, $term_slug) = explode(':', $value, 2);

            // Check taxonomy archive
            if (is_tax($taxonomy, $term_slug) || is_category($term_slug) || is_tag($term_slug)) {
                return true;
            }

            // Check query var
            if (get_query_var($taxonomy) === $term_slug) {
                return true;
            }

            // Check if post has this term
            $queried = get_queried_object();
            if ($queried && isset($queried->ID)) {
                if (has_term($term_slug, $taxonomy, $queried->ID)) {
                    return true;
                }
            }
            break;

        case 'post_type':
            if (is_singular($value) || is_post_type_archive($value)) {
                return true;
            }
            break;

        case 'page':
            $queried = get_queried_object();
            if ($queried && isset($queried->ID) && $queried->ID == $value) {
                return true;
            }
            break;
    }

    return false;
}

/**
 * Get current term from context (for display purposes)
 *
 * @return WP_Term|null
 */
function hub21_get_current_term() {
    if (is_tax() || is_category() || is_tag()) {
        return get_queried_object();
    }

    $taxonomies = get_taxonomies(array('public' => true));
    foreach ($taxonomies as $taxonomy) {
        $term_slug = get_query_var($taxonomy);
        if ($term_slug) {
            $term = get_term_by('slug', $term_slug, $taxonomy);
            if ($term && !is_wp_error($term)) {
                return $term;
            }
        }
    }

    $queried = get_queried_object();
    if ($queried && isset($queried->ID)) {
        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($queried->ID, $taxonomy);
            if ($terms && !is_wp_error($terms)) {
                return $terms[0];
            }
        }
    }

    return null;
}

/**
 * Admin page
 */
add_action('admin_menu', function() {
    add_theme_page(
        __('Menu Locations', 'hub21-base'),
        __('Menu Locations', 'hub21-base'),
        'edit_theme_options',
        'menu-locations',
        'hub21_menu_locations_page'
    );
});

/**
 * Handle form submissions
 */
add_action('admin_init', function() {
    if (!isset($_POST['hub21_menu_action'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['_wpnonce'], 'hub21_menu_locations')) {
        wp_die('Security check failed');
    }

    if (!current_user_can('edit_theme_options')) {
        wp_die('Unauthorized');
    }

    $locations = get_option('hub21_menu_locations', array());
    $action = $_POST['hub21_menu_action'];

    if ($action === 'add') {
        $name = sanitize_text_field($_POST['location_name'] ?? '');
        if ($name) {
            $id = 'custom_' . sanitize_key($name) . '_' . time();
            $locations[$id] = array(
                'name' => $name,
                'conditions' => array(),
            );
        }
    }

    if ($action === 'delete' && isset($_POST['location_id'])) {
        $id = sanitize_key($_POST['location_id']);
        unset($locations[$id]);
    }

    if ($action === 'add_condition' && isset($_POST['location_id'])) {
        $id = sanitize_key($_POST['location_id']);
        $type = sanitize_key($_POST['condition_type'] ?? '');
        $value = sanitize_text_field($_POST['condition_value'] ?? '');

        if (isset($locations[$id]) && $type && $value) {
            $locations[$id]['conditions'][] = array(
                'type' => $type,
                'value' => $value,
            );
        }
    }

    if ($action === 'remove_condition' && isset($_POST['location_id'], $_POST['condition_index'])) {
        $id = sanitize_key($_POST['location_id']);
        $index = intval($_POST['condition_index']);

        if (isset($locations[$id]['conditions'][$index])) {
            array_splice($locations[$id]['conditions'], $index, 1);
        }
    }

    update_option('hub21_menu_locations', $locations);

    wp_redirect(admin_url('themes.php?page=menu-locations&updated=1'));
    exit;
});

/**
 * Render admin page
 */
function hub21_menu_locations_page() {
    $locations = get_option('hub21_menu_locations', array());
    $menus = wp_get_nav_menus();
    $menu_assignments = get_nav_menu_locations();

    // Get taxonomies and their terms for condition dropdowns
    $taxonomies = get_taxonomies(array('public' => true, '_builtin' => false), 'objects');
    $taxonomies['category'] = get_taxonomy('category');
    $taxonomies['post_tag'] = get_taxonomy('post_tag');
    unset($taxonomies['post_format']);

    $post_types = get_post_types(array('public' => true), 'objects');
    unset($post_types['attachment']);

    ?>
    <div class="wrap">
        <h1><?php _e('Menu Locations', 'hub21-base'); ?></h1>

        <?php if (isset($_GET['updated'])) : ?>
            <div class="notice notice-success is-dismissible"><p><?php _e('Settings saved.', 'hub21-base'); ?></p></div>
        <?php endif; ?>

        <p><?php _e('Create menu locations and define when they should appear. Assign menus in', 'hub21-base'); ?>
            <a href="<?php echo admin_url('nav-menus.php'); ?>"><?php _e('Appearance → Menus', 'hub21-base'); ?></a>.
        </p>

        <h2><?php _e('Default Locations', 'hub21-base'); ?></h2>
        <table class="widefat striped" style="max-width: 600px;">
            <tbody>
                <tr>
                    <td><strong><?php _e('Sidebar Menu (Default)', 'hub21-base'); ?></strong></td>
                    <td><?php
                        $assigned = isset($menu_assignments['sidebar_menu']) ? wp_get_nav_menu_object($menu_assignments['sidebar_menu']) : null;
                        echo $assigned ? esc_html($assigned->name) : '<em>' . __('Not assigned', 'hub21-base') . '</em>';
                    ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('User Popup Menu', 'hub21-base'); ?></strong></td>
                    <td><?php
                        $assigned = isset($menu_assignments['user_menu']) ? wp_get_nav_menu_object($menu_assignments['user_menu']) : null;
                        echo $assigned ? esc_html($assigned->name) : '<em>' . __('Not assigned', 'hub21-base') . '</em>';
                    ?></td>
                </tr>
            </tbody>
        </table>

        <h2 style="margin-top: 30px;"><?php _e('Custom Locations', 'hub21-base'); ?></h2>

        <?php if (empty($locations)) : ?>
            <p><?php _e('No custom locations yet.', 'hub21-base'); ?></p>
        <?php else : ?>
            <?php foreach ($locations as $id => $location) : ?>
                <div class="card" style="max-width: 600px; margin-bottom: 15px;">
                    <h3 style="margin-top: 0;">
                        <?php echo esc_html($location['name']); ?>
                        <code style="font-size: 12px; margin-left: 10px;"><?php echo esc_html($id); ?></code>
                    </h3>

                    <p>
                        <strong><?php _e('Assigned Menu:', 'hub21-base'); ?></strong>
                        <?php
                        $assigned = isset($menu_assignments[$id]) ? wp_get_nav_menu_object($menu_assignments[$id]) : null;
                        echo $assigned ? esc_html($assigned->name) : '<em>' . __('Not assigned', 'hub21-base') . '</em>';
                        ?>
                    </p>

                    <p><strong><?php _e('Show when:', 'hub21-base'); ?></strong></p>
                    <?php if (empty($location['conditions'])) : ?>
                        <p><em><?php _e('No conditions (never shown)', 'hub21-base'); ?></em></p>
                    <?php else : ?>
                        <ul style="margin: 0 0 15px 20px;">
                            <?php foreach ($location['conditions'] as $index => $condition) : ?>
                                <li>
                                    <?php echo esc_html(hub21_format_condition($condition)); ?>
                                    <form method="post" style="display: inline;">
                                        <?php wp_nonce_field('hub21_menu_locations'); ?>
                                        <input type="hidden" name="hub21_menu_action" value="remove_condition">
                                        <input type="hidden" name="location_id" value="<?php echo esc_attr($id); ?>">
                                        <input type="hidden" name="condition_index" value="<?php echo $index; ?>">
                                        <button type="submit" class="button-link" style="color: #a00;">&times;</button>
                                    </form>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <!-- Add condition form -->
                    <form method="post" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                        <?php wp_nonce_field('hub21_menu_locations'); ?>
                        <input type="hidden" name="hub21_menu_action" value="add_condition">
                        <input type="hidden" name="location_id" value="<?php echo esc_attr($id); ?>">

                        <select name="condition_type" id="condition_type_<?php echo esc_attr($id); ?>" onchange="workspacesToggleConditionValue(this)">
                            <option value=""><?php _e('Select type...', 'hub21-base'); ?></option>
                            <option value="taxonomy_term"><?php _e('Taxonomy Term', 'hub21-base'); ?></option>
                            <option value="post_type"><?php _e('Post Type', 'hub21-base'); ?></option>
                            <option value="page"><?php _e('Specific Page', 'hub21-base'); ?></option>
                        </select>

                        <select name="condition_value" id="condition_value_<?php echo esc_attr($id); ?>" style="min-width: 200px;">
                            <option value=""><?php _e('Select...', 'hub21-base'); ?></option>
                        </select>

                        <button type="submit" class="button"><?php _e('Add Condition', 'hub21-base'); ?></button>
                    </form>

                    <hr style="margin: 15px 0;">

                    <!-- Delete location -->
                    <form method="post">
                        <?php wp_nonce_field('hub21_menu_locations'); ?>
                        <input type="hidden" name="hub21_menu_action" value="delete">
                        <input type="hidden" name="location_id" value="<?php echo esc_attr($id); ?>">
                        <button type="submit" class="button-link" style="color: #a00;" onclick="return confirm('<?php esc_attr_e('Delete this location?', 'hub21-base'); ?>')">
                            <?php _e('Delete Location', 'hub21-base'); ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Add new location -->
        <form method="post" style="margin-top: 20px;">
            <?php wp_nonce_field('hub21_menu_locations'); ?>
            <input type="hidden" name="hub21_menu_action" value="add">
            <input type="text" name="location_name" placeholder="<?php esc_attr_e('Location name...', 'hub21-base'); ?>" required style="width: 250px;">
            <button type="submit" class="button button-primary"><?php _e('Add Location', 'hub21-base'); ?></button>
        </form>

        <p class="submit" style="margin-top: 30px;">
            <a href="<?php echo admin_url('nav-menus.php'); ?>" class="button button-primary"><?php _e('Assign Menus', 'hub21-base'); ?></a>
        </p>
    </div>

    <script>
    // Condition value options data
    var workspacesConditionData = {
        taxonomy_term: [
            <?php
            foreach ($taxonomies as $tax) :
                if (!$tax) continue;
                $terms = get_terms(array('taxonomy' => $tax->name, 'hide_empty' => false));
                if (is_wp_error($terms)) continue;
                foreach ($terms as $term) :
            ?>
            {value: '<?php echo esc_js($tax->name . ':' . $term->slug); ?>', label: '<?php echo esc_js($tax->labels->singular_name . ': ' . $term->name); ?>'},
            <?php endforeach; endforeach; ?>
        ],
        post_type: [
            <?php foreach ($post_types as $pt) : ?>
            {value: '<?php echo esc_js($pt->name); ?>', label: '<?php echo esc_js($pt->labels->singular_name); ?>'},
            <?php endforeach; ?>
        ],
        page: [
            <?php
            $pages = get_posts(array('post_type' => 'page', 'numberposts' => 100, 'orderby' => 'title', 'order' => 'ASC'));
            foreach ($pages as $page) :
            ?>
            {value: '<?php echo esc_js($page->ID); ?>', label: '<?php echo esc_js($page->post_title); ?>'},
            <?php endforeach; ?>
        ]
    };

    function workspacesToggleConditionValue(select) {
        var type = select.value;
        var valueSelect = select.parentElement.querySelector('[name="condition_value"]');
        valueSelect.innerHTML = '<option value=""><?php _e('Select...', 'hub21-base'); ?></option>';

        if (type && workspacesConditionData[type]) {
            workspacesConditionData[type].forEach(function(opt) {
                var option = document.createElement('option');
                option.value = opt.value;
                option.textContent = opt.label;
                valueSelect.appendChild(option);
            });
        }
    }
    </script>
    <?php
}

/**
 * Format condition for display
 */
function hub21_format_condition($condition) {
    $type = $condition['type'] ?? '';
    $value = $condition['value'] ?? '';

    switch ($type) {
        case 'taxonomy_term':
            list($taxonomy, $term_slug) = explode(':', $value, 2);
            $tax_obj = get_taxonomy($taxonomy);
            $term = get_term_by('slug', $term_slug, $taxonomy);
            $tax_label = $tax_obj ? $tax_obj->labels->singular_name : $taxonomy;
            $term_label = $term ? $term->name : $term_slug;
            return sprintf(__('%s is "%s"', 'hub21-base'), $tax_label, $term_label);

        case 'post_type':
            $pt = get_post_type_object($value);
            return sprintf(__('Post type is "%s"', 'hub21-base'), $pt ? $pt->labels->singular_name : $value);

        case 'page':
            $page = get_post($value);
            return sprintf(__('Page is "%s"', 'hub21-base'), $page ? $page->post_title : $value);

        default:
            return $type . ': ' . $value;
    }
}
