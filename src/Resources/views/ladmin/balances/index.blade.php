<x-ladmin-layout>
  <x-slot name="title">Balances</x-slot>

  <div id="app-coinpayment">
  <balances currency="{{ config('coinpayment.default_currency', 'USD') }}"></balances>
  </div>

  <x-slot name="scripts">
    <script src="{{ asset('/js/ladmin.coinpayment.js') }}"></script>
  </x-slot>
</x-ladmin-layout>