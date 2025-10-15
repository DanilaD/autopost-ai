import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    // Enable dark mode with class strategy
    // The 'dark' class will be toggled on the <html> element
    darkMode: 'class',

    theme: {
        extend: {
            colors: {
                accent: '#6E56CF',
                'accent-light': '#8B7BFF',
                // Glass Edge color system
                glass: {
                    bg: 'rgba(255,255,255,.60)',
                    'bg-soft': 'rgba(255,255,255,.45)',
                    border: 'rgba(255,255,255,.65)',
                    'border-soft': 'rgba(255,255,255,.45)',
                },
                // Dark mode glass colors
                'dark-glass': {
                    bg: 'rgba(15,23,42,.60)',
                    'bg-soft': 'rgba(15,23,42,.45)',
                    border: 'rgba(255,255,255,.10)',
                    'border-soft': 'rgba(255,255,255,.05)',
                },
            },
            boxShadow: {
                sm: '0 1px 2px rgba(15,23,42,.08)',
                md: '0 8px 24px rgba(15,23,42,.10)',
                lg: '0 20px 40px rgba(15,23,42,.14)',
                // Glass-specific shadows
                'glass-sm': '0 1px 2px rgba(15,23,42,.08)',
                'glass-md': '0 8px 24px rgba(15,23,42,.10)',
                'glass-lg': '0 20px 40px rgba(15,23,42,.14)',
            },
            borderRadius: {
                lg: '16px',
                xl: '20px',
                '2xl': '24px',
            },
            backdropBlur: {
                md: '16px',
                lg: '20px',
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', 'sans-serif'],
            },
            // Glass Edge specific utilities
            backgroundImage: {
                'glass-gradient':
                    'linear-gradient(135deg, rgba(255,255,255,.1) 0%, rgba(255,255,255,.05) 100%)',
                'dark-glass-gradient':
                    'linear-gradient(135deg, rgba(15,23,42,.1) 0%, rgba(15,23,42,.05) 100%)',
            },
        },
    },

    plugins: [forms],
}
