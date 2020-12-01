<x-ladmin-layout>
  <x-slot name="title">List of Transactions</x-slot>

  <div id="app-coinpayment">
    <x-ladmin-datatables :fields="$fields" :options="$options" />
    <llc></llc>
  </div>
  
  <x-slot name="scripts">
    <script src="{{ asset('/js/ladmin.coinpayment.js') }}"></script>
  </x-slot>
</x-ladmin-layout>