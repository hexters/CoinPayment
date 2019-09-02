<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>{{ env('APP_NAME') }} | CoinPayments IPN Error</title>
  </head>
  <body>
    <h3>{{ env('APP_NAME') }} | CoinPayment IPN Fatal Error</h3>

    <p>{!! $data['message'] !!}</p>


  </body>
</html>