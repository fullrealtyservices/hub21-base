/**
 * Customizer Live Preview
 *
 * Updates CSS variables in real-time as colors are changed in the Customizer.
 */
(function($) {
    'use strict';

    // Helper to convert hex to rgba
    function hexToRgba(hex, alpha) {
        hex = hex.replace('#', '');
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        var r = parseInt(hex.substring(0, 2), 16);
        var g = parseInt(hex.substring(2, 4), 16);
        var b = parseInt(hex.substring(4, 6), 16);
        return 'rgba(' + r + ', ' + g + ', ' + b + ', ' + alpha + ')';
    }

    // Update CSS variable
    function updateCssVar(varName, value) {
        document.documentElement.style.setProperty(varName, value);
    }

    // Sidebar Background
    wp.customize('sidebar_bg_color', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-sidebar-bg', newval);
        });
    });

    // Sidebar Text
    wp.customize('sidebar_text_color', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-sidebar-text', newval);
            updateCssVar('--wd-sidebar-text-muted', hexToRgba(newval, 0.7));
        });
    });

    // Sidebar Accent
    wp.customize('sidebar_accent_color', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-sidebar-accent', newval);
        });
    });

    // Header Bar Background
    wp.customize('header_bar_bg_color', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-header-bar-bg', newval);
        });
    });

    // Header Bar Text
    wp.customize('header_bar_text_color', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-header-bar-text', newval);
        });
    });

    // Border Color
    wp.customize('sidebar_border_color', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-border-color', newval);
        });
    });

    // User Popup Background
    wp.customize('user_popup_bg_color', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-user-popup-bg', newval);
        });
    });

    // Profile Gradient
    wp.customize('profile_gradient_start', function(value) {
        value.bind(function(newval) {
            var end = wp.customize('profile_gradient_end').get();
            updateCssVar('--wd-profile-gradient', 'linear-gradient(135deg, ' + newval + ' 0%, ' + end + ' 100%)');
        });
    });

    wp.customize('profile_gradient_end', function(value) {
        value.bind(function(newval) {
            var start = wp.customize('profile_gradient_start').get();
            updateCssVar('--wd-profile-gradient', 'linear-gradient(135deg, ' + start + ' 0%, ' + newval + ' 100%)');
        });
    });

    // === LAYOUT DIMENSIONS ===

    // Sidebar Width
    wp.customize('wd_sidebar_width', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-sidebar-width', newval + 'px');
        });
    });

    // Header Bar Height
    wp.customize('wd_header_bar_height', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-bar-height', newval + 'px');
        });
    });

    // Toggle Button Width
    wp.customize('wd_toggle_button_width', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-toggle-width', newval + 'px');
        });
    });

    // Profile Card Height
    wp.customize('wd_profile_card_height', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-profile-card-height', newval + 'px');
        });
    });

    // Widget Area Mode
    wp.customize('wd_widget_area_mode', function(value) {
        value.bind(function(newval) {
            var el = document.querySelector('.workspace-sidebar-header-widget');
            if (!el) return;

            el.style.aspectRatio = '';
            el.style.height = '';
            el.style.display = '';

            switch (newval) {
                case '16:9':
                    el.style.aspectRatio = '16/9';
                    break;
                case '4:3':
                    el.style.aspectRatio = '4/3';
                    break;
                case '1:1':
                    el.style.aspectRatio = '1/1';
                    break;
                case 'custom-height':
                    var customHeight = wp.customize('wd_widget_area_custom_height').get();
                    el.style.height = customHeight + 'px';
                    el.style.aspectRatio = 'auto';
                    break;
                case 'none':
                    el.style.aspectRatio = 'auto';
                    break;
            }
        });
    });

    // Widget Area Custom Height
    wp.customize('wd_widget_area_custom_height', function(value) {
        value.bind(function(newval) {
            var mode = wp.customize('wd_widget_area_mode').get();
            if (mode !== 'custom-height') return;

            var el = document.querySelector('.workspace-sidebar-header-widget');
            if (el) {
                el.style.height = newval + 'px';
                el.style.aspectRatio = 'auto';
            }
        });
    });

    // === INSET ADMIN LOOK ===

    // Enable/disable inset mode — toggles wd-inset body class
    wp.customize('wd_inset_mode', function(value) {
        value.bind(function(newval) {
            if (newval) {
                document.body.classList.add('wd-inset');
                document.body.style.backgroundColor = wp.customize('wd_site_bg_color').get();
            } else {
                document.body.classList.remove('wd-inset');
                document.body.style.backgroundColor = '';
            }
        });
    });

    // Site background color
    wp.customize('wd_site_bg_color', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-site-bg', newval);
            if (document.body.classList.contains('wd-inset')) {
                document.body.style.backgroundColor = newval;
            }
        });
    });

    // Content area border radius
    wp.customize('wd_content_border_radius', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-content-radius', newval + 'px');
        });
    });

    // Inset padding
    wp.customize('wd_inset_padding', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-inset-padding', newval + 'px');
        });
    });

    // === BRAND CYAN ACCESSIBILITY ===

    // Brand Cyan — Light Mode
    wp.customize('brand_cyan_light', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-brand-cyan', newval);
            updateCssVar('--theme-palette-color-1', newval);
            updateCssVar('--21c-rich-teal', newval);
        });
    });

    // Brand Cyan Hover — Light Mode
    wp.customize('brand_cyan_light_hover', function(value) {
        value.bind(function(newval) {
            updateCssVar('--wd-brand-cyan-hover', newval);
            updateCssVar('--theme-palette-color-2', newval);
        });
    });

    // Brand Cyan — Dark Mode (only applies when data-color-mode*=dark)
    // postMessage preview updates :root vars; the correct dark-mode
    // value will be applied on page refresh since dark-mode uses a
    // scoped selector ([data-color-mode*="dark"]) that we cannot target
    // via documentElement.style.setProperty. Marking transport=postMessage
    // still gives instant feedback for the light-mode portion.
    wp.customize('brand_cyan_dark', function(value) {
        value.bind(function(newval) {
            // Update the dark-mode preview if currently in dark mode
            var colorMode = document.documentElement.getAttribute('data-color-mode') || '';
            if (colorMode.indexOf('dark') !== -1) {
                updateCssVar('--wd-brand-cyan', newval);
                updateCssVar('--theme-palette-color-1', newval);
                updateCssVar('--21c-rich-teal', newval);
            }
        });
    });

    // Brand Cyan Hover — Dark Mode
    wp.customize('brand_cyan_dark_hover', function(value) {
        value.bind(function(newval) {
            var colorMode = document.documentElement.getAttribute('data-color-mode') || '';
            if (colorMode.indexOf('dark') !== -1) {
                updateCssVar('--wd-brand-cyan-hover', newval);
                updateCssVar('--theme-palette-color-2', newval);
            }
        });
    });

    // === COMPONENT VISIBILITY ===

    function toggleComponentVisibility(selector, visible) {
        var el = document.querySelector(selector);
        if (el) {
            el.style.display = visible ? '' : 'none';
        }
    }

    wp.customize('wd_show_header_widget', function(value) {
        value.bind(function(newval) {
            toggleComponentVisibility('.workspace-sidebar-header-widget', newval);
        });
    });

    wp.customize('wd_show_nav_menu', function(value) {
        value.bind(function(newval) {
            toggleComponentVisibility('#workspace-nav', newval);
        });
    });

    wp.customize('wd_show_profile_completion', function(value) {
        value.bind(function(newval) {
            toggleComponentVisibility('.workspace-profile-completion', newval);
        });
    });

    wp.customize('wd_show_user_profile_card', function(value) {
        value.bind(function(newval) {
            toggleComponentVisibility('.user-menu-wrapper', newval);
        });
    });

    wp.customize('wd_show_datetime_display', function(value) {
        value.bind(function(newval) {
            toggleComponentVisibility('.workspace-header-datetime', newval);
        });
    });

})(jQuery);
