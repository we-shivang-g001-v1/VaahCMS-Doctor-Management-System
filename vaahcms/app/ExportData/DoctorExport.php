<?php
namespace App\ExportData;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use VaahCms\Modules\Appointments\Models\Doctor;

class DoctorExport implements FromCollection, WithHeadings, WithCustomCsvSettings
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
        $query = Doctor::query();
        if (!empty($this->ids_selected)) {
            $query->whereIn('id', $this->ids_selected);
        }
        return $query->get()->map(function ($item) {
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
