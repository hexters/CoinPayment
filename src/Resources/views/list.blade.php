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
        <div class="list-group">
          <a href="{{ url('/') }}" class="list-group-item list-group-item-action">
            <i class="fa fa-home"></i> Home
          </a>
        </div>

        <div class="input-group mt-3 form-search">
          <input autofocus type="search" placeholder="Search Coin..." value="" class="form-control form-search-input">
          <div class="input-group-prepend form-search-icon">
            <span class="input-group-text" id="basic-addon1">
              <i class="fa fa-search"></i>
            </span>
          </div>
        </div>

      </div>

      <div class="col-sm-8">
        <ul class="list-unstyled">
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
                    <a class="dropdown-item" href="#">
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
                  <td><b>Payment ID</b></td>
                  <td>: #@{{ item.payment_id }}</td>
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
        <div class="">
          <paginate
          :page-count="page_count"
          :prev-text="'Prev'"
          :next-text="'Next'"
          :container-class="'pagination justify-content-center pagination-sm'"
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
@stop

@section('footer')

@stop

@push('scripts')
  <script src="https://unpkg.com/vuejs-paginate@latest"></script>
  <script src="https://cdn.jsdelivr.net/npm/@xkeshi/vue-qrcode@0.3.0/dist/vue-qrcode.min.js" charset="utf-8"></script>
  <script type="text/javascript">
    Vue.component('qrcode', VueQrcode);
    Vue.component('paginate', VuejsPaginate);
    var vue = new Vue({
      'el': '#coinpayment-vue',
      data:{
        histories: [],
        page_count: 0
      },
      mounted(){
        this.getHistories(0);
      },
      methods:{
        paginate(pageNum){
          this.getHistories(pageNum);
        },
        getHistories(pageNum){
          var self = this;
          var pageNum = pageNum || 0;
          axios.get(`{{ route('coinpayment.ajax.transaction.histories') }}`,{
            params: {
              page: pageNum
            }
          })
            .then(function(json){
              self.page_count = Math.ceil((json.data.meta.total / json.data.meta.per_page));
              self.histories = json.data.data;
            });
        }
      }
    });
  </script>
@endpush
