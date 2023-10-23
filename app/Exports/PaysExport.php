<?php

namespace App\Exports;

use App\Models\pay;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PaysExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            'Họ Tên',
            'Ban',
            'Số tiền',
            'Tiền chi',
            'Thanh toán',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true]],
            'C' => ['font' => ['italic' => true]],
        ];
    }

    public function collection()
    {
        return DB::table('users')->join('pays', 'users.id', '=', 'pays.id_user')
        ->select('users.name','users.department','pays.price','pays.spending','pays.status')
        ->get();
    }
}
