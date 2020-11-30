<template>
  <div>
    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table m-0 table-striped ladmin-datatables coinpayment-datatable-base">
          <thead>
            <tr>
              <th>COIN</th>
              <th class="text-right">Balance</th>
              <th>Coin Status</th>
              <th>Status</th>
              <th class="text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, i) in balances" :key="i">
              <td><img :src="item.icon" alt="Icon" width="30"> {{ item.coin }}</td>
              <td  class="text-right">
                {{ item.balancef }}
              </td>
              <td>{{ item.coin_status }}</td>
              <td>{{ item.status }}</td>
              <td class="text-right">
                <button data-toggle="modal" @click="topUp(item)" :data-target="`#modal-topup-${i}`" class="btn btn-primary btn-sm">Top Up</button>

                <div class="modal text-left fade" data-backdrop="static" :id="`modal-topup-${i}`" tabindex="-1" aria-labelledby="modal-topup-Label" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header border-0">
                        <h5 class="modal-title" id="modal-topup-Label">{{ item.coin }} Address</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="p-3 bg-dark text-center text-light">
                          {{ item.address || 'Loading...' }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
            </tr>

            <tr v-if="balances.length == 0">
              <td colspan="5">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<script>

import axios from 'axios';

export default {
  props: ['currency'],
  data() {
    return {
      balances: [],
      rates: [],
    };
  },
  mounted() {
    this.getBalance();
  },
  methods: {
    getBalance() {
      axios.get('/coinpayment/ajax/balances')
        .then(json => {
          this.balances = json.data;
        });
    },
    topUp(item) {
      item.address = '';
      axios.post('/coinpayment/ajax/top_up', {
        currency: item.coin
      })
        .then(json => {
          item.address = json.data.address;
        });
    }
  }
}
</script>