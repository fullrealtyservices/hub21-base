/**
 * Workspace Icon Picker
 * Modal-based icon picker with search, categories, SVG previews, and remote download
 */
(function($) {
    'use strict';

    var IconPicker = {
        modal: null,
        currentInput: null,
        currentTrigger: null,
        icons: {},
        customIcons: {},
        categories: {},
        allIconNames: [],
        remoteIcons: [],
        existingIcons: [],
        mode: 'local', // 'local' or 'remote'
        loading: false,

        init: function() {
            if (typeof workspaceIconPicker === 'undefined') {
                console.warn('Icon picker data not found');
                return;
            }

            this.icons = workspaceIconPicker.icons || {};
            this.customIcons = workspaceIconPicker.customIcons || {};
            this.categories = workspaceIconPicker.categories || {};

            // Merge local and custom icons
            var allIcons = Object.assign({}, this.icons, this.customIcons);
            this.allIconNames = Object.keys(allIcons).sort();

            this.modal = $('#workspace-icon-picker-modal');
            if (!this.modal.length) {
                return;
            }

            this.buildCategories();
            this.renderIcons(this.allIconNames);
            this.bindEvents();
        },

        buildCategories: function() {
            var container = this.modal.find('.modal-categories');
            var categoryNames = Object.keys(this.categories).sort();

            // Add "Downloaded" category if there are custom icons
            if (Object.keys(this.customIcons).length > 0) {
                container.append(
                    '<button type="button" class="category-btn" data-category="downloaded">' +
                    workspaceIconPicker.i18n.downloaded +
                    '</button>'
                );
            }

            categoryNames.forEach(function(category) {
                container.append(
                    '<button type="button" class="category-btn" data-category="' + category + '">' +
                    category +
                    '</button>'
                );
            });
        },

        renderIcons: function(iconNames, isRemote) {
            var self = this;
            var grid = this.modal.find('.modal-grid');
            grid.empty();

            if (!iconNames.length) {
                grid.html('<div class="no-icons">' + workspaceIconPicker.i18n.noResults + '</div>');
                return;
            }

            iconNames.forEach(function(name) {
                var allIcons = Object.assign({}, self.icons, self.customIcons);
                var svg = allIcons[name] || '';
                var label = name.replace(/-/g, ' ');
                var isExisting = self.existingIcons.indexOf(name) !== -1;

                if (isRemote && !svg) {
                    // Remote icon - show placeholder with download button
                    var item = $(
                        '<button type="button" class="icon-item icon-item-remote' + (isExisting ? ' icon-exists' : '') + '" data-icon="' + name + '" title="' + label + '">' +
                        '<span class="icon-svg"><span class="dashicons dashicons-download"></span></span>' +
                        '<span class="icon-name">' + label + '</span>' +
                        (isExisting ? '<span class="icon-badge">+</span>' : '') +
                        '</button>'
                    );
                } else {
                    var item = $(
                        '<button type="button" class="icon-item" data-icon="' + name + '" title="' + label + '">' +
                        '<span class="icon-svg">' + svg + '</span>' +
                        '<span class="icon-name">' + label + '</span>' +
                        '</button>'
                    );
                }

                grid.append(item);
            });
        },

        bindEvents: function() {
            var self = this;

            // Open modal
            $(document).on('click', '.icon-picker-trigger', function(e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                self.currentInput = $('#' + targetId);
                self.currentTrigger = $(this);
                self.openModal();
            });

            // Clear icon
            $(document).on('click', '.icon-picker-clear', function(e) {
                e.preventDefault();
                var field = $(this).closest('.icon-picker-field');
                var input = field.find('.icon-picker-input');
                var trigger = field.find('.icon-picker-trigger');

                input.val('');
                trigger.find('.icon-preview').empty();
                trigger.find('.icon-label').text(workspaceIconPicker.i18n.selectIcon);
                $(this).remove();
            });

            // Close modal
            this.modal.on('click', '.modal-close, .modal-overlay', function() {
                self.closeModal();
            });

            // Search
            this.modal.on('input', '.icon-search-input', function() {
                var query = $(this).val().toLowerCase().trim();
                self.filterIcons(query);
            });

            // Category filter
            this.modal.on('click', '.category-btn', function() {
                var category = $(this).data('category');
                self.modal.find('.category-btn').removeClass('active');
                $(this).addClass('active');
                self.modal.find('.icon-search-input').val('');
                self.filterByCategory(category);
            });

            // Select local icon
            this.modal.on('click', '.icon-item:not(.icon-item-remote)', function() {
                var iconName = $(this).data('icon');
                self.selectIcon(iconName);
            });

            // Download remote icon
            this.modal.on('click', '.icon-item-remote', function() {
                var iconName = $(this).data('icon');
                var $item = $(this);

                if ($item.hasClass('icon-exists') || $item.hasClass('downloading')) {
                    // Already exists or downloading, just select it
                    if ($item.hasClass('icon-exists')) {
                        self.selectIcon(iconName);
                    }
                    return;
                }

                self.downloadIcon(iconName, $item);
            });

            // Browse more button
            this.modal.on('click', '.browse-more-btn', function() {
                if (self.mode === 'local') {
                    self.switchToRemoteMode();
                } else {
                    self.switchToLocalMode();
                }
            });

            // Keyboard navigation
            $(document).on('keydown', function(e) {
                if (!self.modal.is(':visible')) return;
                if (e.key === 'Escape') {
                    self.closeModal();
                }
            });
        },

        openModal: function() {
            this.mode = 'local';
            this.modal.fadeIn(150);
            this.modal.find('.icon-search-input').val('').focus();
            this.modal.find('.category-btn').removeClass('active').first().addClass('active');
            this.modal.find('.modal-title').text(workspaceIconPicker.i18n.selectIcon);
            this.modal.find('.browse-more-btn').html('<span class="dashicons dashicons-download"></span> ' + workspaceIconPicker.i18n.browseMore);
            this.modal.find('.modal-categories').show();
            this.renderIcons(this.allIconNames);
            $('body').addClass('workspace-icon-picker-open');
        },

        closeModal: function() {
            this.modal.fadeOut(150);
            $('body').removeClass('workspace-icon-picker-open');
            this.currentInput = null;
            this.currentTrigger = null;
            this.mode = 'local';
        },

        filterIcons: function(query) {
            var source = this.mode === 'remote' ? this.remoteIcons : this.allIconNames;

            if (!query) {
                this.renderIcons(source, this.mode === 'remote');
                return;
            }

            var filtered = source.filter(function(name) {
                return name.toLowerCase().includes(query);
            });

            this.renderIcons(filtered, this.mode === 'remote');
        },

        filterByCategory: function(category) {
            if (category === 'all') {
                this.renderIcons(this.allIconNames);
                return;
            }

            if (category === 'downloaded') {
                var downloadedNames = Object.keys(this.customIcons).sort();
                this.renderIcons(downloadedNames);
                return;
            }

            var categoryIcons = this.categories[category] || [];
            categoryIcons = categoryIcons.slice().sort();
            this.renderIcons(categoryIcons);
        },

        switchToRemoteMode: function() {
            var self = this;

            if (this.loading) return;
            this.loading = true;

            var $btn = this.modal.find('.browse-more-btn');
            $btn.prop('disabled', true).html('<span class="spinner is-active" style="float:none;margin:0 5px 0 0;"></span> Loading...');

            // Fetch remote icons
            $.post(workspaceIconPicker.ajaxUrl, {
                action: 'hub21_get_lucide_icons',
                nonce: workspaceIconPicker.nonce
            })
            .done(function(response) {
                if (response.success) {
                    self.remoteIcons = response.data.icons;
                    self.existingIcons = response.data.existing;
                    self.mode = 'remote';

                    // Update UI
                    self.modal.find('.modal-title').text('Browse Lucide Icons (' + self.remoteIcons.length + ' icons)');
                    self.modal.find('.icon-search-input')
                        .attr('placeholder', workspaceIconPicker.i18n.searchAll)
                        .val('')
                        .focus();
                    self.modal.find('.modal-categories').hide();
                    $btn.html('<span class="dashicons dashicons-arrow-left-alt"></span> ' + workspaceIconPicker.i18n.back);

                    self.renderIcons(self.remoteIcons, true);
                } else {
                    alert('Error: ' + (response.data || 'Failed to load icons'));
                }
            })
            .fail(function() {
                alert('Failed to connect to server');
            })
            .always(function() {
                self.loading = false;
                $btn.prop('disabled', false);
            });
        },

        switchToLocalMode: function() {
            this.mode = 'local';
            this.modal.find('.modal-title').text(workspaceIconPicker.i18n.selectIcon);
            this.modal.find('.icon-search-input')
                .attr('placeholder', workspaceIconPicker.i18n.search)
                .val('');
            this.modal.find('.modal-categories').show();
            this.modal.find('.category-btn').removeClass('active').first().addClass('active');
            this.modal.find('.browse-more-btn').html('<span class="dashicons dashicons-download"></span> ' + workspaceIconPicker.i18n.browseMore);

            // Refresh local icons in case new ones were downloaded
            var allIcons = Object.assign({}, this.icons, this.customIcons);
            this.allIconNames = Object.keys(allIcons).sort();
            this.renderIcons(this.allIconNames);
        },

        downloadIcon: function(iconName, $item) {
            var self = this;

            $item.addClass('downloading');
            $item.find('.icon-svg').html('<span class="spinner is-active" style="float:none;"></span>');

            $.post(workspaceIconPicker.ajaxUrl, {
                action: 'hub21_download_icon',
                nonce: workspaceIconPicker.nonce,
                icon: iconName
            })
            .done(function(response) {
                if (response.success) {
                    // Add to custom icons
                    self.customIcons[iconName] = response.data.svg;
                    self.existingIcons.push(iconName);

                    // Update the item
                    $item.removeClass('downloading icon-item-remote').addClass('icon-exists');
                    $item.find('.icon-svg').html(response.data.svg);
                    if (!$item.find('.icon-badge').length) {
                        $item.append('<span class="icon-badge">+</span>');
                    }

                    // Allow selecting on future clicks
                    $item.off('click').on('click', function() {
                        self.selectIcon(iconName);
                    });

                    // Auto-select the icon after download
                    self.selectIcon(iconName);
                } else {
                    alert('Error: ' + (response.data || 'Failed to download icon'));
                    $item.removeClass('downloading');
                    $item.find('.icon-svg').html('<span class="dashicons dashicons-download"></span>');
                }
            })
            .fail(function() {
                alert('Failed to connect to server');
                $item.removeClass('downloading');
                $item.find('.icon-svg').html('<span class="dashicons dashicons-download"></span>');
            });
        },

        selectIcon: function(iconName) {
            if (!this.currentInput || !this.currentTrigger) return;

            var allIcons = Object.assign({}, this.icons, this.customIcons);
            var svg = allIcons[iconName] || '';
            var label = iconName.replace(/-/g, ' ');
            label = label.charAt(0).toUpperCase() + label.slice(1);

            this.currentInput.val(iconName).trigger('change');
            this.currentTrigger.find('.icon-preview').html(svg);
            this.currentTrigger.find('.icon-label').text(label);

            // Add clear button if not present
            var field = this.currentTrigger.closest('.icon-picker-field');
            if (!field.find('.icon-picker-clear').length) {
                this.currentTrigger.after(
                    '<button type="button" class="button-link icon-picker-clear" title="Clear icon">' +
                    '<span class="dashicons dashicons-no-alt"></span>' +
                    '</button>'
                );
            }

            this.closeModal();
        }
    };

    $(document).ready(function() {
        IconPicker.init();
    });

    window.WorkspaceIconPicker = IconPicker;

})(jQuery);
