@component('mail::message')
# Dear {{ $data['buyer_name'] }}

{{ $data['note'] ?? 'Please complete this payment, click button below' }}

@component('mail::button', ['url' => $data['checkout_url']])
  Checkout Now
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
