@extends('coinpayment::layouts.master')

@section('content')
    <div id="app">
        <form-transaction _host="{{ url('/') }}" _payload="{{ $payload }}"></form-transaction>
    </div>
@stop
