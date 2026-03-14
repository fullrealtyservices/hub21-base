<?php
/**
 * Workspace Sidebar Content
 * PHP + vanilla JS version with all React design elements preserved
 *
 * @package Workspaces_Directory_Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current context using theme's flexible menu system
$sidebar_menu_location = hub21_get_menu_location();
$has_sidebar_menu = $sidebar_menu_location && has_nav_menu($sidebar_menu_location);
$current_term = hub21_get_current_term();

$current_user = wp_get_current_user();

// Get profile data directly from model (not via API to avoid loopback)
$profile_data = null;
if ($current_user->ID > 0 && class_exists('FRSUsers\Models\Profile')) {
    $profile = \FRSUsers\Models\Profile::get_by_user_id($current_user->ID);
    if ($profile) {
        $profile_data = (array) $profile;
    }
}

// Set user data - prefer API data, fallback to WordPress data
if ($profile_data) {
    $user_avatar = $profile_data['profile_photo'] ?? get_avatar_url($current_user->ID, ['size' => 200]);
    $user_name = trim(($profile_data['first_name'] ?? '') . ' ' . ($profile_data['last_name'] ?? '')) ?: $current_user->display_name;
    $user_job_title = $profile_data['job_title'] ?? 'Team Member';
} else {
    $user_avatar = get_avatar_url($current_user->ID, ['size' => 200]);
    $user_name = $current_user->display_name;
    $user_job_title = get_user_meta($current_user->ID, 'job_title', true) ?: 'Team Member';
}

// Get gradient background URL - use uploads folder for cross-environment compatibility
$gradient_url = content_url('/uploads/2025/08/Blue-Dark-Blue-Gradient-Color-and-Style-Video-Background-2.gif');

// Calculate profile completion percentage using UserProfile model
$profile_completion = 0;
$completed_fields = 0;
$total_fields = 0;
$completion_items = array();
$needs_onboarding = false;

if ($current_user->ID > 0 && class_exists('FRSUsers\Models\UserProfile')) {
    $user_profile = \FRSUsers\Models\UserProfile::find($current_user->ID);
    if ($user_profile) {
        $items = $user_profile->get_profile_completion_items();
        $completion_items = $items;
        $total_fields = count($items);
        $completed_fields = count(array_filter($items, fn($i) => $i['is_completed']));
        if ($total_fields > 0) {
            $profile_completion = round(($completed_fields / $total_fields) * 100);
        }
    }
}

// Check if onboarding plugin is active and user needs onboarding
if (class_exists('FRSLendingOnboarding\OnboardingWizard')) {
    $needs_onboarding = \FRSLendingOnboarding\OnboardingWizard::needs_onboarding($current_user->ID);
}

// Get user role
$user_roles = $current_user->roles;
$role = '';
if (in_array('loan_officer', $user_roles)) {
    $role = 'loan_officer';
} elseif (in_array('realtor_partner', $user_roles)) {
    $role = 'realtor';
} elseif (in_array('administrator', $user_roles)) {
    $role = 'admin';
}
?>

<div id="workspace-sidebar-root" class="scrollbar-hide flex flex-col" style="height: calc(100dvh - var(--workspace-bar-height, 60px)); background-color: var(--wd-sidebar-bg, #020014);">

    <?php if (get_theme_mod('wd_show_header_widget', true)) : ?>
    <!-- Header Widget Area -->
    <div class="workspace-sidebar-header-widget">
        <?php if (is_active_sidebar('workspace-sidebar-header')) : ?>
            <?php dynamic_sidebar('workspace-sidebar-header'); ?>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Sidebar content - split into scrollable menu and fixed bottom widgets -->
    <div class="flex-1 overflow-y-auto scrollbar-hide flex flex-col">

        <?php if (get_theme_mod('wd_show_nav_menu', true)) : ?>
        <!-- Menu items - take available space -->
        <nav class="flex flex-col flex-1 pt-4" id="workspace-nav">
            <?php
            // Special handling for Learning context and Tutor LMS pages - use Tutor dashboard sections
            $is_learning_term = ($current_term && $current_term->slug === 'learning');
            $is_tutor_page = (
                is_singular('courses') ||
                is_singular('lesson') ||
                is_singular('tutor_quiz') ||
                is_singular('tutor_assignments') ||
                is_post_type_archive('courses')
            );
            $show_learning_menu = ($is_learning_term || $is_tutor_page);

            if ($show_learning_menu && class_exists('Workspaces_Tutor_Dashboard')) :
                $tutor_dashboard = Workspaces_Tutor_Dashboard::instance();
                $sections = $tutor_dashboard->get_dashboard_sections();
                $dashboard_url = $current_term ? home_url('/' . $current_term->slug . '/') : home_url('/learning/');
                $current_hash = '';

                // Get current section from URL hash (for active state)
                if (isset($_SERVER['REQUEST_URI'])) {
                    $uri_parts = parse_url($_SERVER['REQUEST_URI']);
                    if (isset($uri_parts['fragment'])) {
                        $current_hash = $uri_parts['fragment'];
                    }
                }

                // Map Tutor icons to Lucide icons
                $tutor_to_lucide = array(
                    'tutor-icon-dashboard'      => 'layout-dashboard',
                    'tutor-icon-user-bold'      => 'user',
                    'tutor-icon-mortarboard-o'  => 'book',
                    'tutor-icon-star-bold'      => 'star',
                    'tutor-icon-quiz-attempt'   => 'clipboard',
                    'tutor-icon-question'       => 'help-circle',
                    'tutor-icon-bookmark-bold'  => 'bookmark',
                    'tutor-icon-cart-bold'      => 'shopping-cart',
                    'tutor-icon-rocket'         => 'zap',
                    'tutor-icon-bullhorn'       => 'bell',
                    'tutor-icon-wallet'         => 'credit-card',
                    'tutor-icon-quiz-o'         => 'clipboard',
                    'tutor-icon-assignment'     => 'file-text',
                    'tutor-icon-brand-zoom'     => 'video',
                    'tutor-icon-chart-pie'      => 'pie-chart',
                    'tutor-icon-gear'           => 'settings',
                );

                // Check if user is admin or instructor
                $is_admin = current_user_can('manage_options');
                $show_instructor_menu = $sections['is_instructor'] || $is_admin;

                // Get custom menu items from Tutor integration
                $custom_menu_items = array();
                if (class_exists('Workspaces_Tutor_Integration')) {
                    $tutor_integration = Workspaces_Tutor_Integration::instance();
                    $custom_menu_items = $tutor_integration->get_learning_custom_menu_items();
                }

                // Render custom menu items with position 'before'
                foreach ($custom_menu_items as $item) :
                    if (($item['position'] ?? 'before') !== 'before') continue;
                    $item_icon = $item['icon'] ?? 'link';
                    $item_icon_html = class_exists('Lucide_Icons') ? Lucide_Icons::render($item_icon, 20) : '';
                    $item_target = !empty($item['target']) ? $item['target'] : '';
                ?>
                    <a href="<?php echo esc_url($item['url']); ?>"
                       class="flex items-center gap-2 px-4 py-3 text-white/70 hover:text-white transition-colors frs-nav-link"
                       <?php echo $item_target ? 'target="' . esc_attr($item_target) . '"' : ''; ?>>
                        <?php echo $item_icon_html; ?>
                        <span><?php echo esc_html($item['title']); ?></span>
                    </a>
                <?php endforeach; ?>

                <?php
                // Student sections
                foreach ($sections['student'] as $key => $section) :
                    $href = $dashboard_url . '#' . $section['key'];
                    $is_active = ($current_hash === $section['key']) || ($current_hash === '' && $section['key'] === 'dashboard');
                    $lucide_icon = isset($tutor_to_lucide[$section['icon']]) ? $tutor_to_lucide[$section['icon']] : 'circle';
                    $icon_html = class_exists('Lucide_Icons') ? Lucide_Icons::render($lucide_icon, 20) : '';
                ?>
                    <a href="<?php echo esc_url($href); ?>"
                       class="flex items-center gap-2 px-4 py-3 text-white/70 hover:text-white transition-colors frs-nav-link<?php echo $is_active ? ' active' : ''; ?>"
                       data-section="<?php echo esc_attr($section['key']); ?>">
                        <?php echo $icon_html; ?>
                        <span><?php echo esc_html($section['title']); ?></span>
                    </a>
                <?php endforeach; ?>

                <?php if ($show_instructor_menu && !empty($sections['instructor'])) : ?>
                    <!-- Instructor Dropdown -->
                    <div class="instructor-dropdown-wrapper" onclick="toggleInstructorMenu(event)">
                        <div class="flex items-center cursor-pointer">
                            <span class="flex items-center gap-2 px-4 py-3 text-white/70 hover:text-white transition-colors frs-nav-link flex-1">
                                <?php echo class_exists('Lucide_Icons') ? Lucide_Icons::render('briefcase', 20) : ''; ?>
                                <span><?php esc_html_e('Instructor', 'hub21-base'); ?></span>
                            </span>
                            <span class="px-3 py-3 text-white/70 hover:text-white transition-colors">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" id="instructor-chevron" style="transition: transform 0.2s ease-in-out;"><path d="m9 18 6-6-6-6"/></svg>
                            </span>
                        </div>
                    </div>
                    <div id="menu-instructor" class="frs-submenu" style="display: none;">
                        <?php foreach ($sections['instructor'] as $key => $section) :
                            $href = $dashboard_url . '#' . $section['key'];
                            $is_active = $current_hash === $section['key'];
                            $lucide_icon = isset($tutor_to_lucide[$section['icon']]) ? $tutor_to_lucide[$section['icon']] : 'circle';
                            $icon_html = class_exists('Lucide_Icons') ? Lucide_Icons::render($lucide_icon, 20) : '';
                        ?>
                            <a href="<?php echo esc_url($href); ?>"
                               class="flex items-center gap-2 pl-8 pr-4 py-2 text-sm text-white/60 hover:text-white transition-colors frs-nav-link<?php echo $is_active ? ' active' : ''; ?>"
                               data-section="<?php echo esc_attr($section['key']); ?>">
                                <?php echo $icon_html; ?>
                                <span><?php echo esc_html($section['title']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php
                // Render custom menu items with position 'after'
                foreach ($custom_menu_items as $item) :
                    if (($item['position'] ?? 'before') !== 'after') continue;
                    $item_icon = $item['icon'] ?? 'link';
                    $item_icon_html = class_exists('Lucide_Icons') ? Lucide_Icons::render($item_icon, 20) : '';
                    $item_target = !empty($item['target']) ? $item['target'] : '';
                ?>
                    <a href="<?php echo esc_url($item['url']); ?>"
                       class="flex items-center gap-2 px-4 py-3 text-white/70 hover:text-white transition-colors frs-nav-link"
                       <?php echo $item_target ? 'target="' . esc_attr($item_target) . '"' : ''; ?>>
                        <?php echo $item_icon_html; ?>
                        <span><?php echo esc_html($item['title']); ?></span>
                    </a>
                <?php endforeach; ?>

            <?php else : ?>
                <?php
                // Show nav menu from taxonomy-based location or default
                if ($has_sidebar_menu) :
                    wp_nav_menu(array(
                        'theme_location' => $sidebar_menu_location,
                        'container'      => false,
                        'items_wrap'     => '%3$s',
                        'walker'         => new Workspace_Nav_Walker(),
                        'fallback_cb'    => false,
                    ));
                elseif ($current_term) : ?>
                    <div class="px-4 py-3 text-xs text-white/50">
                        <?php
                        printf(
                            __('No menu assigned to "%s". Go to Appearance → Menus and assign a menu to "Workspace: %s"', 'hub21-base'),
                            esc_html($current_term->name),
                            esc_html($current_term->name)
                        );
                        ?>
                    </div>
                <?php else : ?>
                    <div class="px-4 py-3 text-xs text-white/50">
                        <?php _e('No workspace detected for this page.', 'hub21-base'); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
        <?php endif; ?>

        <!-- Bottom widgets - stick to bottom -->
        <div class="mt-auto">
            <?php if (get_theme_mod('wd_show_profile_completion', true)) : ?>
            <!-- Profile Completion Widget -->
            <div class="workspace-profile-completion relative overflow-hidden" style="min-height: var(--workspace-profile-card-height, 80px); background: <?php echo $gradient_url ? 'transparent' : 'var(--wd-profile-gradient, linear-gradient(135deg, #2563eb 0%, #2dd4da 100%))'; ?>;">
                <?php if ($gradient_url): ?>
                <!-- Video Background -->
                <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover" style="z-index: 0;">
                    <source src="<?php echo esc_url($gradient_url); ?>" type="video/mp4">
                </video>
                <!-- Glassy overlay -->
                <div class="absolute inset-0" style="z-index: 1; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);"></div>
                <?php endif; ?>

                <div class="profile-completion-content relative px-4 py-3" style="z-index: 10;">
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-semibold text-white uppercase tracking-wider drop-shadow-md">Profile Completion</div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-semibold text-white drop-shadow-md"><?php echo esc_html($profile_completion); ?>%</span>
                            <?php if ($profile_completion >= 100) : ?>
                            <button onclick="localStorage.setItem('hideProfileCompletion','1');this.closest('.profile-completion-content').style.display='none'" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;padding:0;font-size:16px;line-height:1;">&times;</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="w-full bg-white/30 rounded-full h-2">
                        <div class="h-2 rounded-full bg-white" style="width: <?php echo esc_attr($profile_completion); ?>%;"></div>
                    </div>
                    <?php if (!empty($completion_items)) : ?>
                    <ul style="margin:8px 0 0;padding:0;list-style:none;font-size:11px;">
                        <?php foreach ($completion_items as $item) : ?>
                        <li style="color:#fff;margin-bottom:2px;"><span style="color:<?php echo $item['is_completed'] ? '#2dd4bf' : 'rgba(255,255,255,0.5)'; ?>"><?php echo $item['is_completed'] ? '●' : '○'; ?></span> <?php echo esc_html($item['title']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                    <?php if ($needs_onboarding) : ?>
                    <button
                        data-wp-on--click="actions.openModal"
                        data-wp-interactive="frs-users/onboarding"
                        class="mt-3 w-full text-center text-xs font-semibold text-white bg-white/20 hover:bg-white/30 rounded py-2 px-3 transition-colors cursor-pointer border-0"
                        style="backdrop-filter: blur(4px); -webkit-backdrop-filter: blur(4px); border-radius: 5px;"
                    >
                        Continue Setup &#9654;
                    </button>
                    <?php endif; ?>
                </div>
                <script>if(localStorage.getItem('hideProfileCompletion'))document.querySelector('.profile-completion-content').style.display='none';</script>
            </div>
            <?php endif; ?>

            <!-- Profile Header with Avatar (Horizontal Layout) - Bottom -->
            <?php if (!get_theme_mod('wd_show_user_profile_card', true)) : ?>
                <div style="height: var(--workspace-profile-card-height, 80px); background-color: var(--wd-sidebar-bg, #020014);"></div>
            <?php else : ?>
            <div class="user-menu-wrapper relative w-full">
                <!-- User Menu Trigger -->
                <button
                    type="button"
                    onclick="toggleUserMenu(event)"
                    class="user-menu-trigger relative w-full px-4 flex items-center gap-3 cursor-pointer border-0 text-left transition-all hover:bg-white/5"
                    style="height: var(--workspace-profile-card-height, 80px); background-color: var(--wd-sidebar-bg, #020014);"
                    aria-expanded="false"
                    aria-haspopup="true"
                >
                    <!-- Glassy overlay -->
                    <div class="absolute inset-0" style="z-index: 1; background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(2px); -webkit-backdrop-filter: blur(2px);"></div>

                    <!-- Avatar -->
                    <div class="relative flex-shrink-0" style="z-index: 10;">
                        <div class="w-[42px] h-[42px] rounded-full overflow-hidden shadow-lg border-2 border-white/20">
                            <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($user_name); ?>" class="w-full h-full object-cover">
                        </div>
                    </div>

                    <!-- Name and Title -->
                    <div class="relative flex-1 min-w-0" style="z-index: 10;">
                        <h3 class="font-bold text-white text-base mb-0.5 truncate"><?php echo esc_html($user_name); ?></h3>
                        <p class="font-normal text-white/70 text-sm truncate"><?php echo esc_html($user_job_title); ?></p>
                    </div>

                    <!-- Chevron Icon -->
                    <div class="relative flex-shrink-0" style="z-index: 10;">
                        <svg id="user-menu-chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white/70 transition-transform duration-200">
                            <path d="m18 15-6-6-6 6"/>
                        </svg>
                    </div>
                </button>

                <!-- User Popup Menu (fixed positioning to escape overflow container) -->
                <div id="user-popup-menu" class="user-popup-menu fixed rounded-lg overflow-hidden shadow-xl" style="display: none; background: var(--wd-user-popup-bg, #1a1f3c); border: 1px solid rgba(255,255,255,0.1); width: 288px; z-index: 9999;">
                    <?php if (has_nav_menu('user_menu')) : ?>
                        <nav class="user-menu-nav py-2">
                            <?php
                            wp_nav_menu(array(
                                'theme_location' => 'user_menu',
                                'container'      => false,
                                'items_wrap'     => '%3$s',
                                'walker'         => new User_Menu_Walker(),
                                'fallback_cb'    => false,
                            ));
                            ?>
                        </nav>
                        <div class="border-t border-white/10"></div>
                    <?php endif; ?>

                    <?php if (is_user_logged_in()) : ?>
                        <!-- Settings link for logged-in users -->
                        <div class="py-2">
                            <?php
                            $settings_url = $current_term
                                ? home_url('/' . $current_term->slug . '/settings/')
                                : home_url('/settings/');
                            ?>
                            <a href="<?php echo esc_url($settings_url); ?>" class="flex items-center gap-3 px-4 py-2.5 text-white/70 hover:text-white hover:bg-white/5 transition-colors">
                                <?php echo class_exists('Lucide_Icons') ? Lucide_Icons::render('settings', 18) : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>'; ?>
                                <span class="text-sm"><?php esc_html_e('Settings', 'hub21-base'); ?></span>
                            </a>
                        </div>
                        <div class="border-t border-white/10"></div>
                        <!-- Logout link -->
                        <div class="py-2">
                            <a href="<?php echo esc_url(wp_logout_url(home_url())); ?>" class="flex items-center gap-3 px-4 py-2.5 text-white/70 hover:text-white hover:bg-white/5 transition-colors">
                                <?php echo class_exists('Lucide_Icons') ? Lucide_Icons::render('log-out', 18) : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>'; ?>
                                <span class="text-sm"><?php esc_html_e('Log out', 'hub21-base'); ?></span>
                            </a>
                        </div>
                    <?php else : ?>
                        <!-- Login link for logged-out users - triggers Blocksy modal -->
                        <div class="py-2">
                            <a href="#account-modal" data-toggle-panel="account-modal" class="flex items-center gap-3 px-4 py-2.5 text-white/70 hover:text-white hover:bg-white/5 transition-colors">
                                <?php echo class_exists('Lucide_Icons') ? Lucide_Icons::render('log-in', 18) : '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/></svg>'; ?>
                                <span class="text-sm"><?php esc_html_e('Log in', 'hub21-base'); ?></span>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<script>
// Section accordion — only one open at a time, with slide animation
window.switchSection = function(sectionId, event) {
    event.preventDefault();
    event.stopPropagation();

    var target = document.getElementById(sectionId);
    if (!target) return;

    // Already open? Do nothing.
    if (target.style.display === 'block') return;

    // Close current open section (slide up)
    var allBodies = document.querySelectorAll('.frs-section-body');
    var allHeaders = document.querySelectorAll('.frs-section-header');

    allHeaders.forEach(function(h) { h.classList.remove('is-open'); });

    allBodies.forEach(function(body) {
        if (body.id !== sectionId && body.style.display !== 'none') {
            slideUp(body);
        }
    });

    // Open target section (slide down)
    slideDown(target);
    event.currentTarget.classList.add('is-open');

    // Persist
    try { localStorage.setItem('frs_active_section', sectionId); } catch(e) {}
};

function slideDown(el) {
    el.style.display = 'block';
    el.style.overflow = 'hidden';
    var h = el.scrollHeight;
    el.style.maxHeight = '0px';
    el.offsetHeight; // reflow
    el.style.transition = 'max-height 0.25s ease-out';
    el.style.maxHeight = h + 'px';
    var done = function() {
        el.style.maxHeight = '';
        el.style.overflow = '';
        el.style.transition = '';
        el.removeEventListener('transitionend', done);
    };
    el.addEventListener('transitionend', done);
}

function slideUp(el) {
    el.style.overflow = 'hidden';
    el.style.maxHeight = el.scrollHeight + 'px';
    el.offsetHeight; // reflow
    el.style.transition = 'max-height 0.2s ease-in';
    el.style.maxHeight = '0px';
    var done = function() {
        el.style.display = 'none';
        el.style.maxHeight = '';
        el.style.overflow = '';
        el.style.transition = '';
        el.removeEventListener('transitionend', done);
    };
    el.addEventListener('transitionend', done);
}

// On load: restore saved section or auto-open section with active link
function initSections() {
    var saved = null;
    try { saved = localStorage.getItem('frs_active_section'); } catch(e) {}

    // Check if active link is inside a section
    var activeLink = document.querySelector('.frs-section-body .frs-nav-link.active');
    if (activeLink) {
        var parentSection = activeLink.closest('.frs-section-body');
        if (parentSection) saved = parentSection.id;
    }

    var allBodies = document.querySelectorAll('.frs-section-body');
    var allHeaders = document.querySelectorAll('.frs-section-header');

    if (!saved && allBodies.length > 0) {
        saved = allBodies[0].id; // default to first
    }

    allBodies.forEach(function(body) {
        body.style.display = (body.id === saved) ? 'block' : 'none';
    });

    allHeaders.forEach(function(header) {
        header.classList.toggle('is-open', header.getAttribute('data-section') === saved);
    });

    try { localStorage.setItem('frs_active_section', saved); } catch(e) {}
}

initSections();

// Toggle workspace section visibility (for submenu display type)
window.toggleSection = function(sectionId) {
    const section = document.getElementById(sectionId);
    const chevron = document.getElementById('chevron-' + sectionId);
    
    if (!section) return;
    
    const isHidden = section.style.display === 'none' || section.style.display === '';
    
    if (isHidden) {
        section.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(90deg)';
    } else {
        section.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
};

// Toggle instructor submenu visibility - attached to window for global access
window.toggleInstructorMenu = function(event) {
    event.preventDefault();
    event.stopPropagation();

    const submenu = document.getElementById('menu-instructor');
    const chevron = document.getElementById('instructor-chevron');

    if (!submenu) return;

    const isHidden = submenu.style.display === 'none' || submenu.style.display === '';

    if (isHidden) {
        submenu.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(90deg)';
    } else {
        submenu.style.display = 'none';
        if (chevron) chevron.style.transform = 'rotate(0deg)';
    }
};

// Toggle user popup menu visibility
window.toggleUserMenu = function(event) {
    event.preventDefault();
    event.stopPropagation();

    const menu = document.getElementById('user-popup-menu');
    const chevron = document.getElementById('user-menu-chevron');
    const trigger = event.currentTarget;

    if (!menu) return;

    const isHidden = menu.style.display === 'none' || menu.style.display === '';

    if (isHidden) {
        // Position the fixed menu above the trigger button
        const triggerRect = trigger.getBoundingClientRect();
        menu.style.left = triggerRect.left + 'px';
        menu.style.bottom = (window.innerHeight - triggerRect.top + 4) + 'px';
        menu.style.display = 'block';
        if (chevron) chevron.style.transform = 'rotate(180deg)';
        if (trigger) trigger.setAttribute('aria-expanded', 'true');

        // Close menu when clicking outside
        setTimeout(() => {
            document.addEventListener('click', closeUserMenuOnClickOutside);
        }, 0);
    } else {
        closeUserMenu();
    }
};

// Close user menu helper
function closeUserMenu() {
    const menu = document.getElementById('user-popup-menu');
    const chevron = document.getElementById('user-menu-chevron');
    const trigger = document.querySelector('.user-menu-trigger');

    if (menu) menu.style.display = 'none';
    if (chevron) chevron.style.transform = 'rotate(0deg)';
    if (trigger) trigger.setAttribute('aria-expanded', 'false');

    document.removeEventListener('click', closeUserMenuOnClickOutside);
}

// Close menu when clicking outside
function closeUserMenuOnClickOutside(event) {
    const wrapper = document.querySelector('.user-menu-wrapper');
    if (wrapper && !wrapper.contains(event.target)) {
        closeUserMenu();
    }
}

// Close menu on escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeUserMenu();
    }
});

// Set active link based on current URL
function setActiveLink() {
    const currentPath = window.location.pathname;
    const links = document.querySelectorAll('.frs-nav-link');

    links.forEach(link => {
        const href = link.getAttribute('href');
        const linkPath = new URL(href, window.location.origin).pathname;

        if (currentPath === linkPath || currentPath === linkPath + '/') {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
        }
    });
}

// Initialize on load
setActiveLink();

// Update active link on navigation (for browser back/forward)
window.addEventListener('popstate', setActiveLink);

// Listen for Interactivity API navigation events
document.addEventListener('wp-router-navigated', function() {
    setActiveLink();
    initSections();
});

// Add click listener for instructor dropdown (runs immediately since DOM is already loaded)
(function() {
    const instructorWrapper = document.querySelector('.instructor-dropdown-wrapper');
    if (instructorWrapper) {
        instructorWrapper.addEventListener('click', function(e) {
            window.toggleInstructorMenu(e);
        });
    }
})();

// Handle login link click to trigger Blocksy account modal
(function() {
    const loginLink = document.querySelector('a[data-toggle-panel="account-modal"]');
    if (loginLink) {
        loginLink.addEventListener('click', function(e) {
            e.preventDefault();

            // Close the user popup menu first
            closeUserMenu();

            // Trigger Blocksy's panel system if available
            if (window.ctEvents) {
                const modal = document.getElementById('account-modal');
                if (modal) {
                    window.ctEvents.trigger('ct:overlay:handle-click', {
                        options: { container: modal }
                    });
                }
            }
        });
    }
})();
</script>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hubot+Sans:wght@400;500;600;700&family=Mona+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* Workspace Sidebar Header Widget */
.workspace-sidebar-header-widget {
    width: 100%;
    flex-shrink: 0;
    overflow: hidden;
}

.workspace-sidebar-header-widget .widget {
    margin: 0;
    padding: 0;
    height: 100%;
    width: 100%;
}

.workspace-sidebar-header-widget .widget > * {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
    border-radius: 0 !important;
}

.workspace-sidebar-header-widget .wp-block-cover {
    min-height: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
    border-radius: 0 !important;
}

.workspace-sidebar-header-widget .wp-block-cover__inner-container {
    padding: 1rem;
}

/* Nav links — Mona Sans */
.frs-nav-link {
    font-family: 'Mona Sans', sans-serif;
    font-weight: 400;
    color: rgba(255, 255, 255, 0.65);
    transition: background 0.2s ease, color 0.2s ease;
    text-decoration: none;
    border-radius: 4px;
    margin: 1px 8px;
}
.frs-nav-link:hover {
    background: rgba(255, 255, 255, 0.06);
    color: rgba(255, 255, 255, 0.95);
    text-decoration: none;
}
.frs-nav-link.active {
    background: rgba(255, 255, 255, 0.1);
    color: white !important;
    font-weight: 500;
}

/* Section header: TITLE ──── ▾  — Hubot Sans, all caps, #37DAE6 */
.frs-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 20px 16px 8px;
    cursor: pointer;
    user-select: none;
    background: none !important;
    background-color: transparent !important;
}
.frs-section-header:hover .frs-section-title {
    color: rgba(55, 218, 230, 0.85);
}
.frs-section-header:hover .frs-section-line {
    background: rgba(55, 218, 230, 0.25);
}
.frs-section-header:hover .frs-section-chevron {
    color: rgba(55, 218, 230, 0.7);
}
.frs-section-title {
    font-family: 'Hubot Sans', sans-serif;
    font-size: 0.8125rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(55, 218, 230, 0.55);
    white-space: nowrap;
    transition: color 0.2s ease;
}
.frs-section-line {
    flex: 1;
    height: 1px;
    background: rgba(55, 218, 230, 0.12);
}
.frs-section-chevron {
    flex-shrink: 0;
    color: rgba(55, 218, 230, 0.35);
    transition: transform 0.25s ease, color 0.2s ease;
}
.frs-section-header.is-open .frs-section-title {
    color: rgba(55, 218, 230, 0.7);
}
.frs-section-header.is-open .frs-section-chevron {
    transform: rotate(0deg);
    color: rgba(55, 218, 230, 0.55);
}
.frs-section-header:not(.is-open) .frs-section-chevron {
    transform: rotate(-90deg);
}

/* Instructor dropdown wrapper */
.instructor-dropdown-wrapper {
    cursor: pointer;
}

.instructor-dropdown-wrapper:hover .frs-nav-link {
    color: white !important;
}

/* User menu wrapper */
.user-menu-wrapper {
    position: relative;
}

/* User menu trigger button */
.user-menu-trigger {
    width: 100%;
    font-family: inherit;
}

.user-menu-trigger:focus {
    outline: none;
}

.user-menu-trigger:focus-visible {
    outline: 2px solid rgba(255, 255, 255, 0.3);
    outline-offset: -2px;
}

/* User popup menu */
.user-popup-menu {
    z-index: 100;
    animation: slideUp 0.15s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-popup-menu a {
    text-decoration: none;
}

.user-popup-menu a:hover {
    text-decoration: none;
}

/* CSS View Transitions */
@supports (view-transition-name: none) {
    ::view-transition-old(root),
    ::view-transition-new(root) {
        animation-duration: 0.3s;
    }

    ::view-transition-old(root) {
        animation-name: fade-out;
    }

    ::view-transition-new(root) {
        animation-name: fade-in;
    }

    @keyframes fade-out {
        to {
            opacity: 0;
        }
    }

    @keyframes fade-in {
        from {
            opacity: 0;
        }
    }
}
</style>
