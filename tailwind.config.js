import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'system-ui', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    DEFAULT: '#1E3A6E',
                    active: '#2D5196',
                    accent: '#6C63FF',
                    surface: '#EEF2FF',
                },
                success: {
                    DEFAULT: '#10B981',
                    light: '#D1FAE5',
                },
                warning: {
                    DEFAULT: '#F59E0B',
                    light: '#FEF3C7',
                },
                danger: {
                    DEFAULT: '#EF4444',
                    light: '#FEE2E2',
                },
                info: {
                    DEFAULT: '#3B82F6',
                    light: '#DBEAFE',
                },
                orange: {
                    DEFAULT: '#F97316',
                    light: '#FFEDD5',
                },
            },
        },
    },

    plugins: [
        forms({
            strategy: 'class', // <--- Tambahkan baris ini
        }),
    ],
};
