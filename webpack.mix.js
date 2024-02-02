const mix = require('laravel-mix');

/*
|--------------------------------------------------------------------------
| Mix Asset Management
|--------------------------------------------------------------------------
|
| Mix provides a clean, fluent API for defining some Webpack build steps
| for your Laravel applications. By default, we are compiling the CSS
| file for the application as well as bundling up all the JS files.
|
*/

mix
  .setPublicPath('resources/dist/')
  .js('resources/js/highlight.js', 'js')
  .sass('resources/css/highlight.scss', 'css')
  .postCss('resources/css/theme.css', 'css', [
    require('tailwindcss'),
  ]);
