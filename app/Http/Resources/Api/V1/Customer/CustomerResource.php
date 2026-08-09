<?php

namespace App\Http\Resources\Api\V1\Customer;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $customer_code
 * @property mixed $name
 * @property mixed $phone
 * @property mixed $address
 */
class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_code' => $this->customer_code,
            'name' => $this->name,
            'phone' => $this->phone,
            'address' => $this->address,

            // Sementara selalu false karena modul piutang
            // belum dibuat.
            'has_receivable' => false,
        ];
    }
}
