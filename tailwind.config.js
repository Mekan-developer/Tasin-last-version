import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', 'Figtree', ...defaultTheme.fontFamily.sans],
                data: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                navy:    '#1e2a3a',
                ink:     '#1e293b',
                muted:   '#64748b',
                line:    '#e2e8f0',
                surface: '#f1f5f9',
                dnavy:   '#141d2b',
                dbg:     '#0f172a',
                dcard:   '#1e293b',
                dline:   '#334155',
            },
            borderRadius: {
                card: '14px',
                btn:  '10px',
            },
        },
    },

    plugins: [forms],
};
