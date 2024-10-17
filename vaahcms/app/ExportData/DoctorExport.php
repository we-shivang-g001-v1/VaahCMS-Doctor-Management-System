<?php
namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use VaahCms\Modules\Appointments\Models\Doctor;

class DoctorExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    public function collection()
    {
        return Doctor::all()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'email' => $item->email,
                'phone' => $item->phone,
                'price' => $item->price ?? 'NA',
                'specialization' => $item->specialization,
                'shift_start_time' => $item->shift_start_time,
                'shift_end_time' => $item->shift_end_time,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Price',
            'Specialization',
            'Shift Start Time',
            'Shift End Time',
        ];
    }

    // Custom CSV settings
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',  // Specify the delimiter
            'enclosure' => '',   // Specify no enclosure (removes double quotes)
        ];
    }
}
