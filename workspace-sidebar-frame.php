<?php
/**
 * Workspace Sidebar Frame
 *
 * Persistent 320px sidebar frame that stays fixed on all workspace pages
 * Uses WordPress Interactivity API for sidebar toggle
 *
 * @package Workspaces_Directory_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current taxonomy term for header title
$current_term = hub21_get_current_term();

// Determine header title - always show current page/post title
if (is_singular()) {
    $header_title = get_the_title();
} elseif (is_tax() || is_category() || is_tag()) {
    $header_title = single_term_title('', false);
} elseif (is_post_type_archive()) {
    $header_title = post_type_archive_title('', false);
} else {
    $header_title = get_bloginfo('name');
}

// Get sidebar state from per-page settings, term settings, or global default
$sidebar_state = hub21_get_sidebar_state();
$is_collapsed = ($sidebar_state === 'closed');

// Special case: lesson pages default to closed if no explicit setting
$is_lesson_page = is_singular('lesson');
if ($is_lesson_page && $sidebar_state === 'open' && !get_post_meta(get_the_ID(), '_sidebar_default_state', true)) {
    $is_collapsed = true;
}

// Check if title bar should be hidden
$post_id = get_queried_object_id();
$hide_titlebar = $post_id ? get_post_meta($post_id, '_hide_titlebar', true) : false;
?>

<div
    data-wp-interactive="workspaces/sidebar"
    <?php echo wp_interactivity_data_wp_context(array(
        'isCollapsed' => $is_collapsed,
        'isLessonPage' => $is_lesson_page,
    )); ?>
    data-wp-class--sidebar-offcanvas="context.isCollapsed"
    data-wp-init="callbacks.initFromStorage"
    class="workspace-frame-wrapper"
>
    <?php if (!$hide_titlebar) : ?>
    <!-- Workspace Header Bar -->
    <div class="workspace-header-bar">
        <!-- Sidebar Toggle Button -->
        <button
            class="sidebar-toggle-btn"
            aria-label="Toggle sidebar"
            data-wp-on--click="actions.toggleSidebar"
        >
            <svg class="ct-icon" width="18" height="14" viewBox="0 0 18 14" aria-hidden="true" data-type="type-3">
                <rect y="0.00" width="18" height="1.7" rx="1"></rect>
                <rect y="6.15" width="18" height="1.7" rx="1"></rect>
                <rect y="12.3" width="18" height="1.7" rx="1"></rect>
            </svg>
        </button>
        <!-- Content Header -->
        <div class="workspace-header-content">
            <h1 class="workspace-page-title"><?php echo esc_html($header_title); ?></h1>
        </div>
        <?php if (get_theme_mod('wd_show_datetime_display', true)) : ?>
        <!-- Date/Time Display -->
        <div
            class="workspace-header-datetime"
            data-wp-interactive="workspaces/datetime"
            data-wp-init="callbacks.startClock"
        >
            <span class="header-date" data-wp-text="state.dateString"></span>
            <span class="header-time" data-wp-text="state.timeString"></span>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Workspace Sidebar Frame (Desktop) -->
    <div class="workspace-sidebar-frame workspace-sidebar-desktop">
        <?php include get_stylesheet_directory() . '/workspace-sidebar-content.php'; ?>
    </div>
</div>

<!-- Inset mode concave corner fillers -->
<div class="wd-corner wd-corner--titlebar-br" aria-hidden="true"></div>
<div class="wd-corner wd-corner--sidebar-br"  aria-hidden="true"></div>

<!-- Mobile Offcanvas Panel (Blocksy compatible) -->
<div id="workspace-sidebar-panel" class="ct-panel workspace-sidebar-mobile" data-behaviour="left-side">
    <div class="ct-panel-inner">
        <div class="ct-panel-actions">
            <button class="ct-toggle-close" data-toggle-panel="#workspace-sidebar-panel" aria-label="<?php esc_attr_e('Close sidebar', 'hub21-base'); ?>">
                <svg class="ct-icon" width="12" height="12" viewBox="0 0 15 15">
                    <path d="M1 15a1 1 0 01-.71-.29 1 1 0 010-1.41l5.8-5.8-5.8-5.8A1 1 0 011.7.29l5.8 5.8 5.8-5.8a1 1 0 011.41 1.41l-5.8 5.8 5.8 5.8a1 1 0 01-1.41 1.41l-5.8-5.8-5.8 5.8A1 1 0 011 15z"></path>
                </svg>
            </button>
        </div>
        <div class="ct-panel-content">
            <?php include get_stylesheet_directory() . '/workspace-sidebar-content.php'; ?>
        </div>
    </div>
</div>

<style>
/* Workspace Sidebar - Using Customizer colors with fallbacks */
:root {
    --workspace-sidebar-width: var(--wd-sidebar-width, var(--wp--custom--sidebar--width, 320px));
    --workspace-sidebar-width-collapsed: var(--wp--custom--sidebar--width-collapsed, 64px);
    --workspace-sidebar-bg: var(--wd-sidebar-bg, #020014);
    --workspace-header-height: var(--wp--custom--sidebar--header-height, 80px);
    --workspace-bar-height: var(--wd-bar-height, var(--wp--custom--sidebar--workspace-header-height, 60px));
    --workspace-header-bg: var(--wd-header-bar-bg, #dce2eb);
    --workspace-header-text: var(--wd-header-bar-text, #0b102c);
    --workspace-border-color: var(--wd-border-color, #a8b4c8);
    --workspace-z-index: var(--wp--custom--sidebar--z-index, 100);
    --workspace-transition: var(--wp--custom--sidebar--transition, all 0.3s ease);
    --workspace-glass-bg: var(--wp--custom--glass--background, rgba(255, 255, 255, 0.1));
    --workspace-glass-blur: var(--wp--custom--glass--backdrop-filter, blur(2px));
    --workspace-toggle-width: var(--wd-toggle-width, 48px);
    --workspace-profile-card-height: var(--wd-profile-card-height, 80px);
}

/* Workspace Sidebar Frame */
.workspace-sidebar-frame {
    position: fixed;
    top: var(--workspace-header-height);
    left: 0;
    width: var(--workspace-sidebar-width);
    height: calc(100vh - var(--workspace-header-height));
    background: var(--workspace-sidebar-bg);
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    border-right: 1px solid var(--workspace-border-color);
    box-shadow: 2px 0 4px rgba(168, 180, 200, 0.1);
    z-index: var(--workspace-z-index);
    transition: var(--workspace-transition);
}

/* Workspace Header Bar */
.workspace-header-bar {
    position: fixed;
    top: var(--workspace-header-height);
    left: var(--workspace-sidebar-width);
    right: 0;
    display: flex;
    height: var(--workspace-bar-height);
    background: var(--workspace-header-bg);
    border-bottom: 1px solid var(--workspace-border-color);
    box-shadow: 0 2px 4px rgba(168, 180, 200, 0.15);
    z-index: calc(var(--workspace-z-index) - 1);
    transition: var(--workspace-transition);
}

/* Admin bar adjustments */
body.admin-bar .workspace-sidebar-frame {
    top: calc(var(--workspace-header-height) + 32px);
    height: calc(100vh - var(--workspace-header-height) - 32px);
}

body.admin-bar .workspace-header-bar {
    top: calc(var(--workspace-header-height) + 32px);
}

.workspace-header-content {
    flex: 1;
    display: flex;
    align-items: center;
    padding: 0 var(--wp--preset--spacing--60, 2rem);
}

/* Sidebar Toggle Button - P2 style */
.sidebar-toggle-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: var(--workspace-toggle-width);
    height: 100%;
    background: transparent;
    border: none;
    border-right: 1px solid var(--workspace-border-color);
    color: var(--workspace-header-text);
    cursor: pointer;
    transition: background 0.2s ease;
}

.sidebar-toggle-btn:hover {
    background: rgba(0, 0, 0, 0.05);
}

/* Sidebar Toggle Icon - Match Blocksy type-3 style */
.sidebar-toggle-btn .ct-icon {
    fill: var(--workspace-header-text);
}

.sidebar-toggle-btn .ct-icon[data-type] rect {
    transform-origin: 50% 50%;
    transition: 0.12s cubic-bezier(0.455, 0.03, 0.515, 0.955);
}

.sidebar-toggle-btn .ct-icon[data-type="type-3"] rect:nth-child(1),
.sidebar-toggle-btn .ct-icon[data-type="type-3"] rect:nth-child(3) {
    width: 12px;
}

.sidebar-toggle-btn:hover .ct-icon {
    fill: var(--wd-sidebar-accent, #0d9488);
}

/* Offcanvas sidebar state - using body class for reliable targeting */
body.sidebar-offcanvas .workspace-sidebar-frame {
    transform: translateX(-100%);
}

body.sidebar-offcanvas .workspace-header-bar {
    left: 0;
}

body.sidebar-offcanvas.has-workspace-sidebar .site-main {
    margin-left: 0;
}

/* Course/lesson pages - no side padding, only top padding for header bar */
body.single-lesson .site-main,
body.single-tutor_quiz .site-main,
body.single-tutor_assignments .site-main {
    padding: 0 !important;
    padding-top: var(--workspace-bar-height) !important;
    margin-left: var(--workspace-sidebar-width);
}

/* When sidebar is collapsed, remove left margin too */
body.sidebar-offcanvas.single-lesson .site-main,
body.sidebar-offcanvas.single-tutor_quiz .site-main,
body.sidebar-offcanvas.single-tutor_assignments .site-main {
    margin-left: 0;
}

.workspace-page-title {
    margin: 0;
    font-size: var(--wp--preset--font-size--lg, 20px);
    font-weight: 600;
    color: var(--workspace-header-text);
}

/* Header DateTime Display */
.workspace-header-datetime {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 0 24px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.header-date,
.header-time {
    font-size: 14px;
    font-weight: 500;
    color: var(--workspace-header-text);
}

.workspace-sidebar-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    background: var(--workspace-sidebar-bg);
}

#lrh-portal-sidebar-root,
#workspace-sidebar-root {
    background: var(--workspace-sidebar-bg);
    min-height: 100%;
}

/* Fix sidebar avatar size - prevent CSS bleed */
.workspace-sidebar-frame .ws-avatar {
    width: 42px !important;
    height: 42px !important;
    min-width: 42px;
    max-width: 42px;
}

.workspace-sidebar-frame .ws-avatar-img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
}

body.has-workspace-sidebar .site-main {
    margin-left: var(--workspace-sidebar-width);
    margin-top: 0;
    padding: var(--wp--preset--spacing--60, 2rem);
    padding-top: calc(var(--workspace-bar-height) + var(--wp--preset--spacing--20, 0.5rem));
    min-height: calc(100vh - var(--workspace-header-height) - var(--workspace-bar-height));
    transition: var(--workspace-transition);
}

body.has-workspace-sidebar footer,
body.has-workspace-sidebar .site-footer {
    margin-left: var(--workspace-sidebar-width);
    transition: var(--workspace-transition);
}

body.has-workspace-sidebar #primary {
    margin-left: 0;
}

/* Collapsed sidebar state */
body.has-workspace-sidebar.sidebar-collapsed .workspace-sidebar-frame {
    width: var(--workspace-sidebar-width-collapsed);
}

body.has-workspace-sidebar.sidebar-collapsed .workspace-header-bar {
    left: var(--workspace-sidebar-width-collapsed);
}

body.has-workspace-sidebar.sidebar-collapsed .site-main {
    margin-left: var(--workspace-sidebar-width-collapsed);
}

body.has-workspace-sidebar.sidebar-collapsed footer,
body.has-workspace-sidebar.sidebar-collapsed .site-footer {
    margin-left: var(--workspace-sidebar-width-collapsed);
}

/* Hidden title bar state */
body.has-workspace-sidebar.hide-titlebar .site-main {
    padding-top: var(--wp--preset--spacing--20, 0.5rem);
}

/* Mobile Offcanvas Panel Styles */
#workspace-sidebar-panel {
    display: none;
}

#workspace-sidebar-panel .ct-panel-inner {
    background: var(--workspace-sidebar-bg);
    width: 100%;
    max-width: 320px;
    height: 100%;
    display: flex;
    flex-direction: column;
}

#workspace-sidebar-panel .ct-panel-actions {
    display: flex;
    justify-content: flex-end;
    padding: 16px;
    background: var(--workspace-sidebar-bg);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

#workspace-sidebar-panel .ct-toggle-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.2s ease;
}

#workspace-sidebar-panel .ct-toggle-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

#workspace-sidebar-panel .ct-toggle-close .ct-icon {
    fill: #ffffff;
    width: 12px;
    height: 12px;
}

#workspace-sidebar-panel .ct-panel-content {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    background: var(--workspace-sidebar-bg);
}

/* Fix sidebar avatar size in offcanvas - prevent CSS bleed */
#workspace-sidebar-panel .ws-avatar {
    width: 42px !important;
    height: 42px !important;
    min-width: 42px;
    max-width: 42px;
}

#workspace-sidebar-panel .ws-avatar-img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover;
}

/* Mobile admin bar is taller (46px) below 783px */
@media (max-width: 782px) {
    body.admin-bar .workspace-sidebar-frame {
        top: calc(var(--workspace-header-height) + 46px);
        height: calc(100vh - var(--workspace-header-height) - 46px);
    }

    body.admin-bar .workspace-header-bar {
        top: calc(var(--workspace-header-height) + 46px);
    }
}

@media (max-width: 768px) {
    /* Hide desktop sidebar and header bar */
    .workspace-sidebar-desktop,
    .workspace-header-bar {
        display: none !important;
    }

    /* Show mobile offcanvas panel */
    #workspace-sidebar-panel {
        display: block;
    }

    body.has-workspace-sidebar .site-main {
        margin-left: 0;
        padding: 0;
        padding-top: 0;
    }

    body.has-workspace-sidebar footer,
    body.has-workspace-sidebar .site-footer {
        margin-left: 0;
    }
}

/* ── Inset Admin Look overrides ────────────────────────────────────────
   These must live here (after the fixed-positioning rules above) so they
   win without needing !important on every property.
   The wd-inset body class is toggled by PHP (wp_head script) + JS preview.
   ─────────────────────────────────────────────────────────────────────── */

/* Corner fillers: hidden by default */
.wd-corner { display: none; }

body.wd-inset {
    background-color: var(--wd-site-bg, #d0d8e8);
}

/* Title bar: top-left + top-right radius */
body.wd-inset .workspace-header-bar {
    border-top-left-radius: var(--wd-content-radius, 12px);
    border-top-right-radius: var(--wd-content-radius, 12px);
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
}

/* Site-main: right + bottom gap, bottom corners rounded */
body.wd-inset .site-main {
    margin-right: var(--wd-inset-padding, 8px);
    margin-bottom: var(--wd-inset-padding, 8px);
    border-bottom-left-radius: var(--wd-content-radius, 12px);
    border-bottom-right-radius: var(--wd-content-radius, 12px);
}

body.wd-inset .wd-corner {
    display: block;
    position: fixed;
    width: var(--wd-content-radius, 12px);
    height: var(--wd-content-radius, 12px);
    pointer-events: none;
    z-index: calc(var(--workspace-z-index, 100) + 1);
}

/* Bottom-right of title bar: sits at the junction between title bar bottom
   and content area — white, curving bottom-right into the content */
body.wd-inset .wd-corner--titlebar-br {
    top: calc(var(--workspace-header-height, 80px) + var(--workspace-bar-height, 60px));
    right: var(--wd-inset-padding, 8px);
    background: radial-gradient(
        circle at 100% 100%,
        transparent var(--wd-content-radius, 12px),
        white calc(var(--wd-content-radius, 12px) + 0.5px)
    );
}

/* Bottom-right of sidebar: directly below the title bar top-left corner.
   Sidebar color fills the square, quarter-circle punches toward top-right
   so the sidebar's right edge appears to curve into the content area. */
body.wd-inset .wd-corner--sidebar-br {
    bottom: 0;
    left: var(--workspace-sidebar-width);
    background: radial-gradient(
        circle at 0% 0%,
        transparent var(--wd-content-radius, 12px),
        var(--workspace-sidebar-bg, #020014) calc(var(--wd-content-radius, 12px) + 0.5px)
    );
}

body.wd-inset.admin-bar .wd-corner--titlebar-br {
    top: calc(var(--workspace-header-height, 80px) + 32px + var(--workspace-bar-height, 60px));
}

@media (max-width: 782px) {
    body.wd-inset.admin-bar .wd-corner--titlebar-br {
        top: calc(var(--workspace-header-height, 80px) + 46px + var(--workspace-bar-height, 60px));
    }
}

/* Footer hidden */
body.wd-inset footer,
body.wd-inset .site-footer {
    display: none;
}

/* Mobile: revert */
@media (max-width: 768px) {
    body.wd-inset .workspace-header-bar {
        border-radius: 0;
    }
    body.wd-inset .site-main {
        margin-right: 0;
        margin-bottom: 0;
        border-radius: 0;
    }
    body.wd-inset .wd-corner { display: none !important; }
}

/* Mobile: collapse back to normal flow */
@media (max-width: 768px) {
    body.wd-inset .workspace-frame-wrapper {
        position: static;
        border-radius: 0;
        box-shadow: none;
        overflow: visible;
    }
    body.wd-inset .workspace-sidebar-frame {
        position: fixed;
        top: var(--workspace-header-height, 0px);
        height: calc(100vh - var(--workspace-header-height, 0px));
        border-radius: 0;
    }
    body.wd-inset .workspace-header-bar {
        position: fixed;
        top: var(--workspace-header-height, 0px);
        left: var(--wd-sidebar-width, 320px);
        border-radius: 0;
    }
    body.wd-inset .site-main {
        position: static;
        overflow-y: visible;
        margin-left: 0;
        border-radius: 0;
    }
}
</style>

