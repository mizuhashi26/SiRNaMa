import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
    ],
    theme: {
        extend: {
            colors: {
                unpam: {
                    navy: '#0F294A',
                    navyDark: '#0A1E36',
                    gold: '#EAB308',
                    goldHover: '#CA8A04',
                    goldSoft: '#FEF3C7',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Segoe UI', 'Roboto', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
