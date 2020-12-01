<template>
  <div>{{ trxid }}</div>
</template>
<script>

import axios from 'axios';

export default {
  props: ['trxid'],
  mounted() {
    this.getDetails();
  },
  methods: {
    getDetails() {
      axios.get('/coinpayment/ajax/withdrawal/' + this.trxid)
        .then(json => {
          console.log(json);
        })
    }
  }
}
</script>