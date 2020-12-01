setTimeout(() => {
  new Vue({
    'el': '#app-coinpayment',
    components: {
      'Balances': require('./components/Balances.vue'),
      'detail-withdrawal': require('./components/DetailWithdrawal.vue')
    }
  });
}, 1000);