@extends('coinpayment::layouts.master')

@section('content')
    <div id="app">
        <form-transaction _checkouturl="{{ request()->fullurl() }}" _host="{{ url('/') }}" _payload="{{ $payload }}"></form-transaction>
    </div>
@stop
