/** @type {import('tailwindcss').Config} */
const colors = require('tailwindcss/colors');

module.exports = {
  content: [
    './src/Filament/Resources/**/*.php',
    './resources/**/*.blade.php',
    './resources/**/*.blade.php.stub',
    './resources/**/*.js',
    './vendor/filament/**/*.blade.php',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        primary: colors.indigo,
        danger: colors.rose,
        success: colors.green,
        warning: colors.yellow,
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
