<?php

namespace Hexters\CoinPayment\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TransactionResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request) {
        return [
          'id' => $this->id,
          'amount' => $this->amount . ' ' . $this->coin,
          'confirmation_at' => ($this->confirmation_at === null) ? '-' : $this->confirmation_at->format('M d, Y H:i A'),
          'expired' => strtotime($this->expired) > time() ? $this->expired->diffForHumans() : $this->expired->format('M d, Y H:i A'),
          'expired_format' => $this->expired,
          'payment_address' => $this->payment_address,
          'payment_created_at' => $this->payment_created_at->format('M d, Y H:i A'),
          'payment_id' => $this->payment_id,
          'status' => $this->status,
          'status_text' => $this->status_text,
          'user' => $this->user,
          'updated_at' => $this->updated_at->diffForHumans(),
          'confirms_needed' => $this->confirms_needed,
          'qrcode_url' => $this->qrcode_url,
          'status_url' => $this->status_url
        ];
    }
}
