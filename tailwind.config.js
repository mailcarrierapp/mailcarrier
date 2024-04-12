import preset from './vendor/filament/filament/tailwind.config.preset'

/** @type {import('tailwindcss').Config} */
export default {
  presets: [preset],
  content: [
    './src/Resources/**/*.php',
    './resources/**/*.blade.php',
    './resources/**/*.blade.php.stub',
    './resources/**/*.js',
    './vendor/filament/**/*.blade.php',
  ],
  safelist: [
    'dark:flex',
    'max-w-[14rem]',
  ],
}
