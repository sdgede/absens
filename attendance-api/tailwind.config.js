import defaultTheme from "tailwindcss/defaultTheme";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.js",
    ],

    darkMode: "class",

    theme: {
        extend: {
            fontFamily: {
                sans: ["Plus Jakarta Sans", ...defaultTheme.fontFamily.sans],
                mono: ["JetBrains Mono", ...defaultTheme.fontFamily.mono],
            },

            colors: {
                dark: {
                    950: "#070B14",
                    900: "#0B0F1A",
                    800: "#111827",
                    700: "#1A2235",
                    600: "#1F2D42",
                    500: "#2A3A52",
                    400: "#334560",
                    300: "#556080",
                    200: "#8B9EC7",
                    100: "#C5CEDF",
                    50: "#F0F4FF",
                },
                brand: {
                    DEFAULT: "#4F8EF7",
                    dark: "#3a7ef0",
                    light: "#7EB0FA",
                },
                emerald: { DEFAULT: "#22D3A0", dark: "#15A87E" },
                amber: { DEFAULT: "#F59E0B", dark: "#D97706" },
                rose: { DEFAULT: "#F43F5E", dark: "#E11D48" },
                violet: { DEFAULT: "#A78BFA", dark: "#7C3AED" },
                indigo: { DEFAULT: "#6366F1", dark: "#4F46E5" },
            },

            keyframes: {
                fadeUp: {
                    from: { opacity: "0", transform: "translateY(16px)" },
                    to: { opacity: "1", transform: "translateY(0)" },
                },
                fadeIn: {
                    from: { opacity: "0" },
                    to: { opacity: "1" },
                },
                pulse2: {
                    "0%, 100%": { opacity: "1" },
                    "50%": { opacity: "0.3" },
                },
                spin2: {
                    to: { transform: "rotate(360deg)" },
                },
                slideDown: {
                    from: { opacity: "0", transform: "translateY(-8px)" },
                    to: { opacity: "1", transform: "translateY(0)" },
                },
            },

            animation: {
                fadeUp: "fadeUp 0.5s cubic-bezier(0.16,1,0.3,1) both",
                fadeIn: "fadeIn 0.3s ease both",
                pulse2: "pulse2 2s ease-in-out infinite",
                spin2: "spin2 0.7s linear infinite",
                slideDown: "slideDown 0.25s ease both",
            },
        },
    },

    plugins: [],
};
