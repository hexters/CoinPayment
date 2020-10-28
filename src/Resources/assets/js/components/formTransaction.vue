<template>
    <div class="coinpayment">
        <div class="row justify-content-md-center mt-3">
            <div class="col-lg-4">
                <div class="card shadow-lg p-3 mb-3 bg-white border-0">
                    <div class="card-body text-center">
                        <div class="logo" v-if="header.default == 'logo'">
                            <img v-bind:src="_host + header.type.logo" v-bind:alt="header.type.text" width="150" v-bind:title="header.type.text">
                        </div>
                        <div class="text" v-else-if="header.default == 'text'">
                            <strong>{{ header.type.text }}</strong>
                        </div>
                        <div v-else>
                            <div class="animated-background" style="height:50px;"></div>
                        </div>
                    </div>
                    <table class="table mb-0">
                        <thead>
                            <tr class="header-color">
                                <th width="70%">Description</th>
                                <th width="30%" class="text-right">Amount</th>
                            </tr>
                        </thead>
                    </table>
                    <div class="product-list">
                        <table class="table mt-0">
                            <tbody>
                                <tr v-for="index in 3" v-bind:key="index" v-if="!payload.items">
                                    <td width="70%">
                                        <div class="animated-background mb-2" style="height:15px;"></div>
                                        <div class="animated-background mb-2" style="height:10px;"></div>
                                        <div class="animated-background" style="height:10px;"></div>
                                    </td>
                                    <td width="30%" class="clear-right">
                                        <div class="animated-background float-right" style="width:30px;height:10px;"></div>
                                    </td>
                                </tr>
                                
                                <tr v-for="(item, i) in payload.items" v-bind:key="i">
                                    <td width="70%" title="">
                                        <strong>{{ item.itemDescription }}</strong><br>
                                        <small class="text-muted">item price: {{ item.itemPrice }} {{ default_currency }}</small><br>
                                        <small class="text-muted">quantity: {{ item.itemQty }}</small>
                                    </td>
                                    <td width="30%" class="text-right">{{ item.itemSubtotalAmount }} {{ default_currency }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr>
                    <table class="mb-0" style="width:100%;">
                        <tbody>
                            <tr>
                                <th width="50%" class="text-right">Amount total {{ default_currency }}</th>
                                <th width="50%" class="text-right border-bottom">{{ payload.amountTotal }} {{ default_currency }}</th>
                            </tr>
                            <tr>
                                <th class="text-right">Convert to</th>
                                <th class="text-right convert_to border-bottom">{{ default_coin.iso }}</th>
                            </tr>
                            <tr>
                                <th class="text-right">Amount total {{ default_coin.iso }}</th>
                                <th class="text-right text-danger border-bottom">{{ default_coin.amount }} {{ default_coin.iso }}</th>
                            </tr>
                        </tbody>
                    </table>
                    <hr>
                    <div class="text-center">
                        {{ payload.note }}
                    </div>
                    <div class="text-right mt-3 clear-left">
                        <button v-if="payload.items" class="btn float-right btn-danger" @click="paynow()">Pay now &raquo;</button>
                        <div v-else>
                            <div class="animated-background float-right" style="height:30px;width:80px;"></div>
                        </div>
                        <button v-if="payload.items" type="button" class="btn mr-2 float-right btn-default mobile-version" data-toggle="modal" data-target="#coinSupport">
                            Choose coin
                        </button>
                    </div>
                </div>
                <div class="text-center mb-3">
                    <a v-bind:href="payload.redirect_url">&laquo; Cancel Transaction</a>
                </div>
            </div>

            <div class="col-lg-7 web-version">
                <div class="card shadow-lg p-3 mb-5 bg-white border-0">
                    <div class="card-body">
                        <form action="">
                            <div class="input-group mb-3 coin-search">
                                <input type="search" class="form-control" v-model="search" placeholder="search coin...">
                                <div class="input-group-append">
                                    <button class="btn" type="button"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </form>
                        <div id="support-coin-web" class="support-coin">
                            <div class="row">
                                <div class="col-lg-6 col-sm-6 col-sm-12 mb-2" v-for="(index) in 10" v-bind:key="index" v-if="rates.length === 0">
                                    <div class="media list-coins p-2">
                                        <div class="col-3 m-0 p-0">
                                            <div class="animated-background" style="height:35px;"></div>
                                        </div>
                                        <div class="col-9">
                                            <div class="animated-background mb-2" style="height:15px;"></div>
                                            <div class="animated-background" style="height:10px;"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6 col-sm-6 col-sm-12 mb-2" v-for="(rate, i) in filterCoin" v-bind:key="i">
                                    <div v-bind:class="'media list-coins p-2 ' + (default_coin.iso == rate.iso ? 'active' : '')" @click="set_billing(rate)">
                                        <img class="align-self-center mr-2" v-bind:src="rate.icon" v-bind:alt="rate.name" height="35">
                                        <div class="media-body">
                                            <strong>{{ rate.name }}</strong>
                                            <div class="text-muted">{{ rate.amount }} {{ rate.iso }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="coinSupport" tabindex="-1" role="dialog" aria-labelledby="coinSupportLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="coinSupportLabel">Support coins</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="input-group mb-3 coin-search">
                            <input type="search" class="form-control" v-model="search" placeholder="search coin...">
                            <div class="input-group-append">
                                <button class="btn" type="button"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </form>
                    <div id="support-coin-mobile" class="support-coin">
                        <div class="row">
                            <div class="col-lg-6 col-sm-6 col-sm-12 mb-2" v-for="(index) in 10" v-bind:key="index" v-if="rates.length === 0">
                                <div class="media list-coins p-2">
                                    <div class="col-3 m-0 p-0">
                                        <div class="animated-background" style="height:35px;"></div>
                                    </div>
                                    <div class="col-9">
                                        <div class="animated-background mb-2" style="height:15px;"></div>
                                        <div class="animated-background" style="height:10px;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-6 col-sm-6 col-sm-12 mb-2" v-for="(rate, i) in filterCoin" v-bind:key="i">
                                <div v-bind:class="'media list-coins p-2 ' + (default_coin.iso == rate.iso ? 'active' : '')" @click="set_billing(rate)">
                                    <img class="align-self-center mr-2" v-bind:src="rate.icon" v-bind:alt="rate.name" height="35">
                                    <div class="media-body">
                                        <strong>{{ rate.name }}</strong>
                                        <div class="text-muted">{{ rate.amount }} {{ rate.iso }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="createdResult" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="createdResultLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                <div class="modal-header text-center">
                    <h5 class="modal-title" id="createdResultLabel">Payment Information</h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="text-right">
                                    Status
                                </td>
                                <td>: {{ transaction.status_text }}</td>
                            </tr>
                            <tr>
                                <td class="text-right">Total Amount To Send</td>
                                <td>: {{ transaction.amountf }} {{ transaction.coin }} (total confirms needed: {{ transaction.confirms_needed }})</td>
                            </tr>
                            <tr>
                                <td class="text-right">
                                    Received So Far
                                </td>
                                <td>: {{ transaction.receivedf }} {{ transaction.coin }} {{ transaction.recv_confirms == 0 ? '(unconfirmed)' : '' }}</td>
                            </tr>
                            <tr>
                                <td class="text-right">
                                    Balance Remaining
                                </td>
                                <td>: {{ transaction.amountf }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>
                                    <img v-bind:src="transaction.qrcode_url" class="img-fluid">
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right">Send To Address</td>
                                <td>: {{ transaction.address }} <br> <small class="text-danger">Do not send value to us if address status is expired!</small></td>
                            </tr>
                            <tr>
                                <td class="text-right">
                                    Time Left For Us to Confirm Funds
                                </td>
                                <td>:
                                    <countdown :time="expired">
                                        <template slot-scope="props">
                                            <strong>{{ props.minutes }}m {{ props.seconds }}s</strong>
                                        </template>
                                    </countdown>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right">Payment ID</td>
                                <td>: {{ transaction.txn_id }} <br> <small class="text-muted">(have this handy if you need any support related to this transaction)</small> </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
                <div class="modal-footer">
                    <a target="_blank" class="btn btn-secondary" v-bind:href="transaction.status_url">Alternative link</a>
                    <a v-bind:href="payload.redirect_url" class="btn btn-primary">Finish</a>
                </div>
                </div>
            </div>
            </div>

    </div>
</template>
<script>
import swal from 'sweetalert';
export default {
    props: ['_host', '_payload'],
    name: 'formTransaction',
    data() {
        return {
            rates: [],
            header: {},
            payload: {},
            default_coin: {},
            default_currency: '',
            search: '',
            transaction: {},
            expired: 0
        }
    },
    computed: {
        filterCoin() {
            return this.rates.filter(post => {
                return post.name.toLowerCase().includes(this.search.toLowerCase())
            })
        }
    },
    created() {
        this.encrypt_payload();
    },
    methods: {
        format_expired() {
            this.expired = this.transaction.time_expires;
        },
        paynow() {
            swal('Are you sure ?', {
                buttons: true
            })
            .then(e => {
                if(e) {
                    this.makeTransaction();
                }
            });
        },
        makeTransaction() {
            let self = this;
            let newPayload = {
                ...this.payload,
                coinIso: this.default_coin.iso,
                coinName: this.default_coin.name,
                coinAmount: this.default_coin.amount
            };

            let loader = this.$loading.show({
                // Optional parameters
                container: this.fullPage ? null : this.$refs.formContainer,
                canCancel: false
            });
    
            axios.post(_host + '/coinpayment/ajax/create', newPayload)
                .then(json => {
                    self.transaction = json.data;
                    $('#createdResult').modal('show');
                    self.format_expired();
                    loader.hide()
                })
                .catch(error => {
                    if(error.response.status >= 400) {
                        swal(error.response.data.message, {
                            icon: 'warning'
                        });
                    }
                    loader.hide()
                });
        },
        set_billing(rate) {
            this.default_coin = rate;
            $('#coinSupport').modal('hide');
        },
        encrypt_payload() {
            let self = this;
            axios.post(_host + '/coinpayment/ajax/payload', {
                payload: this._payload
            })
            .then(json => {
                if(json.data.result) {
                    self.rates = json.data.data.rates.accepted_coin;
                    self.header = json.data.data.config;
                    self.payload = json.data.data.payload;
                    self.default_currency = json.data.data.default_currency;
                    self.default_coin = json.data.data.default_coin;
                }
            })
            .catch(error => {
                if(typeof error.response.data.message !== "undefined") {
                    swal(error.response.data.message, {
                        icon: 'warning'
                    });
                }
            });
        }
    }
}
</script>

