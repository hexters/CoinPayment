setTimeout(() => {
  new Vue({
    'el': '#app-coinpayment',
    components: {
      'Balances': require('./components/Balances.vue'),
      'llc': require('./components/License.vue')
    }
  });
}, 1000);