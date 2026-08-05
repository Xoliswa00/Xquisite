import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                app: 'rgb(var(--bg-app) / <alpha-value>)',
                panel: 'rgb(var(--bg-panel) / <alpha-value>)',
                'panel-2': 'rgb(var(--bg-panel-2) / <alpha-value>)',
                ink: 'rgb(var(--text-ink) / <alpha-value>)',
                'ink-muted': 'rgb(var(--text-ink-muted) / <alpha-value>)',
                'ink-faint': 'rgb(var(--text-ink-faint) / <alpha-value>)',
                line: 'rgb(var(--border-line) / <alpha-value>)',
                'line-2': 'rgb(var(--border-line-2) / <alpha-value>)',
            },
        },
    },

    plugins: [forms],
};
