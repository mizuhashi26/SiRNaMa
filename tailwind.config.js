import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
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
                    navyDark: '#091A30',
                    gold: '#FACC15',
                    goldHover: '#EAB308',
                    goldSoft: '#FEF9C3',
                },
            },
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', 'Segoe UI', 'Roboto', ...defaultTheme.fontFamily.sans],
            },
        },
    },
    plugins: [],
};
