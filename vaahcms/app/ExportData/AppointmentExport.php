<?php
namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use VaahCms\Modules\Appointments\Models\Appointment;

class AppointmentExport implements FromCollection, WithHeadings, WithCustomCsvSettings
{
    protected $ids_selected;

    public function __construct($ids_selected = null)
    {

        if (is_string($ids_selected)) {
            $this->ids_selected = explode(',', $ids_selected);

        } elseif (is_array($ids_selected)) {
            $this->ids_selected = $ids_selected;
        } else {
            $this->ids_selected = [];
        }
    }

    public function collection()
    {
        // Eager load doctor and patient relationships
        $query = Appointment::with(['doctor', 'patient']);


        // Apply filter for selected IDs if any
        if (!empty($this->ids_selected)) {
            $query->whereIn('id', $this->ids_selected);
        }

        return $query->get()->map(function ($item) {

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
        ];
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '',  // No enclosure to avoid double quotes
        ];
    }
}
