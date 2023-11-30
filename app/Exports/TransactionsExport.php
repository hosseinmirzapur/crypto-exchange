<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TransactionsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct($builder)
    {
        $this->builder = $builder;
    }

    public function query()
    {
        return $this->builder;
    }


    public function map($row): array
    {
        return [
            $row->user->profile->name ?? '',
            $row->user->email,
            $row->amount,
            $row->coin->label ?? $row->coin->code,
            ($row->coin->code === "TOMAN") ? $row->account->account_number : "",
            $row->created_at,
        ];
    }


    public function headings(): array
    {
        return [
            'نام',
            'نام کاربری',
            'مقدار در خواستی',
            'واحد در خواستی',
            'حساب مقصد',
            'تاریخ درخواست',
        ];
    }
}
