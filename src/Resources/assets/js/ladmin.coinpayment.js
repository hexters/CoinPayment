setTimeout(() => {
  new Vue({
    'el': '#app-coinpayment',
    components: {
      'Balances': require('./components/Balances.vue')
    }
  });
}, 1000);