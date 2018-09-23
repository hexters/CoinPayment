@extends('coinpayment::layouts.master')

@section('title', 'Payment')

@push('styles')
  <style media="screen">
    .card{
      border-radius: 0;
    }
    .dropdown-toggle::before {
        display:none !important;
    }
    .form-search{
      border: solid 1px rgba(0,0,0,.2);
      padding: 5px;
    }
    .form-search .form-search-input{
      border: none;
    }
    .form-search .form-search-input:focus {
      box-shadow: none;
    }

    .form-search .form-search-icon span{
      background: none;
      border: none;
    }
    .page-item.active .page-link {
      z-index: 1;
      color: #333;
      background-color: rgba(0,0,0,.2);
      border-color: rgba(0,0,0,.1);
      }
      .bold{
        font-weight: bold;
      }
      .opacity{
        opacity: .3;
      }
  </style>
@endpush

@section('content')
  <div id="coinpayment-vue">
    <div class="text-center mt-3 mb-3">
      <img src="{{ asset(config('coinpayment.header_logo')) }}" width="180">
    </div>
    <hr>
    <div class="row justify-content-md-center">
      <div class="col-sm-3">
        <h5 class="mt-3">Filter Transaction</h5>
        <div class="input-group mt-3 form-search">
          <select class="form-control form-search-input" v-on:change="filterCoin($event)">
            <option value="">@{{ coinAliases.length == 0 ? 'Loading...' : 'Filter Coin' }}</option>
            <option v-bind:value="coin" v-for="(val, coin) in coinAliases">@{{ val }}</option>
          </select>
        </div>

        <div class="input-group mt-3 form-search">
          <select class="form-control form-search-input" v-on:change="filterStatus($event)">
            <option value="all">Filter Status</option>
            <option value="0">Waiting for buyer funds</option>
            <option value="1">Funds received and confirmed</option>
            <option value="100">Complete</option>
            <option value="-1">Cancelled / Timed Out</option>
          </select>
        </div>

        <div class="input-group mt-3 form-search">
          <select class="form-control form-search-input" v-on:change="filterLimit($event)">
            <option value="5">Limit 5</option>
            <option value="10">Limit 10</option>
            <option value="20">Limit 20</option>
            <option value="50">Limit 50</option>
            <option value="100">Limit 100</option>
          </select>
        </div>

        @if(!empty(config('coinpayment.menus')))
          <div class="list-group mt-5">
            @foreach(config('coinpayment.menus') as $key => $menu)
            <a href="{{ url($menu['url']) }}" class="list-group-item list-group-item-action">
              <i class="{{ $menu['class_icon'] }}"></i> {{ $key }}
            </a>
            @endforeach
          </div>
        @endif
      </div>

      <div class="col-sm-8">
        <p v-if="histories.length == 0">Transaction not found?</p>
        <ul v-bind:class="{ 'list-unstyled' : true, 'opacity': isLoading }" v-if="histories.length > 0">
          <li class="media mb-4" v-for="item in histories">
            <qrcode class="mr-3" v-bind:value="item.payment_address" :options="{ size: 150 }"></qrcode>
            <div class="media-body">
              <div class="float-right">
                <div class="dropdown dropleft float-right">
                  <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fa fa-cog"></i>
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" v-bind:href="item.status_url" target="_blank">
                      <small>Alternative Link</small>
                    </a>
                    <a class="dropdown-item" href="#" @click="manualCheck(event, item)">
                      <small>Manual Check</small>
                    </a>
                  </div>
                </div>
                <small class="text-muted">@{{ item.updated_at }}&nbsp;&nbsp;</small>
              </div>
              <h5 class="mt-0 mb-1" title="Payment ID">@{{ item.amount }}</h5>
              <small class="text-muted">@{{ item.payment_created_at }} | @{{ item.status_text }}</small>
              <table>
                <tr>
                  <td>
                    <small><b>Payment ID</b></small>
                  </td>
                  <td>
                    <small>: #@{{ item.payment_id }}</small>
                  </td>
                </tr>
                <tr>
                  <td><b>Address</b></td>
                  <td>: @{{ item.payment_address }}</td>
                </tr>
              </table>
              <div class="text-muted">
                <small>Expired date: @{{ item.expired }} | Confirmation at: @{{ item.confirmation_at }}</small>
              </div>
              <small class="text-danger">Do not send value to us if address status is expired!</small>
            </div>
          </li>
        </ul>
        <hr>
        <div class="row">
          <div class="col-xs-4 col-sm-4">
            <small>Showing @{{ histories.length }} of @{{ pageInfo.total }} entries</small>
          </div>
          <div class="col-xs-8 col-sm-8">
            <paginate
              v-if="histories.length > 0"
              :page-count="page_count"
              :prev-text="'Prev'"
              :next-text="'Next'"
              :container-class="'pagination justify-content-end pagination-sm'"
              :page-class="'page-item'"
              :page-link-class="'page-link'"
              :prev-class="'page-item'"
              :prev-link-class="'page-link'"
              :next-class="'page-item'"
              :next-link-class="'page-link'"
              :click-handler="paginate"
              >
            </paginate>
          </div>
        </div>
      </div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="showDetailManual" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Detail Transaction</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="">
            <table class="table">
              <tr>
                <td class="text-right">Status:</td>
                <td>@{{ manualDetail.status_text }}</td>
              </tr>
              <tr>
                <td class="text-right">Total Amount To Send:</td>
                <td>@{{ manualDetail.amount }} @{{ manualDetail.coin }} <span>(total confirms needed: @{{ manualDetail.confirms_needed }})</span></td>
              </tr>
              <tr>
                <td class="text-right">Received So Far:</td>
                <td>@{{ manualDetail.receivedf }} @{{ manualDetail.coin }}</td>
              </tr>
              <tr>
                <td class="text-right">Balance Remaining:</td>
                <td>
                  @{{ manualDetail.amount }} @{{ manualDetail.coin }}
                </td>
              </tr>
              <tr>
                <td class="text-center" colspan="2">
                  <img v-bind:src="manualDetail.qrcode_url">
                  <div class="text-danger">
                    <small>Do not send value to us if address status is expired!</small>
                  </div>
                </td>
              </tr>
              <tr>
                <td class="text-right">Send To Address:</td>
                <td>@{{ manualDetail.payment_address }}</td>
              </tr>
              <tr>
                <td class="text-right">Time Left For Us to Confirm Funds:</td>
                <td>
                  <div class="time-remaining bold">.../.../... ...:...:...</div>
                </td>
              </tr>
              <tr>
                <td class="text-right">Payment ID:</td>
                <td>@{{ manualDetail.payment_id }}</td>
              </tr>
              <tr>
                <td class="text-center text-muted" colspan="2">
                  <a class="text-muted" v-bind:href="manualDetail.status_url" target="_blank">Alternative Link</a>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
@stop

@section('footer')

@stop

@push('scripts')
  <script src="https://unpkg.com/vuejs-paginate@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/@xkeshi/vue-qrcode@0.3.0/dist/vue-qrcode.min.js"></script>
  <script type="text/javascript">
    Vue.component('qrcode', VueQrcode);
    Vue.component('paginate', VuejsPaginate);
    var vue = new Vue({
      'el': '#coinpayment-vue',
      data:{
        histories: [],
        coinAliases: [],
        page_count: 0,
        manualDetail: {},
        isLoading: false,
        params: {
          coin: '',
          limit: 5,
          status: 'all'
        },
        pageInfo: {}
      },
      mounted(){
        var self = this;
        this.getHistories(0);
        this.getMethods();

        $('#showDetailManual').on('hidden.bs.modal', function (e) {
          self.getHistories(0);
        });
      },
      methods:{
        getMethods(){
          var self = this;
          axios.get('{{ route('coinpayment.ajax.rate.usd', 0) }}')
            .then(function(json){
              self.coinAliases = json.data.aliases;
            });
        },
        paginate(pageNum){
          this.getHistories(pageNum);
        },
        getHistories(pageNum){
          var self = this;
          var pageNum = pageNum || 0;
          this.isLoading = true;
          axios.get(`{{ route('coinpayment.ajax.transaction.histories') }}`,{
            params: {
              page: pageNum,
              coin: this.params.coin,
              limit: this.params.limit,
              status: this.params.status,
            }
          })
            .then(function(json){
              self.page_count = Math.ceil((json.data.meta.total / json.data.meta.per_page));
              self.histories = json.data.data;
              self.isLoading = false;
              self.pageInfo = json.data.meta;
            });
        },
        manualCheck(e, params){
          e.preventDefault();
          var self = this;

          swal('Please Wait','checking...', {
            closeOnClickOutside: false,
            closeOnEsc: false,
            buttons:false,
            timer: 10000
          });

          axios.post('{{ route('coinpayment.ajax.transaction.manual.check') }}', params)
            .then(function(json){
              self.manualDetail = json.data;
              $('.time-remaining').countdown(json.data.expired, function(event){
                $(this).html(event.strftime('%D days %H:%M:%S'));
              });
              swal.close();
              $('#showDetailManual').modal('show');
            })
            .catch(function(err){
              console.error(err);
              swal('', 'Look like the something wrong!');
            });
        },
        filterCoin(e){
          this.params.coin = e.target.value;
          this.getHistories(0);
        },
        filterLimit(e){
          this.params.limit = e.target.value;
          this.getHistories(0);
        },
        filterStatus(e){
          this.params.status = e.target.value;
          this.getHistories(0);
        }
      }
    });
  </script>
@endpush
