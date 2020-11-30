<x-ladmin-layout>
  <x-slot name="title">Detail of Transaction</x-slot>
  
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
              <table class="table">
                <tbody>
                  <tr>
                    <td>Payment ID</td>
                    <td>{{ $data->txn_id }}</td>
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
                        {{ $data->address }}
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td>Time Left For Us to Confirm Funds	</td>
                    <td><count-down timeout="{{ $data->time_expires }}" /></td>
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
                    <td>Payment Date</td>
                    <td>{{ $data->created_at->format(config('coinpayment.font.date_format')) }}</td>
                  </tr>
                  <tr>
                    <td>Order ID</td>
                    <td>{{ $data->order_id }}</td>
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
          <img src="{{ $data->qrcode_url }}" alt="Qr Code" class="img-fluid">
          <a target="_blank" href="{{ $data->status_url }}">Alternative Link</a>
        </div>
        <x-slot name="footer">
          <form action="{{ route('administrator.coinpayment.update', $data->uuid) }}" method="POST">
          @csrf 
          @method('PUT')
            <button type="submit" class="btn btn-primary btn-sm btn-block">Check Status &rarr;</button>
          </form>
        </x-slot>
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
                <td class="text-right">{{ $item->price }} {{ $item->currency_code }}</td>
                <td class="text-center">{{ $item->qty }}</td>
                <td class="text-right">{{ $item->subtotal }} {{ $item->currency_code }}</td>
              </tr>
          @empty
              
          @endforelse
        </tbody>
      </table>
    </x-slot>
  </x-ladmin-card>

  <x-slot name="styles">
    <script src="{{ asset('/js/ladmin.coinpayment.js') }}"></script>
  </x-slot>
</x-ladmin-layout>