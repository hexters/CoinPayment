setTimeout(() => {
  new Vue({
    'el': '#app-coinpayment',
    components: {
      'Balances': require('./components/Balances.vue'),
      'detail-withdrawal': require('./components/DetailWithdrawal.vue'),
      'llc': require('./components/License.vue')
    }
  });
}, 1000);