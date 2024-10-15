<?php
namespace App\ExportData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use VaahCms\Modules\Appointments\Models\Doctor;
use VaahCms\Modules\Appointments\Models\Patient;
use VaahCms\Modules\Appointments\Models\Appointment;

class AppointmentExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Fetch appointments with related doctor and patient data
        return Appointment::with(['doctor', 'patient'])->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'patient_name' => $item->patient ? $item->patient->name : 'N/A',
                'doctor_name' => $item->doctor ? $item->doctor->name : 'N/A',
                'slot_start_time' => $item->slot_start_time,
                'slot_end_time' => $item->slot_end_time,
                'status' => $item->status == 1 ? 'Booked' : 'Canceled',
                'reason'=> $item->reason,

                // Add any other fields you want to export
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Patient Name',
            'Doctor Name',
            'Start Time',
            'End Time',
            'Status',
            'Reason'
            // Include other headings for additional fields
        ];
    }
}

