/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './assets/js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        frs: {
          primary: '#1e40af',
          secondary: '#3b82f6',
          accent: '#60a5fa',
        },
      },
    },
  },
  plugins: [],
}
