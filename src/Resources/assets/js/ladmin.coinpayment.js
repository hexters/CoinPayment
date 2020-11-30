import VueCountdown from '@xkeshi/vue-countdown';

setTimeout(() => {
  
  Vue.component(VueCountdown.name, VueCountdown);
  let vue = new Vue({
    'el': '#app',
    components: {
      'countDown': require('./components/Countdowns.vue')
    }
  });
}, 1000);