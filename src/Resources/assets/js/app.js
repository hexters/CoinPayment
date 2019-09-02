require('./bootstrap');  
require('jquery-slimscroll');
window.Vue = require('vue');
import VueCountdown from '@xkeshi/vue-countdown';
import Loading from 'vue-loading-overlay';

Vue.use(Loading);
Vue.component(VueCountdown.name, VueCountdown);

$(function() {
  $('.product-list').slimScroll({
    height: '200px'
  });
  $('#support-coin-web').slimScroll({
    height: '430px'
  });
  $('#support-coin-mobile').slimScroll({
    height: '400px'
  });
});

let vue = new Vue({
  'el': '#app',
  components: {
    'formTransaction': require('./components/formTransaction.vue')
  }
});