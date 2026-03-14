/**
 * Workspace Sidebar - Interactivity API Store
 *
 * Handles sidebar toggle state with localStorage persistence
 * State persists across all pages. Lesson pages default to collapsed if no preference saved.
 */
import { store, getContext } from '@wordpress/interactivity';

const STORAGE_KEY = 'workspace-sidebar-collapsed';

store('workspaces/sidebar', {
    actions: {
        toggleSidebar() {
            const context = getContext();
            context.isCollapsed = !context.isCollapsed;
            document.body.classList.toggle('sidebar-offcanvas', context.isCollapsed);
            localStorage.setItem(STORAGE_KEY, context.isCollapsed);
        },
    },
    callbacks: {
        initFromStorage() {
            const context = getContext();
            const saved = localStorage.getItem(STORAGE_KEY);

            if (saved !== null) {
                // Use saved preference
                context.isCollapsed = saved === 'true';
            } else if (context.isLessonPage) {
                // No saved preference + lesson page = default to collapsed
                context.isCollapsed = true;
            }

            // Sync body class with context state
            document.body.classList.toggle('sidebar-offcanvas', context.isCollapsed);
        },
    },
});
