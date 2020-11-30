const { mix } = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.js(__dirname + '/src/Resources/assets/js/app.js', 'src/Resources/assets/prod/js/coinpayment.js')
    .sass( __dirname + '/src/Resources/assets/sass/app.scss', 'src/Resources/assets/prod/css/coinpayment.css');

mix.js(__dirname + '/src/Resources/assets/js/coinpayment.transaction.js', 'src/Resources/assets/prod/js/coinpayment.transaction.js')
    .js(__dirname + '/src/Resources/assets/js/coinpayment.transaction.vue.js', 'src/Resources/assets/prod/js/coinpayment.transaction.vue.js')
    .js(__dirname + '/src/Resources/assets/js/ladmin.coinpayment', 'src/Resources/assets/prod/js/ladmin.coinpayment')
    .sass( __dirname + '/src/Resources/assets/sass/coinpayment.transaction.scss', 'src/Resources/assets/prod/css/coinpayment.transaction.css');
    