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
                // Material Design 3 Color System
                primary: {
                    50: '#e3f2fd',
                    100: '#bbdefb',
                    200: '#90caf9',
                    300: '#64b5f6',
                    400: '#42a5f5',
                    500: '#2196f3',
                    600: '#1e88e5',
                    700: '#1976d2',
                    800: '#1565c0',
                    900: '#0d47a1',
                },
                secondary: {
                    50: '#f3e5f5',
                    100: '#e1bee7',
                    200: '#ce93d8',
                    300: '#ba68c8',
                    400: '#ab47bc',
                    500: '#9c27b0',
                    600: '#8e24aa',
                    700: '#7b1fa2',
                    800: '#6a1b9a',
                    900: '#4a148c',
                },
                tertiary: {
                    50: '#fff3e0',
                    100: '#ffe0b2',
                    200: '#ffcc80',
                    300: '#ffb74d',
                    400: '#ffa726',
                    500: '#ff9800',
                    600: '#fb8c00',
                    700: '#f57c00',
                    800: '#ef6c00',
                    900: '#e65100',
                },
                // Material Design 3 Surface Colors
                surface: {
                    50: '#fafafa',
                    100: '#f5f5f5',
                    200: '#eeeeee',
                    300: '#e0e0e0',
                    400: '#bdbdbd',
                    500: '#9e9e9e',
                    600: '#757575',
                    700: '#616161',
                    800: '#424242',
                    900: '#212121',
                },
                // Material Design 3 Background Colors
                background: {
                    light: '#fafafa',
                    dark: '#121212',
                },
                // Material Design 3 Error Colors
                error: {
                    50: '#ffebee',
                    100: '#ffcdd2',
                    200: '#ef9a9a',
                    300: '#e57373',
                    400: '#ef5350',
                    500: '#f44336',
                    600: '#e53935',
                    700: '#d32f2f',
                    800: '#c62828',
                    900: '#b71c1c',
                },
                // Material Design 3 Success Colors
                success: {
                    50: '#e8f5e8',
                    100: '#c8e6c9',
                    200: '#a5d6a7',
                    300: '#81c784',
                    400: '#66bb6a',
                    500: '#4caf50',
                    600: '#43a047',
                    700: '#388e3c',
                    800: '#2e7d32',
                    900: '#1b5e20',
                },
                // Material Design 3 Warning Colors
                warning: {
                    50: '#fff8e1',
                    100: '#ffecb3',
                    200: '#ffe082',
                    300: '#ffd54f',
                    400: '#ffca28',
                    500: '#ffc107',
                    600: '#ffb300',
                    700: '#ffa000',
                    800: '#ff8f00',
                    900: '#ff6f00',
                },
                // Legacy colors for compatibility
                accent: '#9c27b0',
                'accent-light': '#ba68c8',
            },
            boxShadow: {
                // Material Design 3 Elevation Shadows
                'elevation-1':
                    '0px 1px 2px 0px rgba(0, 0, 0, 0.3), 0px 1px 3px 1px rgba(0, 0, 0, 0.15)',
                'elevation-2':
                    '0px 1px 2px 0px rgba(0, 0, 0, 0.3), 0px 2px 6px 2px rgba(0, 0, 0, 0.15)',
                'elevation-3':
                    '0px 1px 3px 0px rgba(0, 0, 0, 0.3), 0px 4px 8px 3px rgba(0, 0, 0, 0.15)',
                'elevation-4':
                    '0px 2px 3px 0px rgba(0, 0, 0, 0.3), 0px 6px 10px 4px rgba(0, 0, 0, 0.15)',
                'elevation-5':
                    '0px 4px 4px 0px rgba(0, 0, 0, 0.3), 0px 8px 12px 6px rgba(0, 0, 0, 0.15)',
                // Dark mode elevation shadows
                'dark-elevation-1':
                    '0px 1px 2px 0px rgba(0, 0, 0, 0.3), 0px 1px 3px 1px rgba(0, 0, 0, 0.15)',
                'dark-elevation-2':
                    '0px 1px 2px 0px rgba(0, 0, 0, 0.3), 0px 2px 6px 2px rgba(0, 0, 0, 0.15)',
                'dark-elevation-3':
                    '0px 1px 3px 0px rgba(0, 0, 0, 0.3), 0px 4px 8px 3px rgba(0, 0, 0, 0.15)',
                'dark-elevation-4':
                    '0px 2px 3px 0px rgba(0, 0, 0, 0.3), 0px 6px 10px 4px rgba(0, 0, 0, 0.15)',
                'dark-elevation-5':
                    '0px 4px 4px 0px rgba(0, 0, 0, 0.3), 0px 8px 12px 6px rgba(0, 0, 0, 0.15)',
                // Legacy shadows
                sm: '0px 1px 2px 0px rgba(0, 0, 0, 0.3), 0px 1px 3px 1px rgba(0, 0, 0, 0.15)',
                md: '0px 1px 2px 0px rgba(0, 0, 0, 0.3), 0px 2px 6px 2px rgba(0, 0, 0, 0.15)',
                lg: '0px 1px 3px 0px rgba(0, 0, 0, 0.3), 0px 4px 8px 3px rgba(0, 0, 0, 0.15)',
            },
            borderRadius: {
                // Material Design 3 Border Radius - Less rounded
                xs: '2px',
                sm: '4px',
                md: '6px',
                lg: '8px',
                xl: '12px',
                '2xl': '16px',
                '3xl': '20px',
            },
            fontFamily: {
                sans: ['Roboto', 'system-ui', 'sans-serif'],
            },
            // Material Design 3 Animation Durations
            transitionDuration: {
                short1: '50ms',
                short2: '100ms',
                short3: '150ms',
                short4: '200ms',
                medium1: '250ms',
                medium2: '300ms',
                medium3: '350ms',
                medium4: '400ms',
                long1: '450ms',
                long2: '500ms',
                long3: '550ms',
                long4: '600ms',
            },
            // Material Design 3 Animation Easing
            transitionTimingFunction: {
                emphasized: 'cubic-bezier(0.2, 0, 0, 1)',
                'emphasized-decelerate': 'cubic-bezier(0.05, 0.7, 0.1, 1)',
                'emphasized-accelerate': 'cubic-bezier(0.3, 0, 0.8, 0.15)',
                standard: 'cubic-bezier(0.2, 0, 0, 1)',
                'standard-decelerate': 'cubic-bezier(0, 0, 0, 1)',
                'standard-accelerate': 'cubic-bezier(0.3, 0, 1, 1)',
            },
        },
    },

    plugins: [forms],
}
