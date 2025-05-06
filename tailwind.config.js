/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.{blade.php,js,vue,ts}',
    './resources/views/**/*.blade.php',
  ],
  theme: {
    extend: {
      colors: {
        'custom-blue': '#1090CB',
        lightgray: '#F1F2F7',
      },
      fontFamily: {
        poppins: ['Poppins','sans-serif'],
      }
    },
  },
  plugins: [],
}
