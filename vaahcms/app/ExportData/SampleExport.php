<?php

namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use VaahCms\Modules\Appointments\Models\Appointment;

class SampleExport implements FromCollection, WithHeadings, WithCustomCsvSettings
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
            'ID',
            'Patient Name',
            'Patient Email',
            'Doctor Name',
            'Doctor Email',
            'Start Time',
            'End Time',
            'Status',
            'Reason',
            // Include other headings for additional fields
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
