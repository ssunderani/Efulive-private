<?php

namespace App\Exports;

use App\Inventory;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
class InventoryoutExport implements FromCollection, WithHeadings
{
    public $record;
    public function __construct($data)
    {
        $this->record = $data;
    }

    public function headings(): array
    {
        return [
            'Item Category',
            'Product s#',
            'Make',
            'Model',
            'Issue to',
            'Location',
            'Issue By',
            'Issue Date',
            'Initial Status',
            'Current Condition',
            'Remarks'
        ];
    }

    public function collection()
    {
        $record = json_decode($this->record);
        return collect($record);
    }
}
