<x-ladmin-layout>
  <x-slot name="title">Details of Withdrawal</x-slot>

  <div id="app-coinpayment">
    <detail-withdrawal trxid="{{ $id }}"></detail-withdrawal>
  </div>


  <x-slot name="scripts">
    <script src="{{ asset('/js/ladmin.coinpayment.js') }}"></script>
  </x-slot>
</x-ladmin-layout>