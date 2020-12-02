<?php 

  namespace Hexters\CoinPayment\Datatables;

  use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
  use Hexters\Ladmin\Datatables\Datatables;
  use Hexters\Ladmin\Contracts\DataTablesInterface;

  class CoinpaymentDatatable extends Datatables implements DataTablesInterface {

    public function render() {
      return $this->eloquent(CoinpaymentTransaction::query())
        ->editColumn('created_at', function($item) {
          return $item->created_at->format(config('coinpayment.formt.date_format', 'd/m/y H:i'));
        })
        ->editColumn('buyer_name', function($item) {
          return '<b>' . $item->buyer_name . '</b><br /><small class="text-muted">' . $item->buyer_email . '</small>';
        })
        ->editColumn('coin', function($item) {
          return '<img src="' . $this->logos($item->coin) . '" title="' . $item->coin . '" data-toggle="tooltip" data-placement="top" width="25" />';
        })
        ->editColumn('amount', function($item) {
          return '<b class="text-danger">' . number_format($item->amount_total_fiat, 2) . ' ' . $item->currency_code . '</b><br /><small class="text-muted">' . number_format($item->amountf, 8) . ' ' . $item->coin . '</small>';
        })
        ->addColumn('action', function($item) {
          return view('ladmin::table.action', [
            'show' => [
              'gate' => 'administrator.coinpayment.transaction.show',
              'url' => route('administrator.coinpayment.transaction.show', [$item->uuid, 'back' => request()->fullUrl()])
            ]
          ]);
        })
        ->escapeColumns([])
        ->make(true);
    }

    public function options() {
      
      return [
        'title' => 'List Of Transaction',
        'fields' => [
          [ 'name' => '#', 'class' => 'text-center'],
          [ 'name' => 'Date'],
          [ 'name' => 'Order Id'],
          [ 'name' => 'Buyer Name'],
          [ 'name' => 'Status'],
          [ 'name' => 'Amount'],
          [ 'name' => 'Action'],
        ],
        'options' => [
          'topButton' => view('coinpayment::ladmin.transaction._partials._button_create'),
          'processing' => true,
          'serverSide' => true,
          'ajax' => request()->fullurl(),
          'order' => [[1, 'asc']],
          'columns' => [
              ['data' => 'coin', 'class' => 'text-center'],
              ['data' => 'created_at'],
              ['data' => 'order_id'],
              ['data' => 'buyer_name'],
              ['data' => 'status_text'],
              ['data' => 'amount', 'class' => 'text-right'],
              ['data' => 'action'],
          ]
        ]
      ];

    }

    public function logos($coin) {
      $logos = config('coinpayment.logos', []);
      if(in_array($coin, ['BTC.LN'])) {
        $img = 'BTCLN';
      } else if(in_array($coin, ['USDT.ERC20'])) {
          $img = 'USDT';
      } else {
          $img = $coin;
      }

      return $logos[$coin] ?? 'https://www.coinpayments.net/images/coins/' . $img . '.png';
    }

  }