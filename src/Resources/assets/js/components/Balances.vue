<template>
  <ladmin-layout>
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
                <!-- Modal Top Up -->
                <button v-if="cantopup" data-toggle="modal" @click="topUp(item)" :data-target="`#modal-topup-${i}`" class="btn btn-primary btn-sm">Top Up</button>
                <div v-if="cantopup" class="modal text-left fade" data-backdrop="static" :id="`modal-topup-${i}`" tabindex="-1" aria-labelledby="modal-topup-Label" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header border-0">
                        <h5 class="modal-title" id="modal-topup-Label">{{ item.coin }} Address</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                        <div class="mb-3">
                          <strong>Send amount to address below</strong>
                        </div>
                        <div class="p-3 bg-dark text-center text-light">
                          {{ item.address || 'Generating Coin Address...' }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Modal Withdrawal -->
                <button v-if="canwd" data-toggle="modal" :data-target="`#modal-withdrawal-${i}`" class="btn btn-primary btn-sm">Withdrawal</button>
                <div v-if="canwd" class="modal text-left fade" data-backdrop="static" :id="`modal-withdrawal-${i}`" tabindex="-1" aria-labelledby="modal-withdrawal-Label" aria-hidden="true">
                  <div class="modal-dialog">
                    <form action="" @submit.prevent="createWithdrawal(item)" method="POST">
                      <div class="modal-content">
                        <div class="modal-header border-0">
                          <h5 class="modal-title" id="modal-withdrawal-Label">Withdraw {{ item.coin }}</h5>
                          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group bg-white">
                                <label for="amount">Amount</label>
                                <div class="input-group rounded border mb-3">
                                  <input type="number" required class="form-control bg-white border-right-0 border-0" v-model="item.withdrawal.amount">
                                  <div class="input-group-append">
                                    <span class="input-group-text border-left-0 bg-white border-0" id="basic-addon2">{{ item.withdrawal.currency }}</span>
                                  </div>
                                </div>
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="form-group bg-white">
                                <label for="amount">Tx Fee</label>
                                <div class="input-group border rounded mb-3">
                                  <div class="input-group-prepend">
                                    <span class="input-group-text border-left-0 bg-white border-0" id="basic-addon2">Tx Fee</span>
                                  </div>
                                  <input type="number" required class="form-control bg-white border-right-0 border-0" v-model="item.withdrawal.add_tx_fee">
                                </div>
                              </div>
                            </div>

                            <div class="col-12">
                              <div class="form-group">
                                <label for="amount">Receiver Address</label>
                                <input type="text" class="form-control" v-model="item.withdrawal.address" required>
                              </div>
                            </div>

                            <div class="col-12">
                              <div class="form-group">
                                <label for="amount">Note</label>
                                <textarea v-model="item.withdrawal.note" class="form-control" cols="30" rows="4"></textarea>
                              </div>
                            </div>
                          </div>

                        </div>
                        <div class="modal-footer border-0">
                          <button type="button" class="btn btn-link" data-dismiss="modal">Cancel</button>
                          <button type="submit" :disabled="item.loading" class="btn btn-danger">Withdrawal &rarr;</button>
                        </div>
                      </div>
                    </form>
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
  </ladmin-layout>
</template>
<script>

import axios from 'axios';
import swal from 'sweetalert';
import LadminLayout from './LadminLayout';

export default {
  props: ['currency', 'cantopup', 'canwd'],
  components: {
    LadminLayout
  },
  data() {
    return {
      balances: [],
      rates: [],
      withdrawal: {}
    };
  },
  mounted() {
    this.getBalance();
  },
  methods: {
    getBalance() {
      axios.get('/administrator/coinpayment/ajax/balances')
        .then(json => {
          this.balances = json.data;
          setTimeout(() => {
            $('.coinpayment-datatable-base').DataTable();
          }, 500);
        });
    },
    topUp(item) {
      item.address = '';
      axios.post('/administrator/coinpayment/ajax/top_up', {
        currency: item.coin
      })
        .then(json => {
          item.address = json.data.address;
        })
        .catch(error => {
          if(error.response.status == 403) { 
            swal('You cannot access this process', {
              icon: 'warning'
            });
          }
        });
    },
    createWithdrawal(item) {
      item.loading = true;
      axios.post('/administrator/coinpayment/ajax/create_withdrawal', item.withdrawal)
      .then(json => {
        item.loading = false;
        window.location.href = '/administrator/coinpayment/withdrawal/' + json.data.id;
      })
      .catch(error => {
        if(error.response.status > 200 && error.response.status < 500 ) {
          item.loading = false;

          if(error.response.status == 422) {
            let { errors } = error.response.data;
            swal('Validation errors', {
              icon: 'warning'
            });
          } else {
            swal(error.response.data.message, {
              icon: 'warning'
            });
          }
          
        }

        if(error.response.status == 403) { 
            swal('You cannot access this process', {
              icon: 'warning'
            });
        }

        if(error.response.status == 500) { 
            swal('Server error', {
              icon: 'warning'
            });
        }
      });
    }
  }
}
</script>