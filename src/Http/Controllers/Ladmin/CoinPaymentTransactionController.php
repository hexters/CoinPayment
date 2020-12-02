<?php

namespace Hexters\CoinPayment\Http\Controllers\Ladmin;

use Illuminate\Http\Request;
use Hexters\CoinPayment\Datatables\CoinpaymentDatatable;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Helpers\CoinPaymentFacade as CoinPayment;
use Hexters\CoinPayment\Repositories\CoinpaymentTransactionRepository;

class CoinPaymentTransactionController extends Controller {

    protected $model;
    protected $repository;

    public function __construct(CoinpaymentTransaction $model, CoinpaymentTransactionRepository $repository) {
      $this->model = $model;
      $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
      ladmin()->allow(['administrator.coinpayment.transaction.index']);

      return CoinpaymentDatatable::view('coinpayment::ladmin.transaction.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
      ladmin()->allow(['administrator.coinpayment.transaction.create']);
      
      return view('coinpayment::ladmin.transaction.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
      ladmin()->allow(['administrator.coinpayment.transaction.create']);
      
      if($this->env()) {
        session()->flash('warning', [__('coinpayment::ladmin.license.forbidden')]);
        return redirect()->back();
      }

      $request->validate([
        'buyer_name' => ['required'],
        'buyer_email' => ['required', 'email'],
        'order_id' => ['required', 'unique:coinpayment_transactions'],
        
        'itemDescription' => ['required', 'array'],
        'itemDescription.*' => ['required'],

        'itemPrice' => ['required', 'array'],
        'itemPrice.*' => ['required', 'numeric', 'not_in:0'],

        'itemQty' => ['required', 'array'],
        'itemQty.*' => ['required', 'numeric', 'not_in:0'],

        'itemSubtotalAmount' => ['required', 'array'],
        'itemSubtotalAmount.*' => ['required', 'numeric', 'not_in:0'],

        'redirect_url' => ['required', 'url'],
        'cancel_url' => ['required', 'url']

      ]);
        
      return $this->repository->create_transaction($request->all());
        

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($uuid) {
      ladmin()->allow(['administrator.coinpayment.transaction.show']);

        $data['data'] = $this->model->where('uuid', $uuid)->firstOrFail();

        return view('coinpayment::ladmin.transaction.show', $data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $uuid) {



      $data = $this->model->where('uuid', $uuid)->firstOrFail();
      $status = CoinPayment::getstatusbytxnid($data->txn_id);

      session()->flash('success', [$status['status_text']]);

      return redirect()->back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        
      $transaction = $this->model->where('uuid', $id)->first();
      if($transaction) {
        
        if($transaction->status == -1000) {
          $transaction->items()->delete();
          $transaction->delete();
  
          session()->flash('success', [
            'Transactions has been deleted'
          ]);
  
          return redirect()->route('administrator.coinpayment.transaction.index');
        }

        session()->flash('warning', [
          'Transactions cannot be deleted, the current status is ' . $transaction->status_text
        ]);
  
        return redirect()->back();
      }

      session()->flash('danger', [
        'Transactions not found!'
      ]);

      return redirect()->back();


    }
}
