<x-ladmin-layout>
  <x-slot name="title">New Transaction</x-slot>

  <div id="app-coinpayment">
    <form action="{{ route('administrator.coinpayment.transaction.store') }}" method="POST">
      @csrf 
      <div class="mb-5"></div>
      <h4 class="font-weight-bold">Buyer Information</h4>
      
      <x-ladmin-form-group name="buyer_name" col-input="7" col-label="5" label="Buyer Name *">
        <input type="text" name="buyer_name" value="{{ old('buyer_name') }}" class="form-control border-0" placeholder="Buyer Name" required>
      </x-ladmin-form-group>
  
      <x-ladmin-form-group name="buyer_email" col-input="7" col-label="5" label="Buyer E-mail *">
        <input type="email" name="buyer_email" value="{{ old('buyer_email') }}" class="form-control border-0" placeholder="Buyer E-mail" required>
      </x-ladmin-form-group>
  
      <div class="mb-5"></div>
      <h4 class="font-weight-bold">Product Information</h4>
      <x-ladmin-form-group name="order_id" col-input="7" col-label="5" label="Order ID / Invoice Number *">
        <input type="text" name="order_id" value="{{ old('order_id') }}" placeholder="Order ID / Invoice Number" class="form-control border-0" required>
      </x-ladmin-form-group>
  
      <x-ladmin-form-group name="note" col-input="7" col-label="5" label="Note for buyer">
        <input type="text" name="note" value="{{ old('note', 'Please complete the payment, click button below') }}" placeholder="Note for buyer" class="form-control border-0" required>
        <x-slot name="caption">
          <small class="text-muted">This note will be send to email buyer</small>
        </x-slot>
      </x-ladmin-form-group>

      <x-ladmin-form-group name="redirect_url" col-input="7" col-label="5" label="Redirect after finish transaction">
        <input type="url" name="redirect_url" value="{{ old('redirect_url') }}" placeholder="https://domian.com/invoice/detail" class="form-control border-0" required>
      </x-ladmin-form-group>

      <x-ladmin-form-group name="cancel_url" col-input="7" col-label="5" label="Redirect when user click link cancel">
        <input type="url" name="cancel_url" value="{{ old('cancel_url') }}" placeholder="https://domian.com/invoice/cancel" class="form-control border-0" required>
      </x-ladmin-form-group>

      <x-ladmin-form-group name="send_email" col-input="7" col-label="5" label="Send to Buyer" class="border-0">
        <div class="form-control border-0">
          <input type="checkbox" name="send_email" id="send_email" checked class="mr-3">
          <label for="send_email">I want to send an email to the buyer</label>
        </div>
      </x-ladmin-form-group>
      
      <div class="mb-5"></div>
  
      <form-input-item-production currency="{{ config('coinpayment.default_currency') }}" />
    </form>
  </div>

  <x-slot name="scripts">
    <script src="{{ asset('/js/ladmin.coinpayment.js') }}"></script>
  </x-slot>
</x-ladmin-layout>