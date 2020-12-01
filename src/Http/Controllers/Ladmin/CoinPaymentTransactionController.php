<?php

namespace Hexters\CoinPayment\Http\Controllers\Ladmin;

use Illuminate\Http\Request;
use Hexters\CoinPayment\Datatables\CoinpaymentDatatable;
use Hexters\CoinPayment\Entities\CoinpaymentTransaction;
use Hexters\CoinPayment\Helpers\CoinPaymentFacade as CoinPayment;

class CoinPaymentTransactionController extends Controller {

    protected $model;

    public function __construct(CoinpaymentTransaction $model) {
      $this->model = $model;
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
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
    public function destroy($id)
    {
        //
    }
}
