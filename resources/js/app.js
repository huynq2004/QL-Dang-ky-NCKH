import './bootstrap';

import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'

// Install the focus plugin
Alpine.plugin(focus)

// Make Alpine available globally
window.Alpine = Alpine

// Initialize Alpine after all plugins are registered
document.addEventListener('DOMContentLoaded', () => {
    Alpine.start()
})
