<?php

namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use VaahCms\Modules\Appointments\Models\Doctor;

class DoctorSampleExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    // This method can return an empty collection as we only need headers
    public function collection()
    {
        // Return an empty collection since we only want headers
        return collect();
    }

    public function headings(): array
    {
        return [
            'Doctor Name',
            'Doctor Email',
            'Specialization',
            'Phone',
            'Shift Start Time',
            'Shift End Time',
            'Price',

        ];
    }

    // Custom CSV settings
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',  // Specify the delimiter
            'enclosure' => '',    // Specify no enclosure (removes double quotes)
        ];
    }
}
