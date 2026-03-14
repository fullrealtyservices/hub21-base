<?php
/**
 * Sidebar Insights Slider
 *
 * Creates a Greenshift-powered animated slider for the portal sidebar header widget area
 * with contextual insights, market updates, and dynamic content.
 *
 * @package Workspaces_Directory_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Generate a unique Greenshift block ID
 */
function frs_generate_gs_id() {
    return 'gsbp-' . substr(md5(uniqid(mt_rand(), true)), 0, 7);
}

/**
 * Get the insights slider block markup
 *
 * @return string The Gutenberg block markup
 */
function frs_get_insights_slider_markup() {
    // Generate unique IDs for all blocks
    $slider_id = frs_generate_gs_id();
    $slide1_id = frs_generate_gs_id();
    $slide1_text_id = frs_generate_gs_id();
    $slide1_heading_id = frs_generate_gs_id();
    $slide2_id = frs_generate_gs_id();
    $slide2_text_id = frs_generate_gs_id();
    $slide2_heading_id = frs_generate_gs_id();
    $slide3_id = frs_generate_gs_id();
    $slide3_text_id = frs_generate_gs_id();
    $slide3_heading_id = frs_generate_gs_id();

    // Get current user for personalization
    $current_user = wp_get_current_user();
    $greeting = frs_get_time_greeting();
    $user_name = $current_user->display_name ?: 'there';

    $markup = <<<BLOCKS
<!-- wp:greenshift-blocks/swiper {"id":"{$slider_id}","tabs":3,"slidesPerView":[1],"spaceBetween":[0],"speed":800,"loop":true,"autoplay":true,"autodelay":5000,"effect":"fade","navigationarrows":false,"bullets":true,"kenBurnsEnable":true} -->
<div class="wp-block-greenshift-blocks-swiper gs-swiper gspb_slider-id-{$slider_id}" style="position:relative"><div class="gs-swiper-init" data-slidesperview="1" data-spacebetween="0" data-spacebetweenmd="0" data-spacebetweensm="0" data-spacebetweenxs="0" data-speed="800" data-loop="true" data-vertical="false" data-verticalheight="500px" data-autoheight="false" data-grabcursor="false" data-freemode="false" data-centered="false" data-autoplay="true" data-autodelay="5000" data-effect="fade" data-coverflowshadow="false" data-kenburns="true"><div class="swiper"><div class="swiper-wrapper"><!-- wp:greenshift-blocks/swipe {"imageurl":"https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=640&q=80","imagealt":"Welcome","asImage":true,"id":"{$slide1_id}","overlayColor":"rgba(11, 16, 44, 0.7)"} -->
<div class="swiper-slide"><div class="wp-block-greenshift-blocks-swipe swiper-slide-inner gspb_sliderinner-id-{$slide1_id}"><div class="slider-overlaybg" style="background:rgba(11, 16, 44, 0.7)"></div><div class="slider-image-wrapper"><img src="https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=640&q=80" alt="Welcome" loading="lazy" width="100%" height="100%"/></div><div class="slider-content-zone"><!-- wp:greenshift-blocks/element {"id":"{$slide1_text_id}","textContent":"{$greeting}","tag":"span","localId":"{$slide1_text_id}","styleAttributes":{"fontSize":["12px"],"color":["rgba(255,255,255,0.7)"],"textTransform":["uppercase"],"letterSpacing":["2px"],"fontWeight":["600"],"marginBottom":["8px"],"display":["block"],"animation_keyframes_Extra":[{"name":"gs_fade_up","code":"0%{opacity:0;transform:translateY(10px);}100%{opacity:1;transform:translateY(0);}"}],"animation":["gs_fade_up 0.6s ease-out 0.2s both"]}} -->
<span class="{$slide1_text_id}">{$greeting}</span>
<!-- /wp:greenshift-blocks/element -->

<!-- wp:greenshift-blocks/element {"id":"{$slide1_heading_id}","textContent":"{$user_name}","tag":"h3","localId":"{$slide1_heading_id}","styleAttributes":{"fontSize":["24px","22px","20px"],"color":["#ffffff"],"fontWeight":["700"],"marginTop":["0px"],"marginBottom":["0px"],"lineHeight":["1.2"],"animation_keyframes_Extra":[{"name":"gs_fade_up2","code":"0%{opacity:0;transform:translateY(10px);}100%{opacity:1;transform:translateY(0);}"}],"animation":["gs_fade_up2 0.6s ease-out 0.4s both"]}} -->
<h3 class="{$slide1_heading_id}">{$user_name}</h3>
<!-- /wp:greenshift-blocks/element --></div></div></div>
<!-- /wp:greenshift-blocks/swipe -->

<!-- wp:greenshift-blocks/swipe {"imageurl":"https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=640&q=80","imagealt":"Market Update","asImage":true,"id":"{$slide2_id}","overlayColor":"rgba(37, 99, 235, 0.8)"} -->
<div class="swiper-slide"><div class="wp-block-greenshift-blocks-swipe swiper-slide-inner gspb_sliderinner-id-{$slide2_id}"><div class="slider-overlaybg" style="background:rgba(37, 99, 235, 0.8)"></div><div class="slider-image-wrapper"><img src="https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=640&q=80" alt="Market Update" loading="lazy" width="100%" height="100%"/></div><div class="slider-content-zone"><!-- wp:greenshift-blocks/element {"id":"{$slide2_text_id}","textContent":"Market Update","tag":"span","localId":"{$slide2_text_id}","styleAttributes":{"fontSize":["12px"],"color":["rgba(255,255,255,0.7)"],"textTransform":["uppercase"],"letterSpacing":["2px"],"fontWeight":["600"],"marginBottom":["8px"],"display":["block"],"animation_keyframes_Extra":[{"name":"gs_fade_up","code":"0%{opacity:0;transform:translateY(10px);}100%{opacity:1;transform:translateY(0);}"}],"animation":["gs_fade_up 0.6s ease-out 0.2s both"]}} -->
<span class="{$slide2_text_id}">Market Update</span>
<!-- /wp:greenshift-blocks/element -->

<!-- wp:greenshift-blocks/element {"id":"{$slide2_heading_id}","textContent":"Rates are moving!","tag":"h3","localId":"{$slide2_heading_id}","styleAttributes":{"fontSize":["24px","22px","20px"],"color":["#ffffff"],"fontWeight":["700"],"marginTop":["0px"],"marginBottom":["0px"],"lineHeight":["1.2"],"animation_keyframes_Extra":[{"name":"gs_fade_up2","code":"0%{opacity:0;transform:translateY(10px);}100%{opacity:1;transform:translateY(0);}"}],"animation":["gs_fade_up2 0.6s ease-out 0.4s both"]}} -->
<h3 class="{$slide2_heading_id}">Rates are moving!</h3>
<!-- /wp:greenshift-blocks/element --></div></div></div>
<!-- /wp:greenshift-blocks/swipe -->

<!-- wp:greenshift-blocks/swipe {"imageurl":"https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=640&q=80","imagealt":"Tip","asImage":true,"id":"{$slide3_id}","overlayColor":"rgba(16, 185, 129, 0.8)"} -->
<div class="swiper-slide"><div class="wp-block-greenshift-blocks-swipe swiper-slide-inner gspb_sliderinner-id-{$slide3_id}"><div class="slider-overlaybg" style="background:rgba(16, 185, 129, 0.8)"></div><div class="slider-image-wrapper"><img src="https://images.unsplash.com/photo-1553729459-efe14ef6055d?w=640&q=80" alt="Tip" loading="lazy" width="100%" height="100%"/></div><div class="slider-content-zone"><!-- wp:greenshift-blocks/element {"id":"{$slide3_text_id}","textContent":"Quick Tip","tag":"span","localId":"{$slide3_text_id}","styleAttributes":{"fontSize":["12px"],"color":["rgba(255,255,255,0.7)"],"textTransform":["uppercase"],"letterSpacing":["2px"],"fontWeight":["600"],"marginBottom":["8px"],"display":["block"],"animation_keyframes_Extra":[{"name":"gs_fade_up","code":"0%{opacity:0;transform:translateY(10px);}100%{opacity:1;transform:translateY(0);}"}],"animation":["gs_fade_up 0.6s ease-out 0.2s both"]}} -->
<span class="{$slide3_text_id}">Quick Tip</span>
<!-- /wp:greenshift-blocks/element -->

<!-- wp:greenshift-blocks/element {"id":"{$slide3_heading_id}","textContent":"Complete your profile!","tag":"h3","localId":"{$slide3_heading_id}","styleAttributes":{"fontSize":["24px","22px","20px"],"color":["#ffffff"],"fontWeight":["700"],"marginTop":["0px"],"marginBottom":["0px"],"lineHeight":["1.2"],"animation_keyframes_Extra":[{"name":"gs_fade_up2","code":"0%{opacity:0;transform:translateY(10px);}100%{opacity:1;transform:translateY(0);}"}],"animation":["gs_fade_up2 0.6s ease-out 0.4s both"]}} -->
<h3 class="{$slide3_heading_id}">Complete your profile!</h3>
<!-- /wp:greenshift-blocks/element --></div></div></div>
<!-- /wp:greenshift-blocks/swipe --></div></div><div class="swiper-pagination"></div></div></div>
<!-- /wp:greenshift-blocks/swiper -->
BLOCKS;

    return $markup;
}

/**
 * Get time-based greeting
 */
function frs_get_time_greeting() {
    $hour = (int) current_time('G');

    if ($hour >= 5 && $hour < 12) {
        return 'Good morning';
    } elseif ($hour >= 12 && $hour < 17) {
        return 'Good afternoon';
    } elseif ($hour >= 17 && $hour < 21) {
        return 'Good evening';
    } else {
        return 'Hello';
    }
}

/**
 * Insert the insights slider into the portal sidebar header widget area
 *
 * @return bool True on success, false on failure
 */
function frs_insert_insights_slider() {
    $markup = frs_get_insights_slider_markup();

    // Get current sidebars_widgets
    $sidebars_widgets = get_option('sidebars_widgets', array());

    // Get widget_block option
    $widget_blocks = get_option('widget_block', array());

    // Find the next available widget ID
    $widget_id = 1;
    if (!empty($widget_blocks)) {
        $existing_ids = array_keys($widget_blocks);
        $numeric_ids = array_map(function($id) {
            return is_numeric($id) ? (int)$id : 0;
        }, $existing_ids);
        $widget_id = max($numeric_ids) + 1;
    }

    // Create the widget block entry
    $widget_blocks[$widget_id] = array(
        'content' => $markup,
    );

    // Update widget_block option
    update_option('widget_block', $widget_blocks);

    // Add to portal-sidebar-header widget area
    if (!isset($sidebars_widgets['portal-sidebar-header'])) {
        $sidebars_widgets['portal-sidebar-header'] = array();
    }

    // Clear existing widgets and add new one
    $sidebars_widgets['portal-sidebar-header'] = array('block-' . $widget_id);

    // Update sidebars_widgets
    update_option('sidebars_widgets', $sidebars_widgets);

    return true;
}

/**
 * Admin action to insert the slider
 */
add_action('admin_init', function() {
    if (isset($_GET['frs_insert_insights_slider']) && current_user_can('manage_options')) {
        if (wp_verify_nonce($_GET['_wpnonce'], 'frs_insert_insights_slider')) {
            frs_insert_insights_slider();
            wp_redirect(admin_url('widgets.php?frs_slider_inserted=1'));
            exit;
        }
    }
});

/**
 * Show admin notice after slider insertion
 */
add_action('admin_notices', function() {
    if (isset($_GET['frs_slider_inserted'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Insights slider has been added to the Portal Sidebar Header widget area!</p></div>';
    }
});

/**
 * Add admin menu item to insert slider
 */
add_action('admin_menu', function() {
    add_submenu_page(
        '', // Hidden from menu (use empty string, not null)
        'Insert Insights Slider',
        'Insert Insights Slider',
        'manage_options',
        'frs-insert-insights-slider',
        function() {
            $nonce_url = wp_nonce_url(
                admin_url('admin.php?frs_insert_insights_slider=1'),
                'frs_insert_insights_slider'
            );

            echo '<div class="wrap">';
            echo '<h1>Insert Insights Slider</h1>';
            echo '<p>Click the button below to insert the animated insights slider into your portal sidebar header.</p>';
            echo '<a href="' . esc_url($nonce_url) . '" class="button button-primary">Insert Slider Now</a>';
            echo '</div>';
        }
    );
});
