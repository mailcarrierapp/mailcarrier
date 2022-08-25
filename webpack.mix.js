const mix = require('laravel-mix');
const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin');

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
  .setPublicPath('dist/')
  .webpackConfig({
    plugins: [
      new MonacoWebpackPlugin({
        languages: ['twig', 'json'],
        features: ['!quickCommand'],
        globalAPI: true,
        filename: 'js/[name].worker.js'
      })
    ]
  })
  .js('resources/js/monaco.js', 'dist/js')
  .postCss('resources/css/app.css', 'dist/css', [
    require('tailwindcss'),
  ]);
