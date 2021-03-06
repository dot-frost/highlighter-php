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

mix.js('resources/js/app.js', 'public/js').vue({ runtimeOnly: (process.env.NODE_ENV || 'production') === 'production' })
mix.postCss('resources/css/app.css', 'public/css', [require('tailwindcss')])
mix.js('resources/js/vendor.js', 'public/js')
mix.css('resources/css/vendor.css', 'public/css/vendor.css')

mix.disableSuccessNotifications();
mix.options({
    hmrOptions: {
        host: 'localhost',
        port: 3000,
    },
    hmr: true,
})
// mix.browserSync({
//     proxy: 'http://localhost:8080',
//     port: 80,
// });

