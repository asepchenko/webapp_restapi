<?php

namespace App\Imports;

use App\Models\OrderReference;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OrderReferencesImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new OrderReference([
            "order_number"      => $row['order_number'],
            "reference_number"  => $row['reference_number'],
            "colly"             => $row['colly'],
            "description"       => $row['description'],
            "user_id"           => auth()->user()->id
        ]);
    }
}
