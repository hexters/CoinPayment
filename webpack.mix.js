const { mix } = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.js(__dirname + '/src/Resources/assets/js/app.js', 'js/coinpayment.js')
    .sass( __dirname + '/src/Resources/assets/sass/app.scss', 'css/coinpayment.css')
    .setPublicPath(__dirname + '/src/Resources/assets/prod');

mix.js(__dirname + '/src/Resources/assets/js/coinpayment.transaction.js', 'js/coinpayment.transaction.js')
    .js(__dirname + '/src/Resources/assets/js/coinpayment.transaction.vue.js', 'js/coinpayment.transaction.vue.js')
    .sass( __dirname + '/src/Resources/assets/sass/coinpayment.transaction.scss', 'css/coinpayment.transaction.css')
    .setPublicPath(__dirname + '/src/Resources/assets/prod');

if (mix.inProduction()) {
    mix.version();
}
