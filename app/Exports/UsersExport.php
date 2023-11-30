<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersExport implements FromQuery, WithMapping, WithHeadings
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
            $row->email,
            $row->is_banned ? 'BANNED' : $row->status,
            $row->profile->name ?? '',
            $row->rank->label,
            $row->profile->phone ?? '',
            $row->profile->mobile ?? '',
            $row->profile->address ?? '',
            $row->profile->national_code ?? '',
            $row->profile->birthday ?? '',
            $row->credits[0]->credit ?? 0,
            $row->credits[0]->blocked ?? 0,
            count($row->accounts)
        ];
    }


    public function headings(): array
    {
        return [
            'نام کاربری',
            'وضعیت',
            'نام',
            'سطح',
            'شماره تلفن',
            'شماره موبایل',
            'آدرس',
            'کد ملی',
            'تاریخ تولد',
            'دارایی',
            'دارایی (بلوکه شده)',
            'تعداد حساب'
        ];
    }
}
