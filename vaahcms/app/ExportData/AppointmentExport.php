<?php
namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use VaahCms\Modules\Appointments\Models\Appointment;

class AppointmentExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    public function collection()
    {
        // Fetch appointments with related doctor and patient data
        return Appointment::with(['doctor', 'patient'])->get()->map(function ($item) {

            return [
                'id' => $item->id,
                'patient_name' => $item->patient ? $item->patient->name : 'N/A',
                'patient_email' => $item->patient ? $item->patient->email : 'N/A',
                'doctor_name' => $item->doctor ? $item->doctor->name : 'N/A',
                'doctor_email' => $item->doctor ? $item->doctor->email : 'N/A',
                'slot_start_time' => $item->slot_start_time,
                'slot_end_time' => $item->slot_end_time,
                'status' => $item->status == 1 ? 'Booked' : 'Canceled',
                'reason' => $item->reason,
            ];
        });
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
            'enclosure' => '',   // Specify no enclosure (removes double quotes)
        ];
    }
}
