<x-ladmin-layout>
  <x-slot name="title">Details of Transaction</x-slot>
  
  <div id="app-coinpayment">
    
      <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
          <a class="nav-link active" id="coinpayment-tag-tab" data-toggle="pill" href="#coinpayment-tag" role="tab" aria-controls="coinpayment-tag" aria-selected="true">Coinpayment Info</a>
        </li>
        <li class="nav-item" role="presentation">
          <a class="nav-link" id="product-tab-tab" data-toggle="pill" href="#product-tab" role="tab" aria-controls="product-tab" aria-selected="false">Product Info</a>
        </li>
      </ul>
    
      <div class="row">
        <div class="col-md-8 mb-3">
          <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="coinpayment-tag" role="tabpanel" aria-labelledby="coinpayment-tag-tab">
    
              <x-ladmin-card>
                <x-slot name="flat">
                  <table class="table m-0">
                    <tbody>
                      <tr>
                        <td>Txn ID</td>
                        <td>{{ $data->txn_id ?? 'Waiting for buyer\'s payment' }}</td>
                      </tr>
                      <tr>
                        <td>Status</td>
                        <td>{{ $data->status_text }}</td>
                      </tr>
                      <tr>
                        <td>Total Amount To Send</td>
                        <td>
                          {{ number_format($data->amountf, 8) }} {{ $data->coin }}<br>
                          <small class="text-muted">Total confirms needed {{ $data->confirms_needed }}</small>
                         </td>
                      </tr>
                      <tr>
                        <td>Received So Far</td>
                        <td>{{ number_format($data->receivedf, 8) }} {{ $data->coin }}</td>
                      </tr>
                      <tr>
                        <td>Address</td>
                        <td>
                          <div class="p-2 bg-dark text-light rounded">
                            {{ $data->address ?? 'Waiting for buyer\'s payment' }}
                          </div>
                        </td>
                      </tr>
                      
                      <tr>
                        <td colspan="2" class="text-right">
                          
                          @if (is_null($data->txn_id))
                            <form action="{{ route('administrator.coinpayment.transaction.destroy', $data->uuid) }}" method="POST">
                              @csrf 
                              @method('DELETE')
                              <button class="btn btn-light float-left">{!! ladmin()->icon('trash') !!}</button>
                              <small class="text-muted">* Right click and copy link address &rarr;</small>
                              <a href="{{ $data->checkout_url }}" class="btn btn-primary ml-3" target="_blank">Checkout Link</a>
                            </form>
                          @else
                            <form action="{{ route('administrator.coinpayment.transaction.update', $data->uuid) }}" method="POST">
                              @csrf 
                              @method('PUT')
                              <a target="_blank" class="btn btn-link" href="{{ $data->status_url }}">Alternative Link</a>
                              @can(['administrator.coinpayment.transaction.cehck.status'])
                                <button type="submit" class="btn btn-primary">Check Status &rarr;</button>
                              @endcan
                            </form>
                          @endif
    
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </x-slot>
              </x-ladmin-card>
    
            </div>
            <div class="tab-pane fade" id="product-tab" role="tabpanel" aria-labelledby="product-tab-tab">
              <x-ladmin-card>
                <x-slot name="flat">
                  <table class="table">
                    <tbody>
                      <tr>
                        <td>Order ID</td>
                        <td>{{ $data->order_id }}</td>
                      </tr>
                      <tr>
                        <td>Payment Date</td>
                        <td>{{ $data->created_at->format(config('coinpayment.font.date_format')) }}</td>
                      </tr>
                      <tr>
                        <td>Buyer Name</td>
                        <td>{{ $data->buyer_name }}</td>
                      </tr>
                      <tr>
                        <td>Buyer Email</td>
                        <td>{{ $data->buyer_email }}</td>
                      </tr>
                      <tr>
                        <td>Total Amount</td>
                        <td>
                          {{ number_format($data->amount, 2) }} {{ $data->currency_code }}
                         </td>
                      </tr>
                    </tbody>
                  </table>
                </x-slot>
              </x-ladmin-card>
            </div>
          </div>
          
        </div>
        <div class="col-md-4 mb-3">
          <x-ladmin-card class="text-center">
            <div>
              @if (is_null($data->qrcode_url))
                Waiting for buyer's payment
              @else 
                <img src="{{ $data->qrcode_url }}" alt="Qr Code" class="img-fluid">
              @endif
            </div>
          </x-ladmin-card>
        </div>
      </div>
    
      <x-ladmin-card>
        <x-slot name="flat">
          <table class="table">
            <thead>
              <tr>
                <th width="1%">No</th>
                <th>Description</th>
                <th class="text-right">Price</th>
                <th width="5%" class="text-center">Qty</th>
                <th class="text-right">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($data->items as $i => $item)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ number_format($item->price, 2) }} {{ $item->currency_code }}</td>
                    <td class="text-center">{{ $item->qty }}</td>
                    <td class="text-right">{{ number_format($item->subtotal, 2) }} {{ $item->currency_code }}</td>
                  </tr>
              @empty
                  <tr>
                    <td colspan="5">Item not available</td>
                  </tr>
              @endforelse
            </tbody>
            <tfoot>
              <tr>
                <td colspan="4" class="text-right font-weight-bold">Total</td>
                <td class="text-right font-weight-bold">{{ number_format($data->amount_total_fiat, 2) }} {{ $item->currency_code }}</td>
              </tr>
            </tfoot>
          </table>
        </x-slot>
      </x-ladmin-card>

      <llc></llc>
  </div>

  <x-slot name="scripts">
    <script src="{{ asset('/js/ladmin.coinpayment.js') }}"></script>
  </x-slot>
</x-ladmin-layout>