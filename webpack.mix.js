const { mix } = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.js(__dirname + '/src/Resources/assets/js/app.js', 'src/Resources/assets/prod/js/coinpayment.js')
    .sass( __dirname + '/src/Resources/assets/sass/app.scss', 'src/Resources/assets/prod/css/coinpayment.css');