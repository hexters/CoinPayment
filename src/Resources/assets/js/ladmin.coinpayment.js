setTimeout(() => {
  new Vue({
    'el': '#app-coinpayment',
    components: {
      'Balances': require('./components/Balances.vue'),
      'llc': require('./components/License.vue'),
      'form-input-item-production': require('./components/FormInputItemProduction.vue'),
    }
  });
}, 1000);